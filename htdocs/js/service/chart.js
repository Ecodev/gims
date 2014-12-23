angular.module('myApp.services').factory('Chart', function($location, $q, $http, HighChartFormatter, ChartCache, $rootScope, Utility) {
    'use strict';

    var chart = null;
    var estimatesCharts = null;
    var cache = null;

    // !! Has to be the same as server side
    var startChartYear = 1980;
    var endChartYear = 2015;

    var ignoredElements = {};
    var globalIndexedFilters = {}; // is used to know if a same filter is ignored or not in all questionnaires (used on globally ignored button)
    var concatenatedIgnoredElements = [];

    /**
     * Dash styles that are acceptable to be displayed all at
     * once on the same graph and still be somewhat readable.
     * @type Array
     */
    var dashStyles = [
        'Solid',
        'Dash',
        'LongDashDot',
        'ShortDashDot',
        'LongDashDotDot',
        'ShortDashDotDot'
    ];

    var adjustedDashStyle = 'ShortDot';

    var getChart = function() {
        return {
            chart: {
                zoomType: 'xy',
                height: 600,
                animation: false
            },
            subtitle: {
                text: 'Estimated proportion of the population'
            },
            xAxis: {
                title: {
                    enabled: true,
                    text: 'Year'
                },
                labels: {
                    step: 1,
                    format: '{value}'
                },
                allowDecimals: false
            },
            yAxis: {
                title: {
                    enabled: true,
                    text: 'Coverage (%)'
                },
                min: 0,
                max: 100
            },
            credits: {enabled: false},
            plotOptions: {
                line: {
                    marker: {
                        enabled: false
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size: 10px">Estimate for {point.category}</span><br/>',
                        pointFormat: '<span style="color:{series.color}">{point.y}% {series.name}</span><br/>',
                        footerFormat: '<br><br><strong>Rules : </strong><br><br>{series.options.usages}</span><br/>',
                        valueSuffix: '%'
                    },
                    pointStart: startChartYear,
                    dataLabels: {
                        enabled: false
                    }
                },
                scatter: {
                    dataLabels: {enabled: true},
                    tooltip: {
                        headerFormat: '',
                        pointFormat: '<b>{point.name}</b> ({point.x})<br/><span style="color:{series.color}">{point.y}% {series.name}</span>'
                    }, marker: {
                        states: {
                            select: {
                                lineColor: '#DDD',
                                fillColor: '#DDD'
                            }
                        }
                    }
                }
            }
        };
    };

    var getEstimatesChart = function(title) {
        return {
            title: {
                text: title
            },
            chart: {
                zoomType: 'xy',
                height: 500,
                width: 450,
                animation: false
            },
            xAxis: {
                title: {
                    enabled: true,
                    text: 'Year'
                },
                labels: {
                    step: 1,
                    format: '{value}'
                },
                allowDecimals: false
            },
            yAxis: {
                title: {
                    enabled: true,
                    text: 'Coverage (%)'
                },
                min: 0,
                max: 100
            },
            tooltip: {
                pointFormat: '<b>{point.y:.1f}%</b> : <span style="color:{series.color}">{series.name}</span><br/>',
                shared: true
            },
            credits: {enabled: false},
            plotOptions: {
                area: {
                    stacking: 'normal',
                    lineColor: '#ffffff',
                    lineWidth: 1,
                    marker: {
                        symbol: 'circle',
                        lineWidth: 1,
                        lineColor: '#ffffff'
                    }

                }
            }
        };
    };

    var updateChartTitle = function(geonames, part) {

        chart.title = {};
        if (!_.isEmpty(geonames)) {
            chart.title.text = _.pluck(geonames, 'name').join(', ');
        }

        if (part) {
            chart.title.text += " - " + part.name;
        }
    };

    var resetSeries = function() {
        if (chart) {
            chart.series = [];
            updateChart();
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

            updateChart();
        }
    };

    /**
     * Remove passed series
     * Type values :
     *  - undefined = all series
     *  - null = normal series (not ignored and not adjusted)
     *  - isIgnored = ignored series
     *  - isAdjusted = adjusted series
     * @param filtersToRemove
     */
    var removeSeries = function(filtersToRemove, type) {

        if (chart) {
            if (filtersToRemove === null) {
                filtersToRemove = _.map(chart.series, function(serie) {
                    return {id: serie.id};
                });
            }

            if (filtersToRemove.length > 0) {
                _.forEach(filtersToRemove, function(filtersToRemove) {
                    _.forEachRight(chart.series, function(serie, index) {
                        if (serie.id == filtersToRemove.id && (_.isUndefined(type) || (type === null && _.isUndefined(serie.isIgnored) && _.isUndefined(serie.isAdjusted)) || (type !== null && serie[type]))) {
                            chart.series.splice(index, 1);
                        }
                    });
                });
            }
        }
    };

    var initCacheWithQuestionnairesName = function(series, part) {

        if (_.isArray(series)) {
            _.forEach(series, function(serie) {
                if (serie.type == 'scatter') {
                    _.forEach(serie.data, function(data) {
                        var questionnaire = {hFilters: {}};
                        var ids = data.id.split(':');
                        questionnaire.id = ids[1];
                        questionnaire.name = data.name + ' - ' + serie.country;
                        questionnaire.hFilters[ids[0]] = null;
                        ChartCache.cache(part, questionnaire);
                    });
                }
            });
        }
    };

    var retrieveFiltersAndValuesCanceler = null;
    var retrieveFiltersAndValues = function(questionnaire, filters, part) {

        var deferred = $q.defer();

        getIgnoredElements();

        // we recompute panel filter if there are no filter yet or if there are any ignored filter
        /** @todo : to activate this cache, we have to remove manually (browser side) valuesWithoutIgnored attribute for all filter of a questionnaire that is not in concatenatedIgnoredElements list*/
        /** @todo : we may add a cache for each already sent request to avoid to send it again -> maybe indexed by (ignored filters)->toString() */
        // if (questionnaire && (!questionnaire.filters || !_.isEmpty(concatenatedIgnoredElements) || !firstFilterHasValue(part, questionnaire))) {

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
        // }

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
        var ignoredQuestionnaires = $location.search().ignoredElements ? $location.search().ignoredElements.split(',') :
                [];
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

    /**
     * Return series by asked type
     * @param series : array with series collection to search in
     * @param type 'isAdjusted', 'isIgnored', null for normal series or undefined for all series
     * @returns {Array}
     */
    var getChartSeries = function(series, type) {
        var filtredSeries = [];
        if (chart) {
            _.forEach(series, function(serie) {
                if (_.isUndefined(type) || (type === null && _.isUndefined(serie.isIgnored) && _.isUndefined(serie.isAdjusted)) || (type !== null && serie[type])) {
                    filtredSeries.push(serie);
                }
            });
        }

        return filtredSeries;
    };

    /**
     * This function only ensures there are original trendlines before asking for ignored ones
     * @param queryParams
     * @param part
     * @param geonames
     * @param timeout Promise that allows to cancel request on user action
     */
    var refresh = function(queryParams, part, geonames, timeout) {
        var deferred = $q.defer();

        if (!chart) {
            chart = getChart();
        }

        $http.get('/api/chart/getSeries', {
            timeout: timeout.promise,
            params: queryParams
        }).success(function(series) {

            // implement tooltip formatter
            chart.tooltip = {
                formatter: function() {
                    return HighChartFormatter.tooltipFormatter.call(this);
                }
            };

            chart.plotOptions.scatter.dataLabels.formatter = function() {
                return HighChartFormatter.scatterFormatter.call(this);
            };

            chart.plotOptions.scatter.point = {
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

            initCacheWithQuestionnairesName(series, part);
            updateChartTitle(geonames, part);
            _.forEach(series, function(serie) {
                serie.idWithIgnored = serie.id + ':' + (serie.isIgnored ? 'true' : '');
            });

            if (!chart.series) {
                chart.series = series;
                updateChart();
            } else {
                addSeries(series);
            }

            deferred.resolve(chart);
        });

        return deferred.promise;
    };

    /**
     * Set lines lighter when there are adjusted lines or ignored elements
     */
    var allSeriesWithDuplicates = null;
    var updateChart = function() {

        if (chart) {
            allSeriesWithDuplicates = chart.series;
            deleteDuplicatedSeries();

            var ignoredSeries = getChartSeries(chart.series, 'isIgnored');
            var adjustedSeries = getChartSeries(chart.series, 'isAdjusted');
            var alternativeSeries = ignoredSeries.concat(adjustedSeries);
            var normalSeries = getChartSeries(chart.series, null);

            var geonames = _.uniq(_.compact(_.pluck(chart.series, 'geonameId')));

            _.forEach(chart.series, function(serie) {
                serie.dashStyle = dashStyles[_.indexOf(geonames, serie.geonameId)];
            });

            // define static dashStyle for adjusted serie to differentiate them from series with ignored elements
            _.forEach(adjustedSeries, function(serie) {
                serie.dashStyle = adjustedDashStyle;
            });

            if (!_.isEmpty(alternativeSeries)) {
                _.forEach(normalSeries, function(serie) {
                    if (!serie.normalColor) {
                        serie.normalColor = serie.color;
                    }
                    serie.color = serie.lightColor;
                });
            } else {
                _.forEach(normalSeries, function(serie) {
                    if (serie.normalColor) {
                        serie.color = serie.normalColor;
                    }
                });
            }

            updateTrendTable();
            updateTrendBarChart();
            $rootScope.$emit('gims-chart-modified', chart);
        }
    };

    var getLineSeries = function(series) {
        var filtred = _.filter(series, function(serie) {
            if (serie.type == 'line') {
                return true;
            }
        });

        return filtred;
    };

    var updateTrendBarChart = function() {

        var ignoredSeries = getChartSeries(allSeriesWithDuplicates, 'isIgnored');
        var normalSeries = getChartSeries(allSeriesWithDuplicates, null);

        // init estimates charts
        estimatesCharts = {};
        _.forEach(_.uniq(_.compact(_.pluck(normalSeries.concat(ignoredSeries), 'geonameId'))), function(geonameId) {
            if (!estimatesCharts[geonameId]) {
                estimatesCharts[geonameId] = {};
                estimatesCharts[geonameId].normal = getEstimatesChart('JMP');
                estimatesCharts[geonameId].ignored = getEstimatesChart('Ignored elements');
            }
        });

        updatePercentChart(normalSeries, 'normal');
        updatePercentChart(ignoredSeries, 'ignored');

        // remove all ignored series only if they are all the same than normal series (avoid to display two identical charts for jmp and ignored data)
        _.forEach(estimatesCharts, function(chart, geonameId) {
            if (areAllSameSeries(chart.normal.series, chart.ignored.series)) {
                estimatesCharts[geonameId].ignored.series = null;
            }
        });

        $rootScope.$emit('gims-estimates-chart-modified', estimatesCharts);
    };

    var updatePercentChart = function(series, type) {

        // clean series to remove styling from main chart and group by geoname
        var barChartSeriesByGeoname = _.groupBy(_.map(getLineSeries(series), function(serie) {
            return {
                id: serie.id,
                name: serie.name,
                color: serie.color,
                geonameId: serie.geonameId,
                isAdjusted: serie.isAdjusted,
                isIgnored: serie.isIgnored,
                sorting: serie.sorting,
                data: serie.data
            };
        }), 'geonameId');

        // for each geoname, computes years and create series with two years
        _.forEach(barChartSeriesByGeoname, function(series, geonameId) {
            var years = getYears(series, 1990, true);
            years = [years[0], years[years.length - 1]];
            estimatesCharts[geonameId][type].xAxis.categories = years;

            _.forEach(series, function(serie) {
                serie.data = [serie.data[years[0] - startChartYear], serie.data[years[1] - startChartYear]];
            });

            estimatesCharts[geonameId][type].series = _.sortBy(series, 'sorting');
        });
    };

    var updateTrendTable = function() {

        var arrayData = [];
        var columns = [
            {
                field: 'year',
                displayName: 'Year',
                name: 'Year',
                enableColumnResize: true,
                width: 100
            }
        ];

        var series = getLineSeries(chart.series);
        var years = getYears(series, 1990);

        _.forEach(series, function(serie) {

            if (!serie.isAdjusted) {
                // create a column by filter on graph
                columns.push({
                    field: 'value' + serie.id + '_' + serie.geonameId,
                    displayName: serie.name,
                    name: serie.name,
                    enableColumnResize: true,
                    color: serie.color,
                    headerCellTemplate: '<div class="ui-grid-header-cell "></div><div class="ui-grid-cell-contents ngHeaderSortColumn {{col.headerClass}}" ng-style="{\'cursor\': col.cursor}" ng-class="{ \'ngSorted\': !noSortVisible }">' +
                            '   <div ng-class="\'colt\' + col.index" class="ngHeaderText" popover-placement="top" popover="{{col.displayName}}">' +
                            '       <i class="fa fa-gims-filter" style="color:{{col.colDef.color}};"></i> {{col.displayName}}' +
                            '   </div>' +
                            '</div></div>',
                    cellTemplate: '<div class="ui-grid-cell-contents text-right" ng-class="col.colIndex()">' +
                            '<span ng-cell-text ng-show="{{row.entity.value' + serie.id + '_' + serie.geonameId + '!==null}}">{{row.entity.value' + serie.id + '_' + serie.geonameId + '}} %</span>' +
                            '</div>'
                });

                // retrieve data
                _.forEach(years, function(year, index) {
                    if (_.isUndefined(arrayData[index])) {
                        arrayData[index] = {};
                    }
                    arrayData[index].year = year;
                    arrayData[index]['value' + serie.id + '_' + serie.geonameId] = serie.data[year - startChartYear];
                });
            }
        });

        $rootScope.$emit('gims-estimates-table-modified', {data: arrayData, columns: columns});
    };

    /**
     * Return array of years multiples of 5 according to data in series
     * @param series
     * @param minYear (int) start limit of years
     * @param allYearsWithValues (bool) If is true, the list consider only years that have value for all series
     * @returns {Array}
     */
    var getYears = function(series, minYear, allYearsWithValues) {

        minYear = _.isUndefined(minYear) ? 0 : minYear;

        var years = [];

        if (series.length) {
            var minYearOfFirstValue = endChartYear; // earliest year that has a value
            var maxYearOfFirstValue = 0; // latest year that has a value

            _.forEach(series, function(serie) {
                // return all data containing null until first value
                // the emptyYears.length is the number of years that contain Null
                var emptyYears = _.initial(serie.data, function(data) {
                    return !_.isNull(data);
                });

                if (emptyYears.length < minYearOfFirstValue) {
                    minYearOfFirstValue = emptyYears.length;
                }
                if (emptyYears.length > maxYearOfFirstValue) {
                    maxYearOfFirstValue = emptyYears.length;
                }
            });

            // transform index to years
            minYearOfFirstValue += startChartYear;
            maxYearOfFirstValue += startChartYear;

            var startLimit = null;
            if (allYearsWithValues) {
                startLimit = maxYearOfFirstValue;
            } else {
                startLimit = minYearOfFirstValue;
            }

            for (var i = startLimit; i <= endChartYear; i++) {
                if (i % 5 === 0 && i >= minYear) {
                    years.push(i);
                }
            }
        }

        return years;
    };

    /**
     * Remove duplicated series to ensure that series are unique
     * A serie is unique only if the filter and the raw data are identical,
     * everything else (label, extra data, etc.) is ignored.
     * This function gives priority to non ignored elements (replace ignored serie if the non ignored one is tested after)
     */
    var deleteDuplicatedSeries = function() {
        var uniqueSeries = [];
        _.forEach(chart.series, function(serie) {
            var foundSame = false;
            _.forEach(uniqueSeries, function(uniqueSerie, index) {
                if (isSameSerie(serie, uniqueSerie)) {
                    foundSame = {serie: serie, index: index};
                    return false;
                }
            });

            if (!foundSame) {
                uniqueSeries.push(serie);
            } else {
                if (!foundSame.serie.isIgnored && uniqueSeries[foundSame.index].isIgnored) {
                    uniqueSeries[foundSame.index] = foundSame.serie;
                }
            }
        });

        chart.series = uniqueSeries;
    };

    var isSameSerie = function(serie1, serie2) {
        return serie1.id == serie2.id && _.isEqual(serie1.data, serie2.data);
    };

    var areAllSameSeries = function(collection1, collection2) {

        if (!collection1 || !collection2) {
            return false;
        }

        // if not same collection, result can't be positive
        if (collection1.length != collection2.length) {
            return false;
        }

        var allSameSeries = true;
        _.forEach(collection1, function(s1) {
            var foundSame = false;
            _.forEach(collection2, function(s2) {
                if (isSameSerie(s1, s2)) {
                    foundSame = true;
                    return false;
                }
            });

            if (!foundSame) {
                allSameSeries = false;
                return false;
            }
        });

        return allSameSeries;
    };

    /**
     * @todo : actually ignored cause a cache has been disabled, will be usefull when it will be reactivated, so don't remove it.
     * As filters are stored in an array on index relative to their Id, this function gets first filter to verify ifs he has a value.
     * Is used to determine if we need to send request to recover values.
     *
     * @param part
     * @param questionnaire
     * @returns {boolean}
     */
    //    var firstFilterHasValue = function(part, questionnaire) {
    //        var hasValue = false;
    //        _.forEach(questionnaire.filters, function(filter) {
    //            if (filter && filter.values && filter.values[part.name]) {
    //                hasValue = true;
    //                return false;
    //            }
    //        });
    //        return hasValue;
    //    };

    // Return public API
    return {
        setCache: function(newCache) {
            cache = newCache;
        },
        getChartSeries: getChartSeries,
        resetSeries: resetSeries,
        updateChart: updateChart,
        initIgnoredElementsFromUrl: initIgnoredElementsFromUrl,
        removeSeries: removeSeries,
        retrieveFiltersAndValues: retrieveFiltersAndValues,
        getIgnoredElements: function() {

            if (!cache) {
                return [];
            }

            return getIgnoredElements();
        },
        refresh: refresh
    };
});
