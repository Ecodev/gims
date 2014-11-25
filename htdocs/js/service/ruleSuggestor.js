/**
 * Service suggesting "nearby" rules for an hypothetical usage
 */
angular.module('myApp.services').factory('RuleSuggestor', function($q, Restangular) {
    'use strict';
    var suggestedRules = {};
    var suggestedUsages = [];
    var suggestedQuestionnaires = [];

    function getSuggestedRules(usage) {
        updateFilterUsages(usage);
        updateQuestionnaireUsages(usage);
        updateGeonameUsages(usage);

        return suggestedRules;
    }

    /**
     * Return an array that will be filled with questionnaires that the given
     * rule is already related to
     * @param {object} rule
     * @returns {Array}
     */
    function getSuggestedQuestionnaires(rule) {

        suggestedQuestionnaires = [];
        if (rule.id) {
            Restangular.one('rule', rule.id).get({fields: 'filterQuestionnaireUsages.filter.thematicFilter,filterQuestionnaireUsages.questionnaire,questionnaireUsages.questionnaire'}).then(function(rule) {

                var usages = rule.filterQuestionnaireUsages.concat(rule.questionnaireUsages);
                var questionnaires = _.sortBy(_.uniq(_.pluck(usages, 'questionnaire'), false, 'id'), false, function(questionnaire) {
                    return questionnaire.name;
                });

                // Move item to the public variable (while still preserving the public variable reference intact)
                _.forEach(questionnaires, function(q) {
                    suggestedQuestionnaires.push(q);
                });
            });
        }

        return suggestedQuestionnaires;
    }

    /**
     * Return an array that will be filled with questionnaireUsages of the
     * given questionnaire, and optionnally filtered by thematic if the rule
     * already has a related thematic
     * @param {object} rule
     * @param {object} questionnaire
     * @returns {Array}
     */
    function getSuggestedQuestionnaireUsages(rule, questionnaire) {

        suggestedUsages = [];

        // If the rule exist, try to find its thematics
        var thematicDeferred = $q.defer();
        if (rule.id) {
            Restangular.one('rule', rule.id).get({fields: 'filterQuestionnaireUsages.filter.thematicFilter,filterGeonameUsages.filter.thematicFilter'}).then(function(rule) {

                var thematics = [];
                var usages = rule.filterQuestionnaireUsages.concat(rule.filterGeonameUsages);
                _.forEach(usages, function(usage) {
                    thematics.push(usage.filter.thematicFilter.id);
                });

                thematicDeferred.resolve(_.uniq(thematics));
            });
        } else {
            thematicDeferred.resolve([]);
        }

        // Get all usages, and when we also have thematics filter and affect final result
        Restangular.one('questionnaire', questionnaire.id).getList('questionnaireUsage', {perPage: 500, fields: 'thematicFilter'}).then(function(usages) {

            usages = _.sortBy(usages, function(usage) {
                return usage.rule.name;
            });

            thematicDeferred.promise.then(function(thematicIds) {
                _.forEach(usages, function(usage) {
                    if (thematicIds.length) {
                        _.forEach(thematicIds, function(thematicId) {

                            // Only keep usage with same thematic, if we know a thematic
                            if (usage.thematicFilter.id == thematicId) {
                                suggestedUsages.push(usage);
                                return false;
                            }
                        });
                    } else {
                        suggestedUsages.push(usage);
                    }
                });
            });
        });

        return suggestedUsages;
    }

    /**
     * From an array of usages, return a unique, sorted array of rules
     * @param {array} usages
     * @returns {array} rules
     */
    function fromUsagesToRules(usages) {
        return _.sortBy(_.uniq(_.pluck(usages, 'rule'), function(rule) {
            return rule.id;
        }), function(rule) {
            return rule.name.toLowerCase();
        });
    }

    function updateGeonameUsages(referenceUsage) {
        suggestedRules.geoname = [];
        if (referenceUsage.geoname && referenceUsage.geoname.id) {
            Restangular.one('geoname', referenceUsage.geoname.id).getList('filterGeonameUsage', {fields: 'rule.structure'}).then(function(usages) {
                suggestedRules.geoname = fromUsagesToRules(usages);
            });
        }
    }

    function updateFilterUsages(referenceUsage) {
        if (referenceUsage.filter && referenceUsage.filter.id) {
            Restangular.one('filter', referenceUsage.filter.id).getList('filterQuestionnaireUsage', {fields: 'rule.structure'}).then(function(usages) {
                suggestedRules.filter = fromUsagesToRules(usages);
            });
        } else {
            suggestedRules.filter = [];
        }
    }

    function updateQuestionnaireUsages(referenceUsage) {
        if (referenceUsage.questionnaire && referenceUsage.questionnaire.id) {
            Restangular.one('questionnaire', referenceUsage.questionnaire.id).get({fields: 'filterQuestionnaireUsages.rule,questionnaireUsages.rule,filterQuestionnaireUsages.rule.structure,questionnaireUsages.rule.structure'}).then(function(object) {
                suggestedRules.questionnaire = fromUsagesToRules(object.filterQuestionnaireUsages.concat(object.questionnaireUsages));
            });
        } else {
            suggestedRules.questionnaire = [];
        }
    }

    // Return public API
    return {
        getSuggestedRules: getSuggestedRules,
        getSuggestedQuestionnaires: getSuggestedQuestionnaires,
        getSuggestedQuestionnaireUsages: getSuggestedQuestionnaireUsages
    };
});
