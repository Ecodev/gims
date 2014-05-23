angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $q) {
    'use strict';

    $scope.chart = {};
    $scope.chartObj = {};
    $scope.tabs = {};
    $scope.panelTabs = {};
    $scope.ignoredElements = [];
    $scope.indexedElements = {};
    $scope.firstExecution = true;
    $scope.tabs.countryParams = {perPage: 500};
    $scope.tabs.filterSetParams = {fields: 'filters.genericColor,filters.color'};
    $scope.tabs.filterParams = {fields: 'paths,color,genericColor', itemOnce: 'true'};
    $scope.tabs.filtersTemplate = "" +
            "<div>" +
            "<div class='col-sm-4 col-md-4 select-label select-label-with-icon'>" +
            "    <i class='fa fa-gims-filter' style='color:[[item.color]];' ></i> [[item.name]]" +
            "</div>" +
            "<div class='col-sm-7 col-md-7'>" +
            "    <small>" +
            "       [[_.map(item.paths, function(path){return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]" +
            "    </small>" +
            "</div>" +
            "<div class='clearfix'></div>" +
            "</div>";

    $scope.tabs.filtersTemplate2 = "" +
            "<div>" +
            "<div class='col-sm-12 col-md-12 select-label select-label-with-icon'>" +
            "    <i class='fa fa-gims-filter' style='color:[[item.genericColor]];' ></i> [[item.name]]" +
            "</div>" +
            "<div class='clearfix'></div>" +
            "</div>";

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

    $scope.getFiltersIds = function() {
        return _.pluck($scope.tabs.filters, 'id').join(',');
    };

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
            _.forEach(filterSet.filters, function(filter) {
                if (!_.find(filters, {id: filter.id})) {
                    filters.push(filter);
                }
            });
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
        removeSeries(removedFilters);

        if (addedFilters.length > 0) {
            initIgnoredElements(addedFilters);
        }

        if ($scope.pointSelected) {
            $scope.retrieveFiltersAndValues($scope.pointSelected.questionnaire);
        }

    });

    /**
     * Executes when country, part or filter set are changed
     */
    $scope.$watch('{country:tabs.country.id, part:tabs.part.id}', function() {
        initIgnoredElements(null);
    }, true);

    var initIgnoredElements = function(filters) {
        if ($scope.tabs.country && $scope.tabs.part && $scope.tabs.filters) {

            var callback = function() {
                if ($scope.pointSelected) {
                    $scope.retrieveFiltersAndValues($scope.pointSelected.questionnaire);
                }
                $scope.initIgnoredElementsFromUrl();
            };
            $scope.refresh(filters, false, callback);
        }
    };

    $scope.$watch('panelOpened', function() {
        $timeout(function() {
            jQuery(window).resize();
        }, 350); // 350 to resize after animation of panel

        $location.search('panelOpened', $scope.panelOpened ? true : undefined);
    });

    /**
     * Executes when a point is selected
     */
    $scope.$watch('pointSelected', function(pointSelected) {
        // We throw an window.resize event to force Highcharts to reflow and adapt to its new size
        $timeout(function() {
            jQuery(window).resize();
        }, 350); // 350 to resize after animation of panel

        if (pointSelected) {
            // select point and then recover the cached questionnaire by reference
            $scope.retrieveFiltersAndValues($scope.pointSelected.questionnaire);
        }
    });

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
     * Get one of selected filters by id, to recover its color from template
     * @param id
     * @returns {*}
     */
    $scope.getHFilter = function(id) {
        return _.find($scope.tabs.filters, function(f) {
            if (f.id == id) {
                return true;
            }
        });
    };

    /**
     * Calls ChartFilterController to recover filters data and values
     *
     * @param questionnaireId
     * @param callback
     */
    var retrieveFiltersAndValuesCanceler = null;
    $scope.retrieveFiltersAndValues = _.debounce(function(questionnaireId, callback) {
        if (questionnaireId && $scope.tabs.filters.length > 0) {
            var questionnaire = cache(questionnaireId);

            // only launch ajax request if the filters in this questionnaire don't have values
            if (!questionnaire.filters || $scope.concatenedIgnoredElements || !$scope.firstFilterHasValue(questionnaire)) {

                var ignoredElements = $scope.concatenedIgnoredElements ? $scope.concatenedIgnoredElements.join(',') : '';

                if (retrieveFiltersAndValuesCanceler) {
                    retrieveFiltersAndValuesCanceler.resolve();
                }
                retrieveFiltersAndValuesCanceler = $q.defer();

                $scope.$apply(function() {
                    $http.get('/api/chart/getPanelFilters', {
                        timeout: retrieveFiltersAndValuesCanceler.promise,
                        params: {
                            questionnaire: questionnaireId,
                            filters: _.pluck($scope.tabs.filters, 'id').join(','),
                            part: $scope.tabs.part.id,
                            fields: 'color',
                            getQuestionnaireUsages: questionnaire.usages && questionnaire.usages.length ? false : true,
                            ignoredElements: ignoredElements
                        }
                    }).success(function(data) {
                            _.forEach(data.filters, function(hFilter, hFilterId) {
                                _.forEach(data.filters[hFilterId], function(filter, index) {
                                    if (!_.isUndefined($scope.indexedElements[questionnaireId].hFilters[hFilterId])) {
                                        filter.filter.sorting = index + 1;
                                        filter.filter.hFilters = {};
                                        filter.filter.hFilters[hFilterId] = null;
                                        cache({id: questionnaireId, usages: data.usages}, filter);
                                    }
                                });
                            });

                            $scope.initiateEmptyQuestionnairesWithLoadedData(questionnaireId, callback);
                            $scope.getIgnoredElements(true);
                        });
                });
            }
        }
    }, 500);

    /**
     *   This function set initiates all questionnaires with data loaded with first one (except values)
     *   It allows to see ignored elements if they are ignored globally before having loading the data specific to asked questionnaire
     *   The data is too big to be executed on the fly and is not needed, so the timeout waits until angularjs generates page.
     *   Then generates index in background
     *
     *   @param questionnaireId Reference questionnaire id
     *   @param callback function to execute when data is indexed (usefull for then change status to ignored if they're ignored in url)
     */
    $scope.initiateEmptyQuestionnairesWithLoadedData = function(questionnaireId, callback) {
        if (questionnaireId) {
            var questionnaire = $scope.indexedElements[questionnaireId];
            if ($scope.firstExecution) {
                $scope.firstExecution = false;
                _.forEach($scope.indexedElements, function(tmpQuestionnaire, tmpQuestionnaireId) {
                    if (tmpQuestionnaireId != questionnaireId) {
                        _.forEach(tmpQuestionnaire.hFilters, function(hFilter, hFilterId) {
                            _.forEach(questionnaire.filters, function(filter) {
                                if (filter && !_.isUndefined(filter.filter.hFilters[hFilterId])) {
                                    cache(tmpQuestionnaireId, {filter: filter.filter});
                                }
                            });
                        });
                    }
                });

                if (callback) {
                    callback();
                }
            }
        }
    };

    /**
     * As filters are stored in an array on index relative to their Id, this function gets first filter to verify is he has a value.
     * Is used to determine if we need to send request to recover values.
     *
     * @param questionnaire
     * @returns {boolean}
     */
    $scope.firstFilterHasValue = function(questionnaire) {
        var hasValue = false;
        _.forEach(questionnaire.filters, function(filter) {
            if (filter && filter.values && filter.values[$scope.tabs.part.name]) {
                hasValue = true;
                return false;
            }
        });
        return hasValue;
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
    $scope.concatenedIgnoredElements = [];
    $scope.getIgnoredElements = function(refreshUrl) {

        if (!$scope.indexedElements) {
            return [];
        }

        $scope.ignoredElements = {};
        $scope.globalIndexedFilters = {}; // is used to know if a same filter is ignored or not in all questionnaires (used on globally ignored button)
        var concatenedIgnoredElements = [];

        // browse each questionnaire
        _.forEach($scope.indexedElements, function(questionnaire, questionnaireId) {
            var ignoredElementsForQuestionnaire = [];
            if (questionnaire.filters) {
                questionnaire.ignored = true;
            }
            questionnaire.hasIgnoredFilters = false;

            // browse each filter of questionnaire
            _.forEach(questionnaire.filters, function(filter) {
                if (filter) {

                    // report globally ignored filter status on $scope.globalIndexedFilters
                    // false = filter never ignored
                    // true = filter ignored on all questionnaires
                    // null = filter sometimes ignored
                    if (_.isUndefined(filter.filter.ignored)) {
                        filter.filter.ignored = false;
                    }
                    if (_.isUndefined($scope.globalIndexedFilters[filter.filter.id])) {
                        $scope.globalIndexedFilters[filter.filter.id] = filter.filter.ignored;
                    } else if (filter.filter.ignored != $scope.globalIndexedFilters[filter.filter.id] && !_.isNull($scope.globalIndexedFilters[filter.filter.id])) {
                        $scope.globalIndexedFilters[filter.filter.id] = null;
                    }

                    if (filter.filter.ignored) {
                        questionnaire.hasIgnoredFilters = true;
                        ignoredElementsForQuestionnaire.push(filter.filter.id);
                    } else {
                        questionnaire.ignored = false;
                    }
                }
            });

            if (ignoredElementsForQuestionnaire.length > 0) {
                if (questionnaire.ignored) {
                    concatenedIgnoredElements.push(questionnaireId);
                    $scope.ignoredElements[questionnaireId] = [];
                } else {
                    concatenedIgnoredElements.push(questionnaireId + ':' + ignoredElementsForQuestionnaire.join('-'));
                    $scope.ignoredElements[questionnaireId] = ignoredElementsForQuestionnaire;
                }
            }
        });

        if (concatenedIgnoredElements.length > 0) {
            $scope.hasIgnoredElements = true;
            if (refreshUrl) {
                $location.search('ignoredElements', concatenedIgnoredElements.join(','));
            }
        } else {
            $scope.hasIgnoredElements = false;
            if (refreshUrl) {
                $location.search('ignoredElements', null);
            }
        }

        $scope.concatenedIgnoredElements = concatenedIgnoredElements;
        return $scope.concatenedIgnoredElements;
    };

    /**
     * Retrieve ignored elements in the url following this operations.
     * This function is called after chart has been loaded.
     *
     * 1) If there are some elements ignored :
     * 2) Retrieve data (filter structure with informations -> name, and values)
     * 3) When request has come back, initiate all questionnaires to allow data display on ignored elements (mainly filter's name).
     */
    $scope.initIgnoredElementsFromUrl = function() {

        // url excluded questionnaires
        var ignoredQuestionnaires = $location.search().ignoredElements ? $location.search().ignoredElements.split(',') :
            [];

        if (ignoredQuestionnaires.length > 0) {

            var callback = function(ignoredQuestionnaires) {
                _.forEach(ignoredQuestionnaires, function(ignoredElement) {
                    var questionnaireDetail = ignoredElement.split(':');
                    var ignoredQuestionnaireId = questionnaireDetail[0];
                    var ignoredFilters = questionnaireDetail[1] ? questionnaireDetail[1].split('-') : null;
                    if (ignoredFilters && ignoredFilters.length > 0) {
                        _.forEach(ignoredFilters, function(filterId) {
                            cache(ignoredQuestionnaireId, {filter: {id: filterId}}, true);
                        });
                    } else {
                        _.forEach($scope.indexedElements[ignoredQuestionnaireId].filters, function(filter) {
                            if (filter) {
                                filter.filter.ignored = true;
                            }
                        });
                        cache(ignoredQuestionnaireId, null, true);
                    }

                    $scope.getIgnoredElements(false);
                });

                $timeout(function() {
                    jQuery(window).resize();
                }, 350);
            };

            var firstQuestionnaire = ignoredQuestionnaires[0].split(':');
            $scope.retrieveFiltersAndValues(firstQuestionnaire[0], function() {
                callback(ignoredQuestionnaires);
            });
        }
    };

    /**
     * Calls ChartController to refresh charts depending on new values
     *
     * @param refreshUrl Usually set to true before sending request but at first execution is set to false
     * @param callback
     */
    $scope.refresh = function(filters, refreshUrl, callback) {
        var ignoredElements = refreshUrl ? $scope.getIgnoredElements(refreshUrl).join(',') : $location.search().ignoredElements;
        return $scope.refreshChart(filters, ignoredElements, callback);
    };

    // get chart data via Ajax, but only once per 500 milliseconds
    // (this avoid sending several request on page loading)
    var refreshCanceler;
    $scope.refreshChart = _.debounce(function(filters, ignoredElements, callback) {

        var resetSeries = false;
        if (filters === null) {
            resetSeries = true;
            filters = _.pluck($scope.tabs.filters, 'id');
        }

        if (filters.length > 0) {

            if (refreshCanceler) {
                refreshCanceler.resolve();
            }
            refreshCanceler = $q.defer();

            $scope.$apply(function() {
                $http.get('/api/chart', {
                    timeout: refreshCanceler.promise,
                    params: {
                        country: $scope.tabs.country.id,
                        part: $scope.tabs.part.id,
                        filters: filters.join(','),
                        ignoredElements: ignoredElements,
                        reference: $scope.panelTabs.reference ? $scope.panelTabs.reference.id ? $scope.panelTabs.reference.id : $scope.panelTabs.reference : null,
                        target: $scope.panelTabs.target ? $scope.panelTabs.target : null,
                        overridable: $scope.panelTabs.overridable ? $scope.panelTabs.overridable : null
                    }
                }).success(function(data) {

                        // implement tooltip formatter
                        data.tooltip.formatter = function() {
                            return highChartTooltipFormatter.call(this);
                        };

                        data.plotOptions.scatter.dataLabels.formatter = function() {
                            return highChartScatterFormatter.call(this);
                        };

                        data.plotOptions.scatter.point = {
                            events: {
                                click: function(e) {
                                    var ids = e.currentTarget.id.split(':');
                                    $scope.setPointSelected(e.currentTarget.id, e.currentTarget.questionnaire, e.currentTarget.name, ids[0]);
                                    $scope.$apply(); // this is needed because we are outside the AngularJS context (highcharts uses jQuery event handlers)
                                }
                            }
                        };

                        if (callback) {
                            callback();
                        }

                        if (resetSeries) {
                            $scope.chart.series = [];
                        }

                        var actualSeries = [];
                        if ($scope.chart && $scope.chart.series.length) {
                            actualSeries = $scope.chart.series;
                        }

                        var newSeries = data.series;
                        delete(data.series);
                        $scope.chart = data;
                        $scope.chart.series = actualSeries;
                        addSeries(newSeries);

                        $scope.computeEstimates(ignoredElements);
                    });
            });
        }

    }, 500);

    var highChartTooltipFormatter = function() {
        // recover the template
        var template = '';
        template += this.series.tooltipOptions.headerFormat ? this.series.tooltipOptions.headerFormat : '';
        template += this.series.tooltipOptions.pointFormat ? this.series.tooltipOptions.pointFormat : '';
        template += this.series.tooltipOptions.footerFormat ? this.series.tooltipOptions.footerFormat : '';

        // find all fields syntax {field}
        var fields = template.match(/(\{.*?\})/g);

        // replace the field by his value using this.field for {field} in formatter context
        var evalValue = function(field) {
            return eval('this.' + field.substring(1, field.length - 1));
        };

        // self design pattern to avoid "this" to be in the forEach context
        var self = this;
        _.forEach(fields, function(field) {
            // recover value using formatter context
            var value = evalValue.call(self, field);

            if (_.isUndefined(value) || _.isNull(value)) {
                value = '';
            }

            // replace {field} tags by their value
            template = template.replace(field, value);
        });

        // return template
        return template;
    };

    var highChartScatterFormatter = function() {
        var questionnaire = {hFilters: {}};
        var ids = this.point.id.split(':');
        questionnaire.id = ids[1];
        questionnaire.name = this.point.name;
        questionnaire.hFilters[ids[0]] = null;
        cache(questionnaire);
        return $('<span/>').css({color: this.series.color}).text(this.point.name)[0].outerHTML;
    };

    /**
     * Add series and update inserted ones
     * @param series
     */
    var addSeries = function(seriesToAdd) {

        if (seriesToAdd.length > 0) {

            var data = _.clone($scope.chart);

            _.forEachRight(seriesToAdd, function(serieToAdd, index) {
                // if exist, remove and add new one
                if (_.contains(data.series, {id: serieToAdd.id, isIgnored: serieToAdd.isIgnored})) {
                    data.series.splice(index, 1);
                }
                data.series.push(serieToAdd);
            });

            $scope.chart = data;
        }
    };

    /**
     * Remove passed series
     * @param series
     */
    var removeSeries = function(seriesToRemove, affectAdjusted) {

        if (seriesToRemove.length > 0) {

            if (_.isUndefined(affectAdjusted)) {
                affectAdjusted = false;
            }

            var data = _.clone($scope.chart);
            _.forEach(seriesToRemove, function(serieToRemoveId) {
                _.forEachRight(data.series, function(existingSerie, index) {
                    if (existingSerie.id == serieToRemoveId && (affectAdjusted && existingSerie.isAdjusted || !affectAdjusted && !existingSerie.isAdjusted)) {
                        data.series.splice(index, 1);
                    }
                });
            });

            $scope.chart = data;
        }
    };

    $scope.removeAdjustedSeries = function() {
        var adjustedSeries = _.uniq(_.pluck(_.filter($scope.chart.series, function(s) {
            if (s.isAdjusted) {
                return true;
            }
        }), 'id'));
        removeSeries(adjustedSeries, true);
        $scope.panelTabs.target = undefined;
        $scope.panelTabs.overridable = undefined;
        $scope.panelTabs.reference = undefined;
        $scope.refresh(null, false);
    };

    $scope.setPointSelected = function(id, questionnaireId, name, filterId) {
        $scope.pointSelected = {
            id: id,
            questionnaire: questionnaireId,
            name: name,
            filter: filterId
        };
    };

    $scope.gridOptions = {
        columnDefs: 'columnDefs',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 0})],
        data: 'data'
    };
    $scope.computeEstimates = function(ignoredElements) {
        var data = $scope.chart;

        $scope.columnDefs = [
            {
                field: 'year',
                displayName: 'Year',
                enableColumnResize: true,
                width: '100px'
            }
        ];

        var arrayData = [];
        _.forEach(data.series, function(serie) {
            if (serie.type == 'line' && ((_.isUndefined(ignoredElements) || ignoredElements.length === 0) && _.isUndefined(serie.isIgnored) || ignoredElements.length > 0 && serie.isIgnored === true)) {

                // create a column by filter on graph
                $scope.columnDefs.push({
                    field: 'value' + serie.id,
                    displayName: serie.name,
                    enableColumnResize: true,
                    bgcolor: serie.color,
                    headerCellTemplate: '' + '<div class="ngHeaderSortColumn {{col.headerClass}}" ng-style="{\'cursor\': col.cursor}" ng-class="{ \'ngSorted\': !noSortVisible }">' + '   <div ng-class="\'colt\' + col.index" class="ngHeaderText" style="background:{{col.colDef.bgcolor}}" popover-placement="top" popover="{{col.displayName}}">' + '       {{col.displayName}}' + '   </div>' + '</div>',
                    cellTemplate: '' + '<div class="ngCellText text-right" ng-class="col.colIndex()"><span ng-cell-text ng-show="{{row.entity.value' + serie.id + '!==null}}">{{row.entity.value' + serie.id + '}} %</span></div>'
                });

                // retrieve data
                _.forEach(serie.data, function(value, index) {
                    if (_.isUndefined(arrayData[index])) {
                        arrayData[index] = {};
                    }
                    arrayData[index]['value' + serie.id] = value;
                });

            }
        });

        var startYear = data.plotOptions.line.pointStart;
        // before adding date to row, create and object with same properties but all to null
        var nullEquivalentData = _.mapValues(arrayData[0], function() {
            return null;
        });
        arrayData = _.map(arrayData, function(row, index) {
            row.year = startYear + index;
            return row;
        });

        // use the equivalent null object to keep all except with null objects
        arrayData = _.rest(arrayData, nullEquivalentData);

        // remove useless dates
        var finalData = [];
        angular.forEach(arrayData, function(row, index) {
            if (row.year % 5 === 0 && index < arrayData.length || index == arrayData.length - 1) {
                finalData.push(arrayData.splice(index, 1)[0]);
            }
        });
        $scope.data = finalData;
    };

    /**
     *  Manage filters ignored actions
     */
    $scope.reportStatusGlobally = function(filter, ignored, questionnaireId) {
        _.forEach($scope.indexedElements, function(questionnaire) {
            if (questionnaire.filters && questionnaire.filters[filter.filter.id]) {
                $scope.ignoreFilter(questionnaire.filters[filter.filter.id], !_.isUndefined(ignored) ? ignored : filter.filter.ignored, false);
                $scope.updateQuestionnaireIgnoredStatus(questionnaire);
            }
        });

        $scope.retrieveFiltersAndValues(questionnaireId);
        $scope.refresh(null, true);
    };

    $scope.toggleQuestionnaire = function(questionnaireId, ignore) {
        var questionnaire = cache(questionnaireId);
        questionnaire.ignored = _.isUndefined(questionnaire.ignored) ? true : !questionnaire.ignored;

        _.forEach(questionnaire.filters, function(filter) {
            $scope.ignoreFilter(filter, !_.isUndefined(ignore) ? ignore : questionnaire.ignored, false);
        });

        $scope.retrieveFiltersAndValues(questionnaireId);
        $scope.refresh(null, true);
    };

    $scope.ignoreFilter = function(filter, ignored, refresh, questionnaireId) {
        if (filter) {
            filter.filter.ignored = ignored;
            if (refresh) {
                if (questionnaireId) {
                    $scope.retrieveFiltersAndValues(questionnaireId);
                }
                $scope.refresh(null, true);
            }
        }
    };

    $scope.updateQuestionnaireIgnoredStatus = function(questionnaire) {
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

    $scope.getFirstAttribute = function(obj) {
        return {
            attribute: Object.keys(obj)[0],
            content: obj[Object.keys(obj)[0]]
        };
    };

    /**
     * Put in $scope.indexedElements the state of all objects that have been selected / viewed / ignored.
     *
     * The cache is a index table.
     *
     * Feed an object $scope.indexedElements that is structured as followed :
     *
     * {
     *     23 : { // --> questionnaire ID
     *         id : '74:02',
     *         name : '...'
     *         (ignoredGlobally : true,)
     *         filters : {
     *           2 : {
     *             id : xx,
     *             level : xx,
     *             name : '...'
     *             value : {
     *                 rural : xxx,
     *                 urban : xxx,
     *                 total : xxx
     *             }
     *             (ignoredGlobally : true)
     *             (ignored : true)
     *           },
     *           28 : {...}
     *           39 : {...}
     *         }
     *     }
     * }
     *
     * Some attributes like highFilter.name and questionnaire.name/code are loaded by ajax
     * These attributes correspond to objects mentionned in the url (highFilter and questionnaire).
     * They name attribute are loaded by ajax cause they're not specified in the url and are needed for display
     * The app can't wait the user to click on a point to retrieve this data from pointSelected.name attribute
     *
     * @param questionnaireId
     * @param filter
     * @param questionnaireName
     * @param ignored
     * @returns current cached highFilter object
     */
    var cache = function(questionnaire, filter, ignored) {
        if (!$scope.tabs.country || !$scope.tabs.part) {
            return [];
        }

        if (!$scope.indexedElements) {
            $scope.indexedElements = {};
        }

        // initiates high filter index and retrieves name for display in panel
        questionnaire = indexQuestionnaire(questionnaire, ignored && !filter ? ignored : false);
        indexQuestionnaireAndFilter(questionnaire.id, filter, ignored);
        return questionnaire;
    };

    var indexQuestionnaire = function(questionnaire, ignored) {
        if (questionnaire) {

            if (!_.isObject(questionnaire)) {
                questionnaire = {id: questionnaire};
            }
            if (!$scope.indexedElements[questionnaire.id]) {
                $scope.indexedElements[questionnaire.id] = {};
            }

            if (questionnaire.id) {
                $scope.indexedElements[questionnaire.id].id = questionnaire.id;
            }
            if (questionnaire.name) {
                $scope.indexedElements[questionnaire.id].name = questionnaire.name;
            }

            if (!$scope.indexedElements[questionnaire.id].hFilters) {
                $scope.indexedElements[questionnaire.id].hFilters = {};
            }

            // assigns root filters to which this filter belongs to.
            _.forEach(questionnaire.hFilters, function(hFilter, hFilterId) {
                $scope.indexedElements[questionnaire.id].hFilters[hFilterId] = hFilter;
            });

            if (questionnaire.usages) {
                $scope.indexedElements[questionnaire.id].usages = questionnaire.usages;
            }

            if (ignored) {
                $scope.indexedElements[questionnaire.id].ignored = true;
            }
            return $scope.indexedElements[questionnaire.id];
        }
    };

    var indexQuestionnaireAndFilter = function(questionnaireId, filter, ignored) {
        if (filter) {
            if (!$scope.indexedElements[questionnaireId].filters) {
                $scope.indexedElements[questionnaireId].filters = {};
            }

            if (!$scope.indexedElements[questionnaireId].filters[filter.filter.id]) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id] = {};
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter = {};
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].values = {};
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored = {};
            }

            if (filter.filter.id) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.id = filter.filter.id;
            }
            if (filter.filter.name) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.name = filter.filter.name;
            }
            if (filter.filter.color) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.color = filter.filter.color;
            }
            if (filter.filter.originalDenomination) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.originalDenomination = filter.filter.originalDenomination;
            }

            if (!$scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters = {};
            }

            if (filter.usages) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].usages = filter.usages;
            }

            // assigns root filters to which this filter belongs to.
            _.forEach(filter.filter.hFilters, function(hFilter, hFilterId) {
                if (_.isNull(hFilter)) {

                    if (!$scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId]) {
                        $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId] = {};
                    }

                    if (filter.filter.level >= 0) {
                        $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId].level = filter.filter.level;
                    }
                    if (filter.filter.sorting) {
                        $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId].sorting = filter.filter.sorting;
                    }
                } else {
                    $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId] = hFilter;
                }
            });

            // if no ignored params and no ignored status specified on filter but questionnaire has one, filter inherits questionnaire status
            if (_.isUndefined(ignored) && !_.isUndefined($scope.indexedElements[questionnaireId].ignored) && _.isUndefined($scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignored)) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignored = $scope.indexedElements[questionnaireId].ignored;

                // if ignored param specified, filter gets its value
            } else if (!_.isUndefined(ignored)) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignored = ignored;
            }

            if (filter.values) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].values[$scope.tabs.part.name] = filter.values[0][$scope.tabs.part.name];
            }
            if (!_.isUndefined(filter.valuesWithoutIgnored)) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored[$scope.tabs.part.name] = filter.valuesWithoutIgnored[0][$scope.tabs.part.name];
            } else {
                delete($scope.indexedElements[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored[$scope.tabs.part.name]);
            }
        }
    };

});
