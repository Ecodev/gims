angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $modal) { 'use strict';

    $scope.chartObj;
    $scope.pointSelected;
    $scope.ignoredQuestionnaires;
    $scope.ignoredFilters;
    $scope.cachedElements = {};

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('country.id + part.id + filterSet.id', function()
    {
        if ($scope.country && $scope.part && $scope.filterSet) {
            $scope.initIgnoredElementsFromUrl();
            $scope.refreshChart();
        }
    });


    // Whenever the list of excluded values is changed
    $scope.$watch('pointSelected', function(newPointSelected) {

        // We throw an window.resize event to force Highcharts to reflow and adapt to its new size
        $timeout(function() {
            jQuery(window).resize();
        }, 0);

        if (newPointSelected) {

            // select point and then recover the cached object reference
            var filterSet = $scope.cache($scope.pointSelected.filter, $scope.pointSelected.questionnaire);
            var questionnaire = filterSet[$scope.pointSelected.filter][$scope.pointSelected.questionnaire];

            // only launch ajax request if the filters in this questionnaire don't have values
            if (!questionnaire || !questionnaire.filters || questionnaire.filters.length == 0) {
                var parameters = {
                    questionnaire: $scope.pointSelected.questionnaire,
                    filter: $scope.pointSelected.filter,
                    filterSet: $scope.filterSet.id,
                    part: $scope.part.id
                };

                $scope.loadFilters(parameters);
            }
        }
    });


    $scope.loadFilters = function(parameters)
    {
        $scope.isLoading = true;
        Restangular.all('chartFilter').getList(parameters).then(function(data)
        {
            angular.forEach(data, function(filter, pos) {
                filter.sorting = pos;
                $scope.cache(parameters.filter, parameters.questionnaire, filter, null);
            });
            $scope.isLoading = false;
        });
    }


    $scope.getExcludedFilters = function(filtersIdsOnly)
    {
        if( !$scope.cachedElements || !$scope.cachedElements[$scope.country.id] || !$scope.cachedElements[$scope.country.id][$scope.part.id] || !$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id]) return [];

        // foreach highFilter in the current combo FilterSet/Country/Part, get ignored filters
        var excludedFilters = [];
        var excludedFiltersNumbersOnly = [];
        angular.forEach($scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id], function(hFilter,hFilterId){
            angular.forEach(hFilter.filters, function(filter){
                if (filter.ignored && _.indexOf(excludedFilters, hFilterId+":"+filter.filter.id) == -1) {
                    excludedFilters.push(hFilterId+":"+filter.filter.id);
                    excludedFiltersNumbersOnly.push(filter.filter.id);
                }
            });
        });

        if (excludedFilters.length > 0) {
            $scope.ignoredFilters = true;
            $location.search('excludedFilters', excludedFilters.join(','));
        } else {
            $scope.ignoredFilters = false;
            $location.search('excludedFilters', null);
        }
        if (filtersIdsOnly) {
            return excludedFiltersNumbersOnly;
        } else {
            return excludedFilters;
        }
    };

    $scope.getExcludedQuestionnaires = function()
    {
        if( !$scope.cachedElements || !$scope.cachedElements[$scope.country.id] || !$scope.cachedElements[$scope.country.id][$scope.part.id] || !$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id]) return [];

        // foreach highFilter in the current combo FilterSet/Country/Part, get ignored filters
        var excludedQuestionnaires = [];
        angular.forEach($scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id], function(hFilter){
            angular.forEach(hFilter.questionnaires, function(questionnaire){
                if (questionnaire.ignored && _.indexOf(excludedQuestionnaires, questionnaire.id) == -1) {
                    excludedQuestionnaires.push(questionnaire.id);
                }
            });
        });

        if (excludedQuestionnaires.length > 0) {
            $scope.ignoredQuestionnaires = true;
            $location.search('excludedQuestionnaires', excludedQuestionnaires.join(','));
        } else {
            $scope.ignoredQuestionnaires = false;
            $location.search('excludedQuestionnaires', null);
        }
        return excludedQuestionnaires;
    };

    $scope.initIgnoredElementsFromUrl = function()
    {
        // url excluded questionnaires
        var excludedQuestionnaires = $location.search()['excludedQuestionnaires'] ? $location.search()['excludedQuestionnaires'].split(',') : [];
        if (excludedQuestionnaires && excludedQuestionnaires.length) {
            angular.forEach(excludedQuestionnaires, function(excludedQuestionnaire){
                var ignoredElements = excludedQuestionnaire.split(':');
                $scope.cache(ignoredElements[0], ignoredElements[1], null, null, true);
            });
        }

        // url excluded filters
        var excludedFilters = $location.search()['excludedFilters'] ? $location.search()['excludedFilters'].split(',') : [];
        if (excludedFilters && excludedFilters.length) {
            angular.forEach(excludedFilters, function(excludedFilter){
                var ignoredElements = excludedFilter.split(':');
                $scope.cache(ignoredElements[0], null, {filter : {id:ignoredElements[1]}}, null, true);
            });

            // once ignored filters have been cached, go take all other filter for each high filter to retrieve name and position for display
            $scope.isLoading = true;
            Restangular.one('filterset', $scope.filterSet.id).get({fields:'filters.officialChildren,filters.officialChildren.officialChildren,filters.officialChildren.officialChildren.officialChildren'}).then(function(data)
            {
                angular.forEach(data.filters, function(hFilter) {
                    angular.forEach(hFilter.officialChildren, function(filter, pos) {
                        console.log('a', pos);
                        pos = $scope.cacheFilters(hFilter.id, filter, pos, 0);
                        console.log('b', pos);
                    });
                });

                $scope.isLoading = false;
            });

        };
    }

    $scope.cacheFilters = function(hFilter, filter, pos, deep)
    {
        console.log('c', pos);
        filter.level= deep;
        $scope.cache(hFilter, null, {filter:filter,sorting:pos});
        deep++;
        angular.forEach(filter.officialChildren, function(filter) {
            pos++;
            pos = $scope.cacheFilters(hFilter, filter, pos, deep);
        });
        console.log('r', pos);
        delete(filter.officialChildren);
        return pos;
    }


    $scope.openNewFilterSet = function() {
        $modal.open({
            templateUrl: 'newFilterSet.html',
            controller: 'Browse/ChartNewFilterSetCtrl',
            scope: $scope
        });
    };

    $scope.refreshChart = function() {
        console.log('refresh');
        $scope.isLoading = true;
        $timeout.cancel(uniqueAjaxRequest);
        uniqueAjaxRequest = $timeout(function() {
            // get chart data via Ajax, but only once per 500 milliseconds
            // (this avoid sending several request on page loading)
            $http.get('/api/chart', { params: {
                country: $scope.country.id,
                part: $scope.part.id,
                filterSet: $scope.filterSet.id,
                excludedQuestionnaires: $scope.getExcludedQuestionnaires().join(','),
                excludedFilters: $scope.getExcludedFilters().join(',') }

            }).success(function(data)
            {
                data.plotOptions.scatter.dataLabels.formatter = function()
                {
//                    console.log(this);
//                    console.log($('<span/>').css({color: !this.point.ignored ? '#DDD' : this.series.color}).text(this.point.name)[0].outerHTML);
                    return $('<span/>').css({color: !this.point.ignored ? '#DDD' : this.series.color}).text(this.point.name)[0].outerHTML;
                };

                data.plotOptions.scatter.point = {
                    events:{
                        click: function(e)
                        {
                            var ids = e.currentTarget.id.split(':');
                            $scope.pointSelected =
                            {
                                id: e.currentTarget.id,
                                questionnaire: e.currentTarget.questionnaire,
                                name: e.currentTarget.name,
                                filter: ids[0]
                            };
                            $scope.$apply(); // this is needed because we are outside the AngularJS context (highcharts uses jQuery event handlers)
                        }
                    }
                };

                $scope.chart = data;
                $scope.isLoading = false;
            });

        }, 500);
    };


    $scope.hasIgnoredQuestionnaire = function(hFilterId){
        $scope.hasIgnoredQuestionnaires = false;
        angular.forEach($scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][hFilterId].questionnaires, function(questionnaire){
            if(questionnaire.ignored) {
                $scope.hasIgnoredQuestionnaires = true;
                return true;
            }
        });
        return $scope.hasIgnoredQuestionnaires;
    }

    $scope.ignoreAllFiltersForAllHighFilterQuestionnaires = function(hFilterId, bool)
    {
        if (bool===null || bool===undefined) {
            bool = $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][hFilterId].filters[0].ignored;
        }

        if (!hFilterId) hFilterId = $scope.pointSelected.filter;
        angular.forEach($scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][hFilterId].filters, function(filter) {
            filter.ignored = bool;
        });
        $scope.refreshChart();
    }

    // not used at the moment
    $scope.toggleAllForOneQuestionnaire = function(hFilterId, questionnaireId) {
        $scope.allIgnored = !$scope.allIgnored;
        angular.forEach($scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][hFilterId].questionnaires[questionnaireId], function(questionnaire) {
            angular.forEach(questionnaire.filters, function(filter) {
                 filter.ignored = $scope.allIgnored; // care, same $scope.allIgnored as toggleAllForAllQuestionnaires() function, should be changed if both functions are used in the app
            });
        });
        $scope.refreshChart();
    }

    $scope.toggleQuestionnaire = function(highFilterId, questionnaireId)
    {
        var questionnaire = $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId];
        questionnaire.ignored = !questionnaire.ignored;
        $scope.refreshChart();
    }

    $scope.toggleFilterForAllQuestionnaires = function(filter)
    {
        filter.ignored = !filter.ignored;
        $scope.refreshChart();
    }


    $scope.toggleFilterForOneQuestionnaire = function(filter)
    {
        filter.ignored = !filter.ignored;
        $scope.refreshChart();
    }








    /**
     * Put in $scope.cacheElements the state of all objects that have been selected / viewed / ignored
     *
     * This object is structured as followed :
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['name']
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['filters'] = []
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['filters']['id']
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['filters']['name']
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['questionnaires']
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['questionnaires'].filters[filter_id].filter = {id : ..., name : ...}
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['questionnaires'].filters[filter_id].sorting
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['questionnaires'].filters[filter_id]['values'] = []
     * $scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['questionnaires'].filters[filter_id]['values']['Urban/Rural/Total'] = {}
     * [$scope.cachedElements[selected_country_id][selected_part_id][selected_filterSet_id][high_filter_id]['questionnaires'].filters[filter_id].ignored Optional ignored attribute
     * [$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][high_filter_id].ignored = true] Optional ignored attribute
     *
     * Some attributes like highFilter.name and questionnaire.name/code are loaded by ajax
     * These attributes correspond to objects mentionned in the url (highFilter and questionnaire).
     * They name attribute are loaded by ajax cause they're not specified in the url and are needed for display
     * The app can't wait the user to click on a point to retrieve this data from selectedPoint.name attribute
     *
     * @param highFilterId
     * @param questionnaireId
     * @param filter
     * @param questionnaireName
     * @param ignored
     * @returns current cached filterSet object (containing the list of the highfilters on chart);
     */
    $scope.cache = function(highFilterId, questionnaireId, filter, questionnaireName, ignored)
    {
        if (!$scope.country || !$scope.part || !$scope.filterSet) return [];

        $scope.initiateCache();

        // initiates high filter index and retrieves name for display in panel
        if (highFilterId) {
            $scope.initiateHighFilterCache(highFilterId);
            $scope.initiateQuestionnaireCache(highFilterId, questionnaireId, questionnaireName, ignored);
            $scope.initiateQuestionnaireFilterCache(highFilterId, questionnaireId, filter, ignored);

        }
        console.log($scope.cachedElements);
        return $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id];
    }

    $scope.initiateCache = function()
    {
        if (!$scope.cachedElements[$scope.country.id]) {
            $scope.cachedElements[$scope.country.id] = {};
        }

        if (!$scope.cachedElements[$scope.country.id][$scope.part.id]) {
            $scope.cachedElements[$scope.country.id][$scope.part.id] = {};
        }

        if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id]) {
            $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id] = {};
        }
    }

    $scope.initiateHighFilterCache = function(highFilterId)
    {
        if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId]) {
            $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId] = {};

            Restangular.one('filter', highFilterId).get().then(function(filter) {
                $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].name = filter.name;
            });
        }
    }

    $scope.initiateQuestionnaireCache = function(highFilterId, questionnaireId, questionnaireName, ignored)
    {
        if (questionnaireId) {
            if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires) {
                $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires = {};
            }
            if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId]) {
                $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId] = {id:highFilterId+':'+questionnaireId};
                if(ignored) $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].ignored = ignored;
                if (questionnaireName) {
                    $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].name = questionnaireName;
                } else {
                    Restangular.one('questionnaire', questionnaireId).get({fields:'survey'}).then(function(questionnaire) {
                        $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].name = questionnaire.survey.code;
                    });
                }
            }
        }
    }

    $scope.initiateQuestionnaireFilterCache = function(highFilterId, questionnaireId, filter, ignored)
    {
        if (filter) {
            if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].filters) {
                $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].filters = [];
            }
            if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].filters[filter.filter.id]){
                $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].filters[filter.filter.id] = {};
            }
            $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].filters[filter.filter.id].filter = filter.filter;
            if (filter.sorting > -1) $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].filters[filter.filter.id].sorting = filter.sorting;
            if (ignored) $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].filters[filter.filter.id].ignored = ignored;

            // stock filter values at questionnaire level
            if(filter.values) {
                if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].filters) {
                    $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].filters = {};
                }
                if (!$scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].filters[filter.filter.id]) {
                    $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].filters[filter.filter.id] = {};
                }
                if (filter.values) { $scope.cachedElements[$scope.country.id][$scope.part.id][$scope.filterSet.id][highFilterId].questionnaires[questionnaireId].filters[filter.filter.id].value = filter.values[0][$scope.part.name];}
            }
        }
    }
});


angular.module('myApp').controller('Browse/ChartNewFilterSetCtrl', function($scope, $location, $http, $timeout, $modalInstance) {
    'use strict';
    $scope.newFilterSet = {};
    $scope.cancelNewFilterSet = function() {
        $modalInstance.dismiss();
    };

    $scope.createNewFilterSet = function() {
        if ($scope.newFilterSet.name) {
            $http.post('/api/filterSet', {
                name: $scope.newFilterSet.name,
                originalFilterSet: $scope.filterSet.id,
                excludedFilters: $scope.getExcludedFilters(true)
            }).success(function(data) {
                $location.search('filterSet', data.id);
                // reload page
                $timeout(function() {
                    window.location.reload();
                }, 0);
            });
        }
    };
});