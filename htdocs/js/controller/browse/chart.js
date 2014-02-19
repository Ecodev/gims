angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $modal) {
    'use strict';

    $scope.Math = window.Math;
    $scope.chartObj;
    $scope.pointSelected;
    $scope.ignoredElements;
    $scope.indexedElements = {};
    $scope.firstExecution = true;
    $scope.filterSetQueryParams = {fields: 'filters,filters.genericColor,filters.officialChildren,filters.officialChildren.officialChildren,filters.officialChildren.officialChildren.officialChildren,filters.officialChildren.officialChildren.officialChildren.officialChildren'}

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
            $scope.retrieveFiltersAndValues($scope.pointSelected.questionnaire);
        }
    });

    /**
     * Calls ChartFilterController to recover filters data and values
     * @param questionnaireId
     * @param callback
     */
    $scope.retrieveFiltersAndValues = function(questionnaireId, callback) {
        if (questionnaireId) {
            var questionnaire = $scope.cache(questionnaireId);

            // only launch ajax request if the filters in this questionnaire don't have values
            if (!questionnaire || !questionnaire.filters || !$scope.firstFilterHasValue(questionnaire)) {

                var usedFiltersIds = [];
                _.forEach($scope.usedFilters, function(filter, filterId) {
                    usedFiltersIds.push(filterId)
                })

                var parameters = {
                    questionnaire: questionnaireId,
                    filters: usedFiltersIds.join(','),
                    part: $scope.part.id,
                    fields: 'name,color'
                };

                $scope.isLoading = true;
                Restangular.all('chart/getPanelFilters').getList(parameters).then(function(data) {
                    _.forEach($scope.usedFilters, function(hFilter, hFilterId) {
                        _.map(data[hFilterId], function(filter, index) {
                            filter.filter.sorting = index + 1;
                            filter.filter.hFilters = {};
                            filter.filter.hFilters[hFilterId] = true;
                            $scope.cache(questionnaireId, filter);
                        });
                    });

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
     *   @param questionnaireId Reference questionnaire id
     *   @param callback function to execute when data is indexed (usefull for then change status to ignored if they're ignored in url)
     */
    $scope.initiateEmptyQuestionnairesWithLoadedData = function(questionnaireId, callback) {
        if (questionnaireId) {
            var questionnaire = $scope.cache(questionnaireId);

            if ($scope.firstExecution) {
                $scope.firstExecution = false;
                $timeout(function() {
                    _.forEach($scope.indexedElements, function(tmpQuestionnaire, tmpQuestionnaireId) {
                        if (tmpQuestionnaireId != questionnaireId) {
                            _.forEach(questionnaire.filters, function(filter) {
                                if (filter) {
                                    $scope.cache(tmpQuestionnaireId, {filter: filter.filter});
                                }
                            });
                        }
                    });

                    if (callback) {
                        callback();
                    }
                    $scope.getIgnoredElements(true);
                }, 1000);
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
        if (!$scope.indexedElements) {
            return [];
        }

        var ignoredElements = [];
        _.forEach($scope.indexedElements, function(questionnaire, questionnaireId) {
            var ignoredElementsForQuestionnaire = [];
            var allFiltersIgnored = true;
            questionnaire.hasIgnoredFilters = false;
            _.forEach(questionnaire.filters, function(filter) {
                if (filter) {
                    if (filter.filter.ignored) {
                        questionnaire.hasIgnoredFilters = true;
                        ignoredElementsForQuestionnaire.push(filter.filter.id);
                    } else {
                        allFiltersIgnored = false;
                    }
                }
            });

            if (ignoredElementsForQuestionnaire.length > 0) {
                if (allFiltersIgnored) {
                    questionnaire.ignored = true;
                    ignoredElements.push(questionnaireId);
                } else {
                    questionnaire.ignored = false;
                    ignoredElements.push(questionnaireId + ':' + ignoredElementsForQuestionnaire.join('-'));
                }
            }
        });

        if (ignoredElements.length > 0) {
            $scope.ignoredElements = true;
            if (refreshUrl) {
                $location.search('ignoredElements', ignoredElements.join(','));
            }
        } else {
            $scope.ignoredElements = false;
            if (refreshUrl) {
                $location.search('ignoredElements', null);
            }
        }

        return ignoredElements;
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
        $scope.isLoading = true;
        $timeout.cancel(uniqueAjaxRequest);
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
                ignoredElements: refreshUrl ? $scope.getIgnoredElements(refreshUrl).join(',') : $location.search()['ignoredElements']
            }
            }).success(function(data) {
                data.plotOptions.scatter.dataLabels.formatter = function() {
                    $scope.cache(this.point.options.questionnaire).name = this.point.name;
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

    $scope.reportStatusGloballyForQuestionnaire = function(questionnaire) {
        _.forEach(questionnaire.filters, function(filter) {
            if (filter) {
                $scope.reportStatusGlobally(questionnaire, filter, false);
            }
        });

        $scope.updateQuestionnaireIgnoredStatus(questionnaire);
        $scope.refreshChart(true);
    }

    $scope.reportStatusGlobally = function(filter, refresh) {
        _.forEach($scope.indexedElements, function(questionnaire) {
            $scope.ignoreFilter(questionnaire, questionnaire.filters[filter.filter.id], filter.filter.ignored, true, false);
            $scope.updateQuestionnaireIgnoredStatus(questionnaire);
        });

        if (refresh) {
            $scope.refreshChart(true);
        }
    }

    $scope.toggleQuestionnaire = function(questionnaireId, ignore) {
        var questionnaire = $scope.cache(questionnaireId);
        questionnaire.ignored = questionnaire.ignored === undefined ? true : !questionnaire.ignored;

        _.forEach(questionnaire.filters, function(filter) {
            $scope.ignoreFilter(questionnaire, filter, ignore !== undefined ? ignore : questionnaire.ignored, false, false)
        });

        $scope.updateQuestionnaireIgnoredStatus(questionnaire);
        $scope.refreshChart(true);
    }

    $scope.ignoreFilter = function(questionnaire, filter, ignored, globally, refresh) {
        if (filter) {
            filter.filter.ignored = ignored;
            if (globally) {
                filter.filter.ignoredGlobally = ignored;
            }

            if (refresh) {
                $scope.updateQuestionnaireIgnoredStatus(questionnaire);
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
    $scope.cache = function(questionnaireId, filter, ignored) {
        if (!$scope.country || !$scope.part) {
            return [];
        }

        if (!$scope.indexedElements) {
            $scope.indexedElements = {};
        }

        // initiates high filter index and retrieves name for display in panel
        $scope.indexQuestionnaireCache(questionnaireId, ignored && !filter ? ignored : false);
        $scope.indexQuestionnaireFilterCache(questionnaireId, filter, ignored);
        return $scope.indexedElements[questionnaireId];
    }

    $scope.indexQuestionnaireCache = function(questionnaireId, ignored) {
        if (questionnaireId) {
            if (!$scope.indexedElements[questionnaireId]) {
                $scope.indexedElements[questionnaireId] = {};
            }

            if (ignored) {
                $scope.indexedElements[questionnaireId].ignored = true;
            }
        }
    }

    $scope.indexQuestionnaireFilterCache = function(questionnaireId, filter, ignored) {
        if (filter) {
            if (!$scope.indexedElements[questionnaireId].filters) {
                $scope.indexedElements[questionnaireId].filters = [];
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
            if (filter.filter.level) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.level = filter.filter.level;
            }
            if (filter.filter.color) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.color = filter.filter.color;
            }

            if (!$scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters = {};
            }

            // assigns root filters to which this filter belongs to.
            _.forEach(filter.filter.hFilters, function(hFilter, hFilterId) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId] = filter.filter.level;
            })

            if (filter.filter.sorting) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.sorting = filter.filter.sorting;
            }

            // if no ignored status specified, find if a the similar filter is globally ignored on the sibling questionnaires list.
            if (ignored === undefined && $scope.indexedElements[questionnaireId].ignored === undefined) {
                _.forEach($scope.indexedElements, function(questionnaire, qid) {
                    if (questionnaire && qid != questionnaireId && questionnaire.filters && questionnaire.filters[filter.filter.id] && questionnaire.filters[filter.filter.id].ignoredGlobally) {
                        $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignored = true;
                        $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignoredGlobally = true;
                        return false; // work done, stop loop;
                    }
                });

                // if no ignored params and no ignored status specified on filter but questionnaire has one, filter inherits questionnaire status
            } else if (ignored === undefined && $scope.indexedElements[questionnaireId].ignored !== undefined && $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignored === undefined) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignored = $scope.indexedElements[questionnaireId].ignored;

                // if ignored param specified, filter gets its value
            } else if (ignored !== undefined) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].filter.ignored = ignored;
            }

            if (filter.values) {
                $scope.indexedElements[questionnaireId].filters[filter.filter.id].values[$scope.part.name] = filter.values[0][$scope.part.name];
            }
        }
    }

});