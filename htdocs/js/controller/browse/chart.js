angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $q) {
    'use strict';

    $scope.Math = window.Math;
    $scope.usedFilters = {};
    $scope.ignoredElements = [];
    $scope.indexedElements = {};
    $scope.firstExecution = true;
    $scope.countryQueryParams = {perPage: 500};
    $scope.filterSetQueryParams = {fields: 'filters.genericColor,filters.children.__recursive'};

    /**
     * Executes when country, part or filterset are changed
     * When filter filterset is changed, rebuilds the list of the hFilters used.
     */
    $scope.$watch('{country:country.id, part:part.id, filterSet:filterSet}', function(newObj, oldObj) {
        if ($scope.country && $scope.part && $scope.filterSet) {

            // When filter filterset is changed, rebuilds the list of the hFilters used.
            // Needed to send only a request for all hFilters at once and then assign to filter to whith hFilter they are assigned.
            if (newObj.filterSet != oldObj.filterSet) {
                var newUsedFilters = {};
                _.forEach($scope.filterSet, function(filterSet) {
                    _.forEach(filterSet.filters, function(filter) {
                        newUsedFilters[filter.id] = filter.genericColor;
                    });
                });
                $scope.usedFilters = newUsedFilters;

                // remove all filters that are nos used by current usedFilters
                _.forEach($scope.indexedElements, function(questionnaire) { // select first questionnaire they return false to break and avoid to loop all questionnaires
                    questionnaire.hFilters = {};
                    _.forEach(questionnaire.filters, function(filter) {
                        var found = false;
                        _.forEach(filter.filter.hFilters, function(hFilter, hFilterId) {
                            if ($scope.usedFilters[hFilterId]) {
                                found = true;
                            }
                        });

                        if (!found) {
                            _.forEach($scope.indexedElements, function(questionnaireBis, questionnaireBisId) {
                                if (!_.isUndefined($scope.indexedElements[questionnaireBisId].filters)) {
                                    delete($scope.indexedElements[questionnaireBisId].filters[filter.filter.id]);
                                }
                            });
                        }
                    });
                    $scope.getIgnoredElements(true);
                    return false;
                });
            }

            var callback = function() {
                if ($scope.pointSelected) {
                    $scope.retrieveFiltersAndValues($scope.pointSelected.questionnaire);
                }
                $scope.initIgnoredElementsFromUrl();
            };
            $scope.refresh(false, callback);
        }
    }, true);

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
     * Calls ChartFilterController to recover filters data and values
     *
     * @param questionnaireId
     * @param callback
     */
    var retrieveFiltersAndValuesCanceler = null;
    $scope.retrieveFiltersAndValues = _.debounce(function(questionnaireId, callback) {
        if (questionnaireId && $scope.filterSet.length > 0) {
            var questionnaire = $scope.cache(questionnaireId);

            // only launch ajax request if the filters in this questionnaire don't have values
            if (!questionnaire.filters || $scope.concatenedIgnoredElements || !$scope.firstFilterHasValue(questionnaire)) {

                $scope.isLoading = true;
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
                            filters: _.keys($scope.usedFilters).join(','),
                            part: $scope.part.id,
                            fields: 'color',
                            getQuestionnaireUsages: questionnaire.usages && questionnaire.usages.length ? false : true,
                            ignoredElements: ignoredElements
                        }
                    }).success(function(data) {
                        _.forEach(data.filters, function(hFilter, hFilterId) {
                            _.map(data.filters[hFilterId], function(filter, index) {
                                if (!_.isUndefined($scope.indexedElements[questionnaireId].hFilters[hFilterId])) {
                                    filter.filter.sorting = index + 1;
                                    filter.filter.hFilters = {};
                                    filter.filter.hFilters[hFilterId] = null;
                                    $scope.cache({id: questionnaireId, usages: data.usages}, filter);
                                }
                            });
                        });

                        $scope.initiateEmptyQuestionnairesWithLoadedData(questionnaireId, callback);
                        $scope.getIgnoredElements(true);
                        $scope.isLoading = false;
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
                                    $scope.cache(tmpQuestionnaireId, {filter: filter.filter});
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
            if (filter && filter.values && filter.values[$scope.part.name]) {
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
                            $scope.cache(ignoredQuestionnaireId, {filter: {id: filterId}}, true);
                        });
                    } else {
                        _.forEach($scope.indexedElements[ignoredQuestionnaireId].filters, function(filter) {
                            if (filter) {
                                filter.filter.ignored = true;
                            }
                        });
                        $scope.cache(ignoredQuestionnaireId, null, true);
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
     * Calls CharController to refresh charts depending on new values
     *
     * @param refreshUrl Usualy set to true before sending request but at first execution is set to false
     * @param callback
     */
    $scope.refresh = function(refreshUrl, callback) {

        $scope.isLoading = true;
        var ignoredElements = refreshUrl ? $scope.getIgnoredElements(refreshUrl).join(',') : $location.search().ignoredElements;
        $scope.refreshChart(refreshUrl, ignoredElements, callback);
    };

    var refreshCanceler;
    $scope.refreshChart = _.debounce(function(refreshUrl, ignoredElements, callback) {
        //$scope.chart = null;
        var filterSets = _.map($scope.filterSet, function(filterSet) {
            return filterSet.id;
        });

        // get chart data via Ajax, but only once per 500 milliseconds
        // (this avoid sending several request on page loading)
        if (refreshCanceler) {
            refreshCanceler.resolve();
        }
        refreshCanceler = $q.defer();

        $scope.$apply(function() {
            $http.get('/api/chart', {
                timeout: refreshCanceler.promise,
                params: {
                    country: $scope.country.id,
                    part: $scope.part.id,
                    filterSet: filterSets.join(','),
                    ignoredElements: ignoredElements
                }
            }).success(function(data) {

                // implement tooltip formatter
                data.tooltip.formatter = function(x) {

                    // recover the template
                    var template = '';
                    template += this.series.tooltipOptions.headerFormat ? this.series.tooltipOptions.headerFormat : '';
                    template += this.series.tooltipOptions.pointFormat ? this.series.tooltipOptions.pointFormat : '';
                    template += this.series.tooltipOptions.footerFormat ? this.series.tooltipOptions.footerFormat : '';

                    // find all fields syntax {field}
                    var fields = template.match(/(\{.*?\})/g);

                    // replace the field by his value using this.field for {field} in formatter context
                    var evalValue = function(field){
                        return eval('this.'+field.substring(1, field.length - 1));
                    };

                    // self design patern to avoid "this" to be in the forEach context
                    var self = this;
                    _.forEach(fields, function(field) {
                        // recover value using formatter context
                        var value = evalValue.call(self, field);
                        // replace {field} tags by their value
                        template = template.replace(field, value);
                    });

                    // return template
                    return template;
                }

                data.plotOptions.scatter.dataLabels.formatter = function() {
                    var questionnaire = {hFilters: {}};
                    var ids = this.point.id.split(':');
                    questionnaire.id = ids[1];
                    questionnaire.name = this.point.name;
                    questionnaire.hFilters[ids[0]] = null;
                    $scope.cache(questionnaire);
                    return $('<span/>').css({color: this.series.color}).text(this.point.name)[0].outerHTML;
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
                $scope.chart = data;
                $scope.generateKeyIndicatorsTable(ignoredElements);
                $scope.isLoading = false;
            });
        });

    }, 500);

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
    $scope.generateKeyIndicatorsTable = function(ignoredElements) {
        var data = $scope.chart;
        $scope.isLoading = true;

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
        $scope.isLoading = false;
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
        $scope.refresh(true);
    };

    $scope.toggleQuestionnaire = function(questionnaireId, ignore) {
        var questionnaire = $scope.cache(questionnaireId);
        questionnaire.ignored = _.isUndefined(questionnaire.ignored) ? true : !questionnaire.ignored;

        _.forEach(questionnaire.filters, function(filter) {
            $scope.ignoreFilter(filter, !_.isUndefined(ignore) ? ignore : questionnaire.ignored, false);
        });

        $scope.retrieveFiltersAndValues(questionnaireId);
        $scope.refresh(true);
    };

    $scope.ignoreFilter = function(filter, ignored, refresh, questionnaireId) {
        if (filter) {
            filter.filter.ignored = ignored;
            if (refresh) {
                if (questionnaireId) {
                    $scope.retrieveFiltersAndValues(questionnaireId);
                }
                $scope.refresh(true);
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
    $scope.cache = function(questionnaire, filter, ignored) {
        if (!$scope.country || !$scope.part) {
            return [];
        }

        if (!$scope.indexedElements) {
            $scope.indexedElements = {};
        }

        // initiates high filter index and retrieves name for display in panel
        questionnaire = $scope.indexQuestionnaireCache(questionnaire, ignored && !filter ? ignored : false);
        $scope.indexQuestionnaireFilterCache(questionnaire.id, filter, ignored);
        return questionnaire;
    };

    $scope.indexQuestionnaireCache = function(questionnaire, ignored) {
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

    $scope.indexQuestionnaireFilterCache = function(questionnaireId, filter, ignored) {
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
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].values[$scope.part.name] = filter.values[0][$scope.part.name];
            }
            if (!_.isUndefined(filter.valuesWithoutIgnored)) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored[$scope.part.name] = filter.valuesWithoutIgnored[0][$scope.part.name];
            } else {
                delete($scope.indexedElements[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored[$scope.part.name]);
            }
        }
    };

});
