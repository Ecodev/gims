angular.module('myApp').controller('Browse/FilterCtrl', function($scope, $location, $http, Restangular, $q, $rootScope, requestNotification, $filter, questionnairesStatus, TableFilter) {
    'use strict';

    /**************************************************************************/
    /*********************************************** Variables initialisation */
    /**************************************************************************/

    // params for ajax requests
    $scope.filterFields = {fields: 'color,bgColor,paths,parents,summands,thematicFilter'};

    // Variables initialisations
    $scope.locationPath = $location.$$path;
    $scope.data = TableFilter.init($scope.locationPath);
    $scope.expandSelection = true;
    $scope.questionnairesStatus = questionnairesStatus;
    $scope.usedThematics = [];
    $scope.cacheDefaultPopulationsByGeoname = {};

    // Expose functions to scope
    $scope.refresh = TableFilter.refresh;
    $scope.toggleShowQuestionnaireUsages = TableFilter.toggleShowQuestionnaireUsages;
    $scope.saveAll = TableFilter.saveAll;
    $scope.checkAndCompleteQuestionnaire = TableFilter.checkAndCompleteQuestionnaire;
    $scope.isValidId = TableFilter.isValidId;
    $scope.savePopulation = TableFilter.savePopulation;
    $scope.setInitialValue = TableFilter.setInitialValue;
    $scope.getFiltersByLevel = TableFilter.getFiltersByLevel;
    $scope.addEquipment = TableFilter.addEquipment;
    $scope.addQuestionnaire = TableFilter.addQuestionnaire;
    $scope.saveQuestion = TableFilter.saveQuestion;
    $scope.toggleShowLabels = TableFilter.toggleShowLabels;

    /**************************************************************************/
    /***************************************** First execution initialisation */
    /**************************************************************************/

    $scope.questionnaireParams = {surveyType: $scope.data.mode.surveyType};
    $scope.surveyParams = {surveyType: $scope.data.mode.surveyType};

    // Ensure that we have a logged in user if we are going to contribute things
    if ($scope.data.mode.isContribute) {
        $rootScope.$broadcast('event:auth-loginRequired');
    }

    $scope.initScrollAndHeight = function() {
        TableFilter.adjustHeight();
        TableFilter.syncScroll();

        $scope.$watch('expandSelection', function() {
            TableFilter.resizeContent();
        });
    };

    /**************************************************************************/
    /*************************************************************** Watchers */
    /**************************************************************************/

    // Subscribe to listen when there is network activity
    $scope.isLoading = false;
    requestNotification.subscribeOnRequest(function() {
        $scope.isLoading = true;
    }, function() {
        if (requestNotification.getRequestCount() === 0) {
            $scope.isLoading = false;
        }
    });

    $scope.$watch(function() {
        return $location.url();
    }, function() {
        $scope.returnUrl = $location.search().returnUrl;
        $scope.currentUrl = encodeURIComponent($location.url());
    });

    var filterSetDeferred = $q.defer();
    if (!$location.search().filterSet) {
        filterSetDeferred.resolve();
    }

    var filterDeferred = $q.defer();
    if (!$location.search().filter) {
        filterDeferred.resolve();
    }
    $scope.$watch('data.filterSet', function() {
        if ($scope.data.filterSet) {
            $scope.data.filters = [];
            Restangular.one('filterSet', $scope.data.filterSet.id).getList('filters', _.merge({flatten: true}, $scope.filterFields)).then(function(filters) {
                if (filters) {
                    $scope.data.filters = filters;
                    $scope.data.filter = null;
                    TableFilter.updateUrl('filter');
                    filterSetDeferred.resolve();
                }
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('data.filter', function() {
        if ($scope.data.filter) {

            if ($scope.isValidId($scope.data.filter)) {
                Restangular.one('filter', $scope.data.filter.id).getList('children', _.merge({flatten: true}, $scope.filterFields)).then(function(filters) {
                    if (filters) {

                        // Inject parent as first filter, so we are able to see the "main" value
                        _.forEach(filters, function(filter) {
                            filter.level++;
                        });
                        var parent = _.clone($scope.data.filter);
                        parent.level = 0;
                        filters.unshift(parent);

                        $scope.data.filters = filters;
                        $scope.data.filterSet = null;
                        TableFilter.updateUrl('filterSet');

                        filterDeferred.resolve();
                    }
                    checkSelectionExpand();
                });
            } else {
                $scope.data.filters = [$scope.data.filter];
            }
        }
    });

    $scope.$watch('data.geoname', function() {
        if ($scope.data.geoname) {
            TableFilter.loadGeoname().then(checkSelectionExpand);
            if ($scope.data.mode.isNsa && !$scope.cacheDefaultPopulationsByGeoname[$scope.data.geoname.id]) {
                TableFilter.getDefaultPopulations($scope.data.geoname).then(function(populations) {
                    $scope.cacheDefaultPopulationsByGeoname[$scope.data.geoname.id] = populations;
                });
            }
        }
    });

    $scope.$watch('data.survey', function() {
        if ($scope.data.survey) {
            TableFilter.loadSurvey().then(checkSelectionExpand);
        }
    });

    var firstLoading = true;
    $q.all([filterSetDeferred, filterDeferred]).then(function() {
        $scope.$watch('data.filters', function(newFilters, oldFilters) {
            $scope.usedThematics = _(newFilters).pluck('thematicFilter').filter().pluck('id').pluck().uniq().value();
            TableFilter.loadFilter(newFilters, oldFilters).then(function() {
                if (firstLoading === true && $scope.data.filters && $scope.data.questionnaires) {
                    checkSelectionExpand();
                }
            });
        });
    });

    $scope.$watchCollection('data.filters', function() {
        TableFilter.prepareNsaFilters();
        $scope.data.filtersIds = _.pluck(_.filter($scope.data.filters, function(f) {
            if (f.level === 0 || _.isUndefined(f.level)) {
                return true;
            }
        }), 'id').join(',');
    });

    $scope.$watch('data.questionnaires', function(newQuests, oldQuests) {
        var newQuestionnaires = _.difference(_.pluck(newQuests, 'id'), _.pluck(oldQuests, 'id'));
        newQuestionnaires = newQuestionnaires ? newQuestionnaires : [];

        if (!_.isEmpty(newQuestionnaires)) {

            TableFilter.loadQuestionnaires(newQuestionnaires).then(function() {
                $scope.orderQuestionnaires(false);
                $scope.data.geonamesIds = _.uniq(_.pluck($scope.data.questionnaires, function(q) {
                    return q.geoname.id;
                })).join(',');
            });

        } else if (($scope.data.geoname || $scope.data.survey) && _.isEmpty($scope.data.questionnaires)) {
            $scope.addQuestionnaire();
        }

        if (firstLoading === true && $scope.data.filters && $scope.data.questionnaires) {
            checkSelectionExpand();
        }
    });

    /**************************************************************************/
    /******************************************************** Scope functions */
    /**************************************************************************/

    $scope.isIncludedInUsedThematics = function(thematics) {
        var used = false;

        _.forEach(thematics, function(thematicId) {
            if (_.indexOf($scope.usedThematics, thematicId) != -1) {
                used = true;
                return false;
            }
        });

        return used;
    };

    $scope.orderQuestionnaires = function(reverse) {
        $scope.data.questionnaires = $filter('orderBy')($scope.data.questionnaires, 'survey.year', reverse);
    };

    /**
     * Returns true if there a questionnaire or a filter which is unsaved
     * @returns {boolean}
     */
    $scope.hasUnsavedElement = function() {
        return !!(_.find($scope.data.questionnaires, function(questionnaire) {
            return _.isUndefined(questionnaire.id);
        }) || _.find($scope.data.filters, function(filter) {
            return !TableFilter.isValidId(filter);
        }));
    };

    $scope.saveComment = function(questionnaire) {
        Restangular.restangularizeElement(null, questionnaire, 'Questionnaire');
        questionnaire.put();
    };

    $scope.copyFilterUsages = function(dest, src) {

        if (dest.id && src.id) {
            // add an array with 1 element to disable the ability to duplicate formulas again
            dest.filterQuestionnaireUsages = true;
            dest.isLoading = true;
            $http.get('/api/questionnaire/copyFilterUsages', {
                params: {
                    dest: dest.id,
                    src: src.id
                }
            }).success(function() {
                dest.isLoading = false;
                $scope.refresh(false, true);
            });
        }
    };

    /**************************************************************************/
    /****************************************************** Private functions */
    /**************************************************************************/

    /**
     * Hide selection panels on :
     *  - survey selection
     *  - country selection
     *  - filter set selection
     *  - filter's children selection
     *  - page loading
     *
     *  If there are filter and questionnaires selected after this manipulation
     *  Don't hide selection panes if select with free selection tool on "Selected" tab.
     *  The button "Expand/Compress Selection" reflects this status and allow to change is again.
     */
    var checkSelectionExpand = function() {
        firstLoading = false;
        if ($scope.data.filters && $scope.data.filters.length && $scope.data.questionnaires && $scope.data.questionnaires.length) {
            $scope.expandSelection = false;
        } else {
            $scope.expandSelection = true;
        }
    };

    $scope.cancel = function() {
        $location.url($location.search().returnUrl);
    };

    $rootScope.$on('gims-tablefilter-show-labels-toggled', function() {
        $scope.$broadcast('vsRepeatTrigger');
    });
});
