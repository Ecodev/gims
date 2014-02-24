angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $q) {
    'use strict';

    $scope.Math = window.Math;
    $scope.chartObj;
    $scope.pointSelected;
    $scope.ignoredElements;
    $scope.indexedElements = {};
    $scope.firstExecution = true;
    $scope.countryQueryParams = {perPage:1000}
    $scope.filterSetQueryParams = {fields: 'filters,filters.genericColor,filters.officialChildren,filters.officialChildren.officialChildren,filters.officialChildren.officialChildren.officialChildren,filters.officialChildren.officialChildren.officialChildren.officialChildren'}

    //    $scope.i = 0;
    //    $scope.test = function(){
    ////        $scope.canceler = $q.defer();
    ////        Restangular.all('chart/test').withHttpConfig({timeout: $scope.canceler.promise}).getList().then(function(data){
    ////            console.log('test response : ', data);
    ////        });
    //
    //        var asdf = _.debounce(function(){
    //            $scope.i++;
    //            console.log('print ', $scope.i);
    //            return i;
    //        }, 2000);
    //
    //        var i = asdf();
    //        console.log('click', i);
    //    }
    //
    //
    //    $scope.print = function() {
    //        $scope.i++;
    //        console.log('print ', $scope.i);
    //        return $scope.i;
    //    }
    //    $scope.stopTest = function()
    //    {
    //        console.log('stop test', $scope.canceler.resolve());
    //    }

    /**
     * Executes when country, part or filterset are changed
     * When filter filterset is changed, rebuilds the list of the hFilters used.
     */
    var uniqueAjaxRequest;
    $scope.$watch('{country:country.id, part:part.id, filterSet:filterSet}', function(newObj, oldObj) {
        if ($scope.country && $scope.part && $scope.filterSet) {

            // When filter filterset is changed, rebuilds the list of the hFilters used.
            // Needed to send only a request for all hFilters at once and then assign to filter to whith hFilter they are assigned.
            if (newObj.filterSet != oldObj.filterSet) {
                $scope.usedFilters = {};
                _.forEach($scope.filterSet, function(filterSet) {
                    _.forEach(filterSet.filters, function(filter) {
                        $scope.usedFilters[filter.id] = filter.genericColor;
                    });
                });
            }

            // allow to reset everything that is no more related to selection
            // all subobjects of the oldObj have to be not null. If they are that means that it's the first display, so don't reset excluded elements. The other vars are already empty
            if (oldObj.country && oldObj.part && oldObj.filterSet && (newObj.country != oldObj.country || newObj.part != oldObj.part || newObj.filterSet != oldObj.filterSet)) {
                $scope.indexedElements = {};
                $scope.pointSelected = null;
            }

            $scope.refreshChart(false, $scope.initIgnoredElementsFromUrl);
        }
    }, true);

    /**
     * Executes when a point is selected
     */
    $scope.$watch('pointSelected', function(pointSelected) {
        // We throw an window.resize event to force Highcharts to reflow and adapt to its new size
        $timeout(function() {
            jQuery(window).resize();
        }, 0);

        if (pointSelected) {
            // select point and then recover the cached questionnaire by reference
            //console.log('point selected');
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
    $scope.sort = function(filtersObj, hFilterId){
        if (!filtersObj) return [];
        var sortable = [];
        for (var filterIndex in filtersObj) {
            if (filtersObj[filterIndex].filter.hFilters[hFilterId]){
                sortable.push(filtersObj[filterIndex])
            }
        }
        sortable.sort(function(a, b) {
            return a.filter.hFilters[hFilterId].sorting - b.filter.hFilters[hFilterId].sorting
        });
        sortable = _.map(sortable, function(filter){return filter.filter.id});
        return sortable;
    }

    /**
     * Calls ChartFilterController to recover filters data and values
     *
     * @param questionnaireId
     * @param callback
     */
    $scope.retrieveFiltersAndValues = function(questionnaireId, callback) {
//        console.log('retrieve filters and values ');
        if (questionnaireId) {
            var questionnaire = $scope.cache(questionnaireId);

            // only launch ajax request if the filters in this questionnaire don't have values
            if (!questionnaire.filters || !$scope.firstFilterHasValue(questionnaire)) {

                var usedFiltersIds = [];
                _.forEach($scope.usedFilters, function(filter, filterId) {
                    usedFiltersIds.push(filterId)
                })

                $scope.isLoading = true;
                $http.get('/api/chart/getPanelFilters', {
                    params: {
                        questionnaire: questionnaireId,
                        filters: usedFiltersIds.join(','),
                        part: $scope.part.id,
                        fields: 'color'
                    }
                }).success(function(data) {
//                        console.log('data received', data);
                        _.forEach(data, function(hFilter, hFilterId) {
                            _.map(data[hFilterId], function(filter, index) {
                                if (!_.isUndefined($scope.indexedElements[questionnaireId].hFilters[hFilterId])) {
                                    filter.filter.sorting = index + 1;
                                    filter.filter.hFilters = {};
                                    filter.filter.hFilters[hFilterId] = null;
                                    $scope.cache(questionnaireId, filter);
                                }
                            });
                        });
//                        console.log('first questionnaire setted');
                        $scope.initiateEmptyQuestionnairesWithLoadedData(questionnaireId, callback);
                        $scope.isLoading = false;
                    });
            }
        }
    }

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
//        console.log('initiate other questionnaires');

        if (questionnaireId) {
            var questionnaire = $scope.indexedElements[questionnaireId];

            if($scope.firstExecution) {
                $scope.firstExecution = false;
            //$timeout(function() {
            _.forEach($scope.indexedElements, function(tmpQuestionnaire, tmpQuestionnaireId) {
                if (tmpQuestionnaireId != questionnaireId) {
                    _.forEach(tmpQuestionnaire.hFilters, function(hFilter, hFilterId){
                        _.forEach(questionnaire.filters, function(filter) {
                            if (filter && !_.isUndefined(filter.filter.hFilters[hFilterId])) {
                                $scope.cache(tmpQuestionnaireId, {filter: filter.filter});
                            }
                        })
                    })
                }
            });

            if (callback) {
                callback();
            }
            $scope.getIgnoredElements(true);
            //}, 1500);
            }
        }
    }

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
    }

    /**
     * Inspect $scope.indexedElements to find ignored elements and update Url if refreshUrl is set to true.
     *
     * When all filters for a questionnaire are ignored, the questionnaire is considered as ignored.
     * Set $scope.ignoredElements to true of false to allow or not panel display
     *
     * @param refreshUrl
     * @returns {Array}
     */
    $scope.getIgnoredElements = function(refreshUrl) {

//        console.log('get ignored elements');
        if (!$scope.indexedElements) {
            return [];
        }

        $scope.ignoredElements = {};
        var concatenedIgnoredElements = [];
        $scope.globalIndexedFilters = {};

        // browse each questionnaire
        _.forEach($scope.indexedElements, function(questionnaire, questionnaireId) {
            var ignoredElementsForQuestionnaire = [];
            questionnaire.ignored = true;
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
                    $scope.ignoredElements.push(questionnaireId);
                } else {
                    concatenedIgnoredElements.push(questionnaireId + ':' + ignoredElementsForQuestionnaire.join('-'));
                    $scope.ignoredElements[questionnaireId] = ignoredElementsForQuestionnaire;// = .push({q : questionnaireId, f : ignoredElementsForQuestionnaire})
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

//        console.log('end of get ignored elements');
        return concatenedIgnoredElements;
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
//        console.log('init from url');
        // url excluded questionnaires
        var ignoredQuestionnaires = $location.search()['ignoredElements'] ? $location.search()['ignoredElements'].split(',') :
            [];

        if (ignoredQuestionnaires.length > 0) {

            var callback = function(ignoredQuestionnaires) {
                _.forEach(ignoredQuestionnaires, function(ignoredElement) {
                    var questionnaireDetail = ignoredElement.split(':');
                    var ignoredQuestionnaireId = questionnaireDetail[0];
                    var ignoredFilters = questionnaireDetail[1] ? questionnaireDetail[1].split('-') : null;

                    if (ignoredFilters) {
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
                });

                $timeout(function() {
                    jQuery(window).resize();
                }, 0)
            }

            var firstQuestionnaire = ignoredQuestionnaires[0].split(':');
            $scope.retrieveFiltersAndValues(firstQuestionnaire[0], function() {
                callback(ignoredQuestionnaires)
            });
        }
    }

    /**
     * Calls CharController to refresh charts depending on new values
     *
     * @param refreshUrl Usualy set to true before sending request but at first execution is set to false
     * @param callback
     */
    $scope.refreshChart = function(refreshUrl, callback) {
//        console.log('refresh chart');
        $scope.isLoading = true;
        $timeout.cancel(uniqueAjaxRequest);
        var ignoredElements = refreshUrl ? $scope.getIgnoredElements(refreshUrl).join(',') : $location.search()['ignoredElements']
        uniqueAjaxRequest = $timeout(function() {
            var filterSets = _.map($scope.filterSet, function(filterSet) {
                return filterSet.id;
            });

            // get chart data via Ajax, but only once per 500 milliseconds
            // (this avoid sending several request on page loading)
            $http.get('/api/chart', { params: {
                country: $scope.country.id,
                part: $scope.part.id,
                filterSet: filterSets.join(','),
                ignoredElements: ignoredElements
            }
            }).success(function(data) {
//                    console.log('chart loaded');
                    data.plotOptions.scatter.dataLabels.formatter = function() {
                        var questionnaire = {hFilters:{}};
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
                    $scope.isLoading = false;
                });

        }, 500);
    };

    $scope.setPointSelected = function(id, questionnaireId, name, filterId) {
        $scope.pointSelected = {
            id: id,
            questionnaire: questionnaireId,
            name: name,
            filter: filterId
        };
    }

    /**
     *  Manage filters ignored actions
     */
    $scope.reportStatusGlobally = function(filter, ignored) {
        _.forEach($scope.indexedElements, function(questionnaire) {
            if (questionnaire.filters && questionnaire.filters[filter.filter.id]) {
                $scope.ignoreFilter(questionnaire.filters[filter.filter.id], !_.isUndefined(ignored) ? ignored : filter.filter.ignored, false);
                $scope.updateQuestionnaireIgnoredStatus(questionnaire);
            }
        });

        $scope.refreshChart(true);
    }

    $scope.toggleQuestionnaire = function(questionnaireId, ignore) {
        var questionnaire = $scope.cache(questionnaireId);
        questionnaire.ignored = _.isUndefined(questionnaire.ignored) ? true : !questionnaire.ignored;

        _.forEach(questionnaire.filters, function(filter) {
            $scope.ignoreFilter( filter, !_.isUndefined(ignore) ? ignore : questionnaire.ignored, false)
        });

        $scope.refreshChart(true);
    }

    $scope.ignoreFilter = function(filter, ignored, refresh) {
        if (filter) {
            filter.filter.ignored = ignored;
            if (refresh) {
                $scope.refreshChart(true);
            }
        }
    }

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
    }

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

        //console.log('index ', questionnaire, filter, ignored);

        if (!$scope.indexedElements) {
            $scope.indexedElements = {};
        }

        // initiates high filter index and retrieves name for display in panel
        questionnaire = $scope.indexQuestionnaireCache(questionnaire, ignored && !filter ? ignored : false);
        $scope.indexQuestionnaireFilterCache(questionnaire.id, filter, ignored);
        return questionnaire;
    }

    $scope.indexQuestionnaireCache = function(questionnaire, ignored) {
        if (questionnaire) {

            if(!_.isObject(questionnaire)) questionnaire = {id:questionnaire};
            if (!$scope.indexedElements[questionnaire.id]) {
                $scope.indexedElements[questionnaire.id] = {};
            }

            if (questionnaire.id) $scope.indexedElements[questionnaire.id].id = questionnaire.id;
            if (questionnaire.name) $scope.indexedElements[questionnaire.id].name = questionnaire.name;

            if (!$scope.indexedElements[questionnaire.id].hFilters) {
                $scope.indexedElements[questionnaire.id].hFilters = {};
            }

            // assigns root filters to which this filter belongs to.
            _.forEach(questionnaire.hFilters, function(hFilter, hFilterId) {
                $scope.indexedElements[questionnaire.id].hFilters[hFilterId] = hFilter;
            })

            if (ignored) {
                $scope.indexedElements[questionnaire.id].ignored = true;
            }
            return $scope.indexedElements[questionnaire.id];
        }
    }

    $scope.indexQuestionnaireFilterCache = function(questionnaireId, filter, ignored) {
        if (filter) {
            if (!$scope.indexedElements[questionnaireId].filters) {
                $scope.indexedElements[questionnaireId].filters = {};
            }

            if (!$scope.indexedElements[questionnaireId].filters[filter.filter.id]) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id] = {};
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter = {};
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].values = {};
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

            if (!$scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters = {};
            }

            // assigns root filters to which this filter belongs to.
            _.forEach(filter.filter.hFilters, function(hFilter, hFilterId) {
                if (_.isNull(hFilter)) {

                    if (!$scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId]){
                        $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId] = {};
                    }

                    if (filter.filter.level >= 0) $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId].level = filter.filter.level;
                    if (filter.filter.sorting) $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId].sorting = filter.filter.sorting;
                } else {
                    $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId] = hFilter;
                }
            })

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
        }
    }

});