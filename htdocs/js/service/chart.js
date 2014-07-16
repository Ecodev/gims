angular.module('myApp.services').factory('Chart', function($location, $q, $http, HighChartFormatter, ChartCache, $rootScope, Utility) {
    'use strict';

    var chart = null;
    var cache = null;

    var ignoredElements = {};
    var globalIndexedFilters = {}; // is used to know if a same filter is ignored or not in all questionnaires (used on globally ignored button)
    var concatenatedIgnoredElements = [];

    var resetSeries = function() {
        if (chart) {
            chart.series = [];
            $rootScope.$emit('gims-chart-modified', chart);
        }
    };

    /**
     * Empty objects and array without creating a new object
     */
    var resetIgnoredFiltersArray = function() {
        Utility.resetObject(ignoredElements);
        Utility.resetObject(globalIndexedFilters);
        concatenatedIgnoredElements.length = 0;
    };

    /**
     * Add series and update inserted ones
     * @param seriesToAdd
     */
    var addSeries = function(seriesToAdd) {

        if (seriesToAdd.length > 0) {
            _.forEach(seriesToAdd, function(serieToAdd, index) {
                // if exist, remove and add new one
                if (_.contains(chart.series, {id: serieToAdd.id, isIgnored: serieToAdd.isIgnored})) {
                    chart.series.splice(index, 1);
                }
                chart.series.push(serieToAdd);
            });

            $rootScope.$emit('gims-chart-modified', chart);
        }
    };

    /**
     * Remove passed series
     * @param seriesToRemove
     * @param onlyRemoveAdjusted
     */
    var removeSeries = function(seriesToRemove) {

        if (seriesToRemove.length > 0) {

            _.forEach(seriesToRemove, function(serieToRemoveId) {
                _.forEachRight(chart.series, function(existingSerie, index) {
                    if (existingSerie.id == serieToRemoveId) {
                        chart.series.splice(index, 1);
                    }
                });
            });

            $rootScope.$emit('gims-chart-modified', chart);
        }
    };

    var initCacheWithQuestionnairesName = function(data, part) {

        if (data && _.isArray(data.series)) {
            _.forEach(data.series, function(serie) {
                if (serie.type == 'scatter') {
                    _.forEach(serie.data, function(data) {
                        var questionnaire = {hFilters: {}};
                        var ids = data.id.split(':');
                        questionnaire.id = ids[1];
                        questionnaire.name = data.name;
                        questionnaire.hFilters[ids[0]] = null;
                        ChartCache.cache(part, questionnaire);
                    });
                }
            });
        }
    };

    var computeEstimates = function(data, ignoredElements) {

        var arrayData = [];
        var columns = [];
        _.forEach(data.series, function(serie) {
            if (serie.type == 'line' && ((_.isUndefined(ignoredElements) || ignoredElements && ignoredElements.length === 0) && _.isUndefined(serie.isIgnored) || ignoredElements && ignoredElements.length > 0 && serie.isIgnored === true)) {

                // create a column by filter on graph
                columns.push({
                    field: 'value' + serie.id,
                    displayName: serie.name,
                    enableColumnResize: true,
                    color: serie.color,
                    headerCellTemplate: " " +
                            '<div class="ngHeaderSortColumn {{col.headerClass}}" ng-style="{\'cursor\': col.cursor}" ng-class="{ \'ngSorted\': !noSortVisible }">' +
                            '   <div ng-class="\'colt\' + col.index" class="ngHeaderText" popover-placement="top" popover="{{col.displayName}}">' +
                            '       <i class="fa fa-gims-filter" style="color:{{col.colDef.color}};"></i> {{col.displayName}}' +
                            '   </div>' +
                            '</div>',
                    cellTemplate: '<div class="ngCellText text-right" ng-class="col.colIndex()"><span ng-cell-text ng-show="{{row.entity.value' + serie.id + '!==null}}">{{row.entity.value' + serie.id + '}} %</span></div>'
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
            if ((row.year % 5 === 0 && index < arrayData.length || index == arrayData.length - 1) && row.year > 1985) {
                finalData.push(arrayData.splice(index, 1)[0]);
            }
        });

        return {
            data: finalData,
            columns: columns
        };
    };

    var retrieveFiltersAndValuesCanceler = null;
    var retrieveFiltersAndValues = function(questionnaire, filters, part) {

        var deferred = $q.defer();

        getIgnoredElements();

        if (questionnaire && (!questionnaire.filters || !_.isEmpty(concatenatedIgnoredElements) || !firstFilterHasValue(part, questionnaire))) {

            if (retrieveFiltersAndValuesCanceler) {
                retrieveFiltersAndValuesCanceler.resolve();
            }
            retrieveFiltersAndValuesCanceler = $q.defer();

            var ignoredElements = concatenatedIgnoredElements ? concatenatedIgnoredElements.join(',') : '';

            $http.get('/api/chart/getPanelFilters', {
                timeout: retrieveFiltersAndValuesCanceler.promise,
                params: {
                    questionnaire: questionnaire.id,
                    filters: _.pluck(filters, 'id').join(','),
                    part: part.id,
                    fields: 'color',
                    getQuestionnaireUsages: questionnaire.usages && questionnaire.usages.length ? false : true,
                    ignoredElements: ignoredElements
                }
            }).success(function(data) {
                questionnaire.name = data.name; // Overwrite short name with full name

                _.forEach(data.filters, function(hFilter, hFilterId) {
                    _.forEach(data.filters[hFilterId], function(filter, index) {
                        if (!_.isUndefined(cache[questionnaire.id].hFilters[hFilterId])) {
                            filter.filter.sorting = index + 1;
                            filter.filter.hFilters = {};
                            filter.filter.hFilters[hFilterId] = null;
                            ChartCache.cache(part, {id: questionnaire.id, usages: data.usages}, filter);
                        }
                    });
                });

                ChartCache.propagateRetrievedQuestionnaires(part, questionnaire);
                deferred.resolve(data);
            });
        }

        return deferred.promise;
    };

    var getIgnoredElements = function() {

        resetIgnoredFiltersArray();

        // browse each questionnaire
        _.forEach(cache, function(questionnaire, questionnaireId) {
            var ignoredElementsForQuestionnaire = [];
            if (questionnaire.filters) {
                questionnaire.ignored = true;
            }
            questionnaire.hasIgnoredFilters = false;

            // browse each filter of questionnaire
            _.forEach(questionnaire.filters, function(filter) {
                if (filter) {

                    // report globally ignored filter status on globalIndexedFilters
                    // false = filter never ignored
                    // true = filter ignored on all questionnaires
                    // null = filter sometimes ignored
                    if (_.isUndefined(filter.filter.ignored)) {
                        filter.filter.ignored = false;
                    }
                    if (_.isUndefined(globalIndexedFilters[filter.filter.id])) {
                        globalIndexedFilters[filter.filter.id] = filter.filter.ignored;
                    } else if (filter.filter.ignored != globalIndexedFilters[filter.filter.id] && !_.isNull(globalIndexedFilters[filter.filter.id])) {
                        globalIndexedFilters[filter.filter.id] = null;
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
                    concatenatedIgnoredElements.push(questionnaireId);
                    ignoredElements[questionnaireId] = [];
                } else {
                    concatenatedIgnoredElements.push(questionnaireId + ':' + ignoredElementsForQuestionnaire.join('-'));
                    ignoredElements[questionnaireId] = ignoredElementsForQuestionnaire;
                }
            }
        });

        return {
            concatenatedIgnoredElements: concatenatedIgnoredElements,
            globalIndexedFilters: globalIndexedFilters,
            ignoredElements: ignoredElements
        };
    };

    /**
     * Retrieve ignored elements in the url and init cache
     * This function is called after chart has been loaded.
     *
     * 1) If there are some elements ignored :
     * 2) Retrieve chart and panel filters
     * 3) When request has come back, initiate all questionnaires to allow data display on ignored elements (mainly filter's name).
     */
    var initIgnoredElementsFromUrl = function(filters, part) {

        var deferred = $q.defer();

        // url excluded questionnaires
        var ignoredQuestionnaires = $location.search().ignoredElements ? $location.search().ignoredElements.split(',') : [];
        if (ignoredQuestionnaires.length > 0) {

            var firstQuestionnaire = ignoredQuestionnaires[0].split(':');
            var questionnaire = ChartCache.cache(part, firstQuestionnaire[0]);
            retrieveFiltersAndValues(questionnaire, filters, part).then(function() {
                _.forEach(ignoredQuestionnaires, function(ignoredElement) {
                    var questionnaireDetail = ignoredElement.split(':');
                    var ignoredQuestionnaireId = questionnaireDetail[0];
                    var ignoredFilters = questionnaireDetail[1] ? questionnaireDetail[1].split('-') : null;

                    if (ignoredFilters && ignoredFilters.length > 0) {
                        _.forEach(ignoredFilters, function(filterId) {
                            ChartCache.cache(part, ignoredQuestionnaireId, {filter: {id: filterId}}, true);
                        });
                    } else {
                        _.forEach(cache[ignoredQuestionnaireId].filters, function(filter) {
                            if (filter) {
                                filter.filter.ignored = true;
                            }
                        });
                        ChartCache.cache(part, ignoredQuestionnaireId, null, true);
                    }
                });

                deferred.resolve();
            });
        }

        return deferred.promise;
    };

    var refresh = function(queryParams, part, refreshCanceler, resetSeries) {
        var deferred = $q.defer();

        $http.get('/api/chart', {
            timeout: refreshCanceler.promise,
            params: queryParams
        }).success(function(data) {

            // implement tooltip formatter
            data.tooltip = {
                formatter: function() {
                    return HighChartFormatter.tooltipFormatter.call(this);
                }
            };

            data.plotOptions.scatter.dataLabels.formatter = function() {
                return HighChartFormatter.scatterFormatter.call(this);
            };

            data.plotOptions.scatter.point = {
                events: {
                    click: function(e) {
                        var ids = e.currentTarget.id.split(':');
                        var pointSelected = {
                            id: e.currentTarget.id,
                            questionnaire: e.currentTarget.questionnaire,
                            name: e.currentTarget.name,
                            filter: ids[0]
                        };

                        $rootScope.$emit('gims-chart-pointSelected', pointSelected);
                    }
                }
            };

            initCacheWithQuestionnairesName(data, part);

            if (!chart || resetSeries) {
                chart = data;
                $rootScope.$emit('gims-chart-modified', chart);
            } else {
                addSeries(data.series);
                chart.title.text = data.title.text;
            }

            deferred.resolve(chart);
        });

        return deferred.promise;
    };

    /**
     * As filters are stored in an array on index relative to their Id, this function gets first filter to verify is he has a value.
     * Is used to determine if we need to send request to recover values.
     *
     * @param part
     * @param questionnaire
     * @returns {boolean}
     */
    var firstFilterHasValue = function(part, questionnaire) {
        var hasValue = false;
        _.forEach(questionnaire.filters, function(filter) {
            if (filter && filter.values && filter.values[part.name]) {
                hasValue = true;
                return false;
            }
        });
        return hasValue;
    };

    return {
        setCache: function(newCache) {
            cache = newCache;
        },
        resetSeries: function() {
            resetSeries();
        },
        computeEstimates: function(data, ignoredElements) {
            return computeEstimates(data, ignoredElements);
        },
        /**
         * Retrieve ignored elements in the url and init cache
         * This function is called after chart has been loaded.
         *
         * 1) If there are some elements ignored :
         * 2) Retrieve chart and panel filters
         * 3) When request has come back, initiate all questionnaires to allow data display on ignored elements (mainly filter's name).
         */
        initIgnoredElementsFromUrl: function(filters, part) {
            return initIgnoredElementsFromUrl(filters, part);
        },
        /**
         * Remove passed series
         * @param seriesToRemove
         */
        removeSeries: function(seriesToRemove) {
            removeSeries(seriesToRemove);
        },
        addSeries: function(seriesToAdd) {
            seriesToAdd(seriesToAdd);
        },
        retrieveFiltersAndValues: function(questionnaire, filters, part) {
            return retrieveFiltersAndValues(questionnaire, filters, part);
        },
        getIgnoredElements: function() {

            if (!cache) {
                return [];
            }

            return getIgnoredElements();
        },
        refresh: function(queryParams, part, refreshCanceler, resetSeries) {
            return refresh(queryParams, part, refreshCanceler, resetSeries);
        }
    };
});
