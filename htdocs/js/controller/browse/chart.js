angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $q, Chart, ChartCache, $rootScope) {
    'use strict';

    /**************************************************************************/
    /* VARIABLE INITIALISATION ************************************************/
    /**************************************************************************/
    $scope.tabs = {};
    $scope.panelTabs = {};
    $scope.ignoredElements = [];
    $scope.concatenatedIgnoredElements = [];
    $scope.geonameParams = {perPage: 500, fields: 'country'};
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

        var overridable = $location.search().overridable ? $location.search().overridable : null;
        if (overridable) {
            $scope.panelTabs.overridable = overridable;
        }

        var target = $location.search().target ? $location.search().target : null;
        if (target) {
            $scope.panelTabs.target = target;
        }

        var panelOpened = $location.search().panelOpened ? $location.search().panelOpened : false;
        if (panelOpened) {
            $scope.panelOpened = panelOpened;
        }
    });

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

        $scope.tabs.filters = filters;
    });

    $scope.$watch('tabs.filters', function(newFilters, oldFilters) {
        var removedFilters = _.difference(_.pluck(oldFilters, 'id'), _.pluck(newFilters, 'id'));
        var addedFilters = _.difference(_.pluck(newFilters, 'id'), _.pluck(oldFilters, 'id'));

        // remove unused filters to avoid them to be displayed in panel after removing
        _.forEach(removedFilters, function(removedFilterId) {

            // unlink unused filters in questionnaires to avoid to display related filters on side panel
            _.forEach($scope.indexedElements, function(questionnaire) {
                delete(questionnaire.hFilters[removedFilterId]);
            });
        });

        // filter series that are no more used in chart (need to clone $scope.chart to fire $watch event on highchart directive
        Chart.removeSeries(removedFilters);

        if (addedFilters.length > 0) {
            initIgnoredElements(addedFilters);
        }

        if ($scope.pointSelected) {
            retrieveFiltersAndValues($scope.pointSelected.questionnaire);
        }
    });

    /**
     * Executes when geoname, part or filter set are changed
     */
    $scope.$watch('tabs.part', function() {
        Chart.resetSeries();
        initIgnoredElements(_.pluck($scope.tabs.filters, 'id'));
    }, true);

    /**
     * Executes when geoname, part or filter set are changed
     */
    $scope.$watch('tabs.geonames', function(newGeoname, oldGeoname) {

        if (oldGeoname) {
            ChartCache.reset();
            Chart.resetSeries();
            getIgnoredElements(true);
            $scope.pointSelected = null;
        }
        initIgnoredElements(_.pluck($scope.tabs.filters, 'id'));
    }, true);

    $scope.$watch('panelOpened', function() {
        $timeout(function() {
            jQuery(window).resize();
        }, 350); // 350 to resize after animation of panel

        $location.search('panelOpened', $scope.panelOpened ? true : undefined);
    });

    /**
     * Watch chart service notifications about changer selected point
     */
    $rootScope.$on('gims-chart-pointSelected', function(event, pointSelected) {
        $scope.setPointSelected(pointSelected.id, pointSelected.questionnaire, pointSelected.name, pointSelected.filter);
        $scope.panelOpened = true;
    });

    /**
     * Create new object to fire $watch listener on highChart directive
     * ChartObj is not used because addSeries() function dont save customized attributes like id, isAdjusted, isIgnored.
     * Use chart.series because all data is kept as submitted.
     */
    $rootScope.$on('gims-chart-modified', function(event, chart) {
        $scope.chart = _.clone(chart);
    });

    /**************************************************************************/
    /* SCOPE FUNCTIONS ********************************************************/
    /**************************************************************************/

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
        $scope.ignoredElements = ignoredElements.ignoredElements;

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
            refresh(filters, false).then(function() {
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
        var hFilters = [];
        _.forEach($scope.indexedElements, function(questionnaire) {
            if (questionnaire.filters && questionnaire.filters[filter.filter.id]) {
                hFilters = hFilters.concat($scope.ignoreFilter(questionnaire.filters[filter.filter.id], !_.isUndefined(ignored) ? ignored : filter.filter.ignored, false));
                updateQuestionnaireIgnoredStatus(questionnaire);
            }
        });

        Chart.removeSeries(_.uniq(hFilters));
        retrieveFiltersAndValues(questionnaireId);
        refresh(_.uniq(hFilters), true);
    };

    /**
     * Ignore or include entire questionnaire
     * @param questionnaireId
     * @param ignore
     */
    $scope.toggleQuestionnaire = function(questionnaireId, ignore) {
        var questionnaire = ChartCache.cache($scope.tabs.part, questionnaireId);
        questionnaire.ignored = _.isUndefined(questionnaire.ignored) ? true : !questionnaire.ignored;

        var hFilters = [];
        _.forEach(questionnaire.filters, function(filter) {
            hFilters = hFilters.concat($scope.ignoreFilter(filter, !_.isUndefined(ignore) ? ignore : questionnaire.ignored, false));
        });

        Chart.removeSeries(_.uniq(hFilters));
        retrieveFiltersAndValues(questionnaireId);
        refresh(_.uniq(hFilters), true);
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
            var hFilters = _.keys(filter.filter.hFilters);

            if (refreshUrl) {
                Chart.removeSeries(hFilters);
                retrieveFiltersAndValues(questionnaireId);
                refresh(hFilters, true);
            }

            return hFilters;
        }

        return [];
    };

    $scope.getFirstAttribute = function(obj) {
        return {
            attribute: Object.keys(obj)[0],
            content: obj[Object.keys(obj)[0]]
        };
    };

    /**
     * Remove all series tagged as adjusted and reset form.
     */
    $scope.removeAdjustedSeries = function() {
        var adjustedSeries = _.uniq(_.pluck(_.filter($scope.chart.series, function(s) {
            if (s.isAdjusted) {
                return true;
            }
        }), 'id'));
        Chart.removeSeries(adjustedSeries, true);
        $scope.panelTabs.target = undefined;
        $scope.panelTabs.overridable = undefined;
        $scope.panelTabs.reference = undefined;
        refresh(adjustedSeries, false);
    };

    /**
     * Add adjusted series after removing old ones
     */
    $scope.addAdjustedSeries = function() {
        var adjustedSeries = _.uniq(_.pluck(_.filter($scope.chart.series, function(s) {
            if (s.isAdjusted) {
                return true;
            }
        }), 'id'));
        Chart.removeSeries(adjustedSeries, true);
        refresh([], false);
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
     * Calls ChartFilterController to recover filters data and values
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
    var refresh = function(filters, refreshUrl) {

        var deferred = $q.defer();

        var resetSeries = false;
        if (filters === null) {
            resetSeries = true;
            filters = _.pluck($scope.tabs.filters, 'id');
        }
        var ignoredElements = refreshUrl ? getIgnoredElements(refreshUrl).join(',') : $location.search().ignoredElements;

        if (_.isArray(filters)) {

            var queryParams = {
                geonames: $location.search().geonames,
                part: $scope.tabs.part.id,
                filters: filters.join(','),
                ignoredElements: ignoredElements,
                reference: $scope.panelTabs.reference ? $scope.panelTabs.reference.id ? $scope.panelTabs.reference.id : $scope.panelTabs.reference : null,
                target: $scope.panelTabs.target ? $scope.panelTabs.target : null,
                overridable: $scope.panelTabs.overridable ? $scope.panelTabs.overridable : null
            };

            Chart.refresh(queryParams, $scope.tabs.part, resetSeries).then(function(data) {
                computeEstimates(ignoredElements);
                deferred.resolve(data);
            });
        }

        return deferred.promise;
    };

    /**
     * Use chart to compute estimates and configure ng-grid for display
     * @param ignoredElements
     */
    $scope.gridOptions = {
        columnDefs: 'columnDefs',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 0})],
        data: 'estimatesData'
    };

    var computeEstimates = function(ignoredElements) {
        $scope.columnDefs = [
            {
                field: 'year',
                displayName: 'Year',
                enableColumnResize: true,
                width: '100px'
            }
        ];

        var estimatesObject = Chart.computeEstimates($scope.chart, ignoredElements);
        $scope.estimatesData = estimatesObject.data;
        $scope.columnDefs = $scope.columnDefs.concat(estimatesObject.columns);
    };

});
