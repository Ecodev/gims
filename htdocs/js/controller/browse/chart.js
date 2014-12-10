angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $q, Chart, ChartCache, $rootScope) {
    'use strict';

    /**************************************************************************/
    /* VARIABLE INITIALISATION ************************************************/
    /**************************************************************************/
    $scope.activeTab = {};
    $scope.tabs = {};
    $scope.panelTabs = {
        activeTab: {}
    };
    $scope.ignoredElements = null;
    $scope.concatenatedIgnoredElements = [];
    $scope.geonameParams = {perPage: 500, fields: 'questionnaires'};
    $scope.filterSetParams = {fields: 'filters.genericColor,filters.color'};
    $scope.indexedElements = ChartCache.getCache();
    Chart.setCache($scope.indexedElements);

    /**************************************************************************/
    /* WATCHERS ***************************************************************/
    /**************************************************************************/

    $scope.$watch(function() {
        return $location.url();
    }, function() {
        $scope.returnUrl = $location.search().returnUrl;
        $scope.currentUrl = encodeURIComponent($location.url());
    });

    // Reload panel if exists in URL
    var originalUrlParameters = _.cloneDeep($location.search());
    var panel = originalUrlParameters.panel;
    if (panel) {
        $scope.panelOpened = true;
        $scope.panelTabs.activeTab[panel] = true;
    }

    // Reload tab if exists in URL
    var tab = originalUrlParameters.tab;
    if (tab) {
        $scope.activeTab[tab] = true;
    }

    // When tab is changed, update URL
    $scope.$watch('activeTab', function() {
        _.forEach($scope.activeTab, function(isActive, tab) {
            if (isActive) {
                $location.search('tab', tab);
            }
        });
    }, true);

    // When panel is closed or changed, update URL
    $scope.$watch('[panelTabs.activeTab, panelOpened]', function() {
        // suppress panel in URL
        $location.search('panel', null);

        // select currently active panel
        if ($scope.panelOpened) {
            _.forEach($scope.panelTabs.activeTab, function(isActive, panel) {
                if (isActive) {
                    $location.search('panel', panel);
                }
            });
        }

    }, true);

    $scope.$watch('panelTabs.target', function() {
        $location.search('target', $scope.panelTabs.target);
    });

    $scope.$watch('panelTabs.overridable', function() {
        $location.search('overridable', $scope.panelTabs.overridable);
    });

    $scope.$watch('panelTabs.reference', function() {
        if ($scope.panelTabs.reference) {
            var id = $scope.panelTabs.reference.id ? $scope.panelTabs.reference.id : $scope.panelTabs.reference;
            $location.search('reference', id);
            Restangular.one('filter', id).get({fields: 'children'}).then(function(filters) {
                $scope.panelTabs.referenceChildren = filters.children;
            });
        }
    });

    $scope.$watch('tabs.filterSets', function() {
        var filters = [];
        _.forEach($scope.tabs.filterSets, function(filterSet) {
            filters = filters.concat(filterSet.filters);
        });

        $scope.tabs.filters = _.uniq(filters, 'id');
    });

    $scope.$watch('tabs.filters', function(newFilters, oldFilters) {
        var removedFilters = _.map(_.difference(_.pluck(oldFilters, 'id'), _.pluck(newFilters, 'id')), function(filterId) {
            return {id: filterId};
        });
        var addedFilters = _.map(_.difference(_.pluck(newFilters, 'id'), _.pluck(oldFilters, 'id')), function(filterId) {
            return {id: filterId};
        });

        ChartCache.removeFilters(removedFilters);

        $scope.filtersIds = _.pluck($scope.tabs.filters, 'id').join(',');

        // filter series that are no more used in chart
        Chart.removeSeries(removedFilters);
        refreshNormalSeries(addedFilters);

        if (addedFilters.length > 0) {
            initIgnoredElements(addedFilters);
        } else {
            Chart.updateChart();
        }

        if ($scope.pointSelected) {
            retrieveFiltersAndValues($scope.pointSelected.questionnaire);
        }
    });

    /**
     * Executes when geoname, part or filter set are changed
     */
    $scope.$watch('tabs.part', function() {
        refreshNormalSeries(null);
        initIgnoredElements($scope.tabs.filters);
    }, true);

    /**
     * Executes when geoname, part or filter set are changed
     */
    $scope.$watch('tabs.geonames', function(newGeoname, oldGeoname) {

        if (oldGeoname) {
            getIgnoredElements(true);
            $scope.pointSelected = null;
        }

        refreshGeonameScopeShortcuts();
        refreshNormalSeries(null);
        initIgnoredElements($scope.tabs.filters);
    }, true);

    $scope.$watch('panelOpened', function() {
        $timeout(function() {
            jQuery(window).resize();
        }, 350); // 350 to resize after animation of panel
    });

    /**
     * Watch chart service notifications about changer selected point
     */
    $rootScope.$on('gims-chart-pointSelected', function(event, pointSelected) {
        $scope.$apply(function() {
            $scope.setPointSelected(pointSelected.id, pointSelected.questionnaire, pointSelected.name, pointSelected.filter);
            $scope.panelOpened = true;
            $scope.panelTabs.activeTab.filters = true;
        });
    });

    /**
     * Finds and affect differences, if there is at least one adjusted line
     * @param {chart} chart
     * @returns {boolean}
     */
    function findDifferences(chart) {
        chart.differences = null;
        _.find(chart.series, function(serie) {

            if (serie.overriddenFilters) {

                chart.differences = {
                    overriddenFilters: serie.overriddenFilters,
                    originalFilters: serie.originalFilters
                };

                return true;
            }
        });
    }

    /**
     * Create new object to fire $watch listener on highChart directive
     * ChartObj is not used because addSeries() function dont save customized attributes like id, isAdjusted, isIgnored.
     * Use chart.series because all data is kept as submitted.
     */
    var firstLoad = true;
    $rootScope.$on('gims-chart-modified', function(event, chart) {
        $scope.chart = _.cloneDeep(chart);
        findDifferences($scope.chart);

        // Reload projection if exists in original URL
        if (firstLoad && $scope.chart.series.length) {
            firstLoad = false;
            $scope.panelTabs.reference = originalUrlParameters.reference;
            $scope.panelTabs.target = originalUrlParameters.target;
            $scope.panelTabs.overridable = originalUrlParameters.overridable;

            if (originalUrlParameters.reference && originalUrlParameters.target && originalUrlParameters.overridable) {
                refreshAlternativeSeries(null, false);
            }
        }
    });

    // update estimates charts
    $rootScope.$on('gims-estimates-chart-modified', function(event, chart) {
        $scope.estimatesCharts = _.cloneDeep(chart);
    });

    /**
     * Use chart to compute estimates and configure ng-grid for display
     * @param ignoredElements
     */
    $scope.gridOptions = {
        data: 'estimatesData'
    };

    // update estimates table
    $rootScope.$on('gims-estimates-table-modified', function(event, data) {
        $scope.estimatesData = data.data;
        $scope.gridOptions.columnDefs = data.columns;
    });

    /**************************************************************************/
    /* SCOPE FUNCTIONS ********************************************************/
    /**************************************************************************/

    /**
     * Set a scoped variable questionnairesIds that contains questionnaire ids by geoname
     * Is used to link to table filter view
     * {
     *   all : '1,2,3,4,5',
     *   19 : '1,2,3',
     *   28 : '4,5'
     * }
     */
    var refreshGeonameScopeShortcuts = function() {
        $scope.questionnairesIds = {all: []};
        angular.forEach($scope.tabs.geonames, function(geoname) {
            $scope.questionnairesIds.all = $scope.questionnairesIds.all.concat(_.pluck(geoname.questionnaires, 'id'));
            $scope.questionnairesIds[geoname.id] = _.pluck(geoname.questionnaires, 'id').join(',');
        });
        $scope.questionnairesIds.all = _.uniq($scope.questionnairesIds.all).join(',');
        $scope.geonameIds = _.pluck($scope.tabs.geonames, 'id').join(',');
    };

    /**
     * Inspect $scope.indexedElements to find ignored elements and update Url if refreshUrl is set to true.
     *
     * When all filters for a questionnaire are ignored, the questionnaire is considered as ignored.
     * Set $scope.ignoredElements to true of false to allow or not panel display
     *
     * @param refreshUrl
     * @returns {Array}
     */
    var getIgnoredElements = function(refreshUrl) {

        var ignoredElements = Chart.getIgnoredElements();
        var concatenatedIgnoredElements = ignoredElements.concatenatedIgnoredElements;
        $scope.globalIndexedFilters = ignoredElements.globalIndexedFilters;
        $scope.ignoredElements = !_.isEmpty(ignoredElements.ignoredElements) ? ignoredElements.ignoredElements : null;

        if (refreshUrl) {
            if (concatenatedIgnoredElements.length > 0) {
                $location.search('ignoredElements', concatenatedIgnoredElements.join(','));
            } else {
                $location.search('ignoredElements', null);
            }
        }

        return concatenatedIgnoredElements;
    };

    /**
     * When retrieving new filters from server, ignored params are only browser side,
     * This function update filter.ignore status and recover from URL if needed.
     * @param filters
     */
    var initIgnoredElements = function(filters) {
        if ($scope.tabs.geonames && $scope.tabs.part && filters && filters.length) {
            refreshAlternativeSeries(filters, false).then(function() {
                if ($scope.pointSelected) {
                    retrieveFiltersAndValues($scope.pointSelected.questionnaire).then(function() {
                        Chart.initIgnoredElementsFromUrl($scope.tabs.filters, $scope.tabs.part).then(function() {
                            getIgnoredElements(false);
                        });

                    });
                } else {
                    Chart.initIgnoredElementsFromUrl($scope.tabs.filters, $scope.tabs.part).then(function() {
                        getIgnoredElements(false);
                    });
                }
            });
        }
    };

    /**
     * Include or ignore a single filter for all questionnaires
     * @param filter
     * @param ignored
     * @param questionnaireId
     */
    $scope.propagateStatusGlobally = function(filter, ignored, questionnaireId) {
        _.forEach($scope.indexedElements, function(questionnaire) {
            if (questionnaire.filters && questionnaire.filters[filter.filter.id]) {
                $scope.ignoreFilter(questionnaire.filters[filter.filter.id], !_.isUndefined(ignored) ? ignored : filter.filter.ignored, false);
                updateQuestionnaireIgnoredStatus(questionnaire);
            }
        });

        retrieveFiltersAndValues(questionnaireId);
        refreshAlternativeSeries(null, true);
    };

    /**
     * Ignore or include entire questionnaire
     * @param questionnaireId
     * @param ignore
     */
    $scope.toggleQuestionnaire = function(questionnaireId, ignore) {
        var questionnaire = ChartCache.cache($scope.tabs.part, questionnaireId);
        questionnaire.ignored = _.isUndefined(questionnaire.ignored) ? true : !questionnaire.ignored;

        _.forEach(questionnaire.filters, function(filter) {
            $scope.ignoreFilter(filter, !_.isUndefined(ignore) ? ignore : questionnaire.ignored, false);
        });

        retrieveFiltersAndValues(questionnaireId);
        refreshAlternativeSeries(null, true);
    };

    /**
     * Include or ignore a single filter for a single questionnaire
     * @param filter
     * @param ignored
     * @param refreshUrl
     * @param questionnaireId
     * @returns {Array}
     */
    $scope.ignoreFilter = function(filter, ignored, refreshUrl, questionnaireId) {
        if (filter) {
            filter.filter.ignored = ignored;

            if (refreshUrl) {
                retrieveFiltersAndValues(questionnaireId);
                refreshAlternativeSeries(null, true);
            }
        }
    };

    $scope.getFirstAttribute = function(obj) {
        return {
            attribute: Object.keys(obj)[0],
            content: obj[Object.keys(obj)[0]]
        };
    };

    /**
     * Reset projection form and refresh all series (without adjusted series)
     */
    $scope.removeAdjustedSeries = function() {
        Chart.removeSeries(null, 'isAdjusted');
        delete $scope.panelTabs.target;
        delete $scope.panelTabs.overridable;
        delete $scope.panelTabs.reference;
        Chart.updateChart();
    };

    /**
     * Add adjusted and original series after removing everything
     */
    $scope.addAdjustedSeries = function() {
        Chart.removeSeries(null, 'isAdjusted');
        refreshAlternativeSeries(null, false);
    };

    /**
     * Allow to change selected questionnaire (used from chart and outside chart in "Ignored elements" tab in panel)
     * @param id
     * @param questionnaireId
     * @param name
     * @param filterId
     */
    $scope.setPointSelected = function(id, questionnaireId, name, filterId) {
        $scope.pointSelected = {
            id: id,
            questionnaire: questionnaireId,
            name: name,
            filter: filterId
        };

        retrieveFiltersAndValues($scope.pointSelected.questionnaire);
    };

    /**
     * Structure of filters need a sorting that is not supported by ng-repeat
     * So panel calls this function that return ordered ids of filters by sorting
     *
     * @param filtersObj
     * @param hFilterId
     * @returns {Array}
     */
    $scope.sort = function(filtersObj, hFilterId) {
        if (!filtersObj) {
            return [];
        }
        var sortable = [];
        for (var filterIndex in filtersObj) {
            if (filtersObj[filterIndex].filter.hFilters[hFilterId]) {
                sortable.push(filtersObj[filterIndex]);
            }
        }
        sortable.sort(function(a, b) {
            return a.filter.hFilters[hFilterId].sorting - b.filter.hFilters[hFilterId].sorting;
        });
        sortable = _.map(sortable, function(filter) {
            return filter.filter.id;
        });

        return sortable;
    };

    /**
     * Returns a list of id of selected filters joined by comma.
     * @returns {String}
     */
    $scope.getFiltersIds = function() {
        return _.pluck($scope.tabs.filters, 'id').join(',');
    };

    /**
     * Get one of selected filters by id, to recover its color from template
     * @param list of elements containing id attribute
     * @param id to find
     * @returns {*}
     */
    $scope.findById = function(list, id) {
        return _.find(list, function(f) {
            if (f.id == id) {
                return true;
            }
        });
    };

    /**************************************************************************/
    /* PRIVATE FUNCTIONS ******************************************************/
    /**************************************************************************/

    var updateQuestionnaireIgnoredStatus = function(questionnaire) {
        if (questionnaire.filters) {
            var questionnaireIgnored = true;
            _.forEach(questionnaire.filters, function(filter) {
                if (filter && !filter.filter.ignored) {
                    questionnaireIgnored = false;
                    return false;
                }
            });
            questionnaire.ignored = questionnaireIgnored;
        }
    };

    /**
     * Calls ChartController to recover filters data and values
     *
     * @param questionnaireId
     * @param callback
     */
    var retrieveFiltersAndValues = function(questionnaireId) {
        var deferred = $q.defer();
        var questionnaire = ChartCache.cache($scope.tabs.part, questionnaireId);
        if (questionnaire) {
            // only launch ajax request if the filters in this questionnaire don't have values
            Chart.retrieveFiltersAndValues(questionnaire, $scope.tabs.filters, $scope.tabs.part).then(function() {
                deferred.resolve();
                getIgnoredElements(false);
            });
        }

        return deferred.promise;
    };

    /**
     * Calls ChartController to refresh charts depending on new values
     *
     * @param filters
     * @param refreshUrl Usually set to true before sending request but at first execution is set to false
     * @param callback
     */
    var alternativeSeriesTimeout = null;
    var refreshAlternativeSeries = function(filters, refreshUrl) {

        var deferred = $q.defer();

        filters = filters === null ? $scope.tabs.filters : filters;

        Chart.removeSeries(filters, 'isIgnored');
        Chart.removeSeries(null, 'isAdjusted');

        var ignoredElements = refreshUrl ? getIgnoredElements(refreshUrl).join(',') : $location.search().ignoredElements;
        var reference = $scope.panelTabs.reference ? $scope.panelTabs.reference.id ? $scope.panelTabs.reference.id : $scope.panelTabs.reference : null;
        var target = $scope.panelTabs.target ? $scope.panelTabs.target : null;
        var overridable = $scope.panelTabs.overridable ? $scope.panelTabs.overridable : null;

        if (_.isArray(filters) && (!_.isEmpty(ignoredElements) || (reference && target && overridable))) {
            var queryParams = {
                geonames: $location.search().geonames,
                part: $scope.tabs.part.id,
                filters: _.pluck(filters, 'id').join(','),
                ignoredElements: ignoredElements,
                reference: reference,
                target: target,
                overridable: overridable
            };

            if (alternativeSeriesTimeout) {
                alternativeSeriesTimeout.resolve();
            }
            alternativeSeriesTimeout = $q.defer();

            Chart.refresh(queryParams, $scope.tabs.part, $scope.tabs.geonames, alternativeSeriesTimeout).then(function(data) {
                deferred.resolve(data);
            });
        } else {
            if (alternativeSeriesTimeout) {
                alternativeSeriesTimeout.resolve();
            }
            Chart.updateChart();
        }

        return deferred.promise;
    };

    /**
     * Get JMP trendlines and don't ask for them again while filters or countries aren't changed
     */
    var normalSeriesTimeout = null;
    var refreshNormalSeries = function(filters) {
        filters = filters === null ? $scope.tabs.filters : filters;
        if (filters.length && $scope.tabs.part && $scope.tabs.geonames) {
            Chart.removeSeries(filters, null);

            var queryParams = {
                geonames: _.pluck($scope.tabs.geonames, 'id').join(','),
                part: $scope.tabs.part.id,
                filters: _.pluck(filters, 'id').join(',')
            };

            if (normalSeriesTimeout) {
                normalSeriesTimeout.resolve();
            }
            normalSeriesTimeout = $q.defer();
            Chart.refresh(queryParams, $scope.tabs.part, $scope.tabs.geonames, normalSeriesTimeout);
        }
    };

    /**
     * Toggle the display of a formula, and load it if not already done
     * @param {usage} usage
     */
    $scope.toggleShowFormula = function(usage) {
        usage.show = !usage.show;
        if (!usage.rule.structure) {
            Restangular.one('rule', usage.rule.id).get({fields: 'structure'}).then(function(rule) {
                usage.rule.structure = rule.structure;
            });
        }
    };

    /**
     * Returns the URL to contribute to the filter
     * @param {filter} filter
     * @param {geoname} geoname
     * @returns {String}
     */
    $scope.getContributeUrl = function(filter, geoname) {
        if (filter.name.match('NSA')) {
            return '/contribute/nsa?&geoname=' + geoname.id + '&returnUrl=' + $scope.currentUrl;
        } else {
            return '/contribute/jmp?&geoname=' + geoname.id + '&filter=' + filter.id + '&returnUrl=' + $scope.currentUrl;
        }

    };
});
