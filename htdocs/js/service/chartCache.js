angular.module('myApp.services').factory('ChartCache', function(Utility) {
    'use strict';

    var firstExecution = true;
    var index = {};

    /**
     * This function set initiates all questionnaires with data loaded with first one (except values)
     * It allows to see ignored elements if they are ignored globally before having loading the data specific to asked questionnaire
     * The data is too big to be executed on the fly and is not needed, so the timeout waits until angularjs generates page.
     * Then generates index in background
     *
     * @param part
     * @param questionnaire
     * @param callback function to execute when data is indexed (usefull for then change status to ignored if they're ignored in url)
     */
    var propagateRetrievedQuestionnaires = function(part, questionnaire) {
        if (questionnaire.id) {
            questionnaire = index[questionnaire.id];
            if (firstExecution) {
                firstExecution = false;
                _.forEach(index, function(tmpQuestionnaire, tmpQuestionnaireId) {
                    if (tmpQuestionnaireId != questionnaire.id, questionnaire) {
                        _.forEach(tmpQuestionnaire.hFilters, function(hFilter, hFilterId) {
                            _.forEach(questionnaire.filters, function(filter) {
                                if (filter && !_.isUndefined(filter.filter.hFilters[hFilterId])) {
                                    cache(part, tmpQuestionnaireId, {filter: filter.filter});
                                }
                            });
                        });
                    }
                });
            }
        }
    };

    /**
     * Put in index the state of all objects that have been selected / viewed / ignored.
     *
     * The index is a index table.
     *
     * Feed an object index that is structured as followed :
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
     * @param part
     * @param questionnaire
     * @param filter
     * @param ignored
     * @returns questionnaire
     */
    var cache = function(part, questionnaire, filter, ignored) {
        if (!part || !questionnaire) {
            return;
        }

        // initiates high filter index and retrieves name for display in panel
        questionnaire = indexQuestionnaire(questionnaire, ignored && !filter ? ignored : false);
        indexQuestionnaireAndFilter(part, questionnaire.id, filter, ignored);
        return questionnaire;
    };

    var indexQuestionnaire = function(questionnaire, ignored) {
        if (questionnaire) {
            if (!_.isObject(questionnaire)) {
                questionnaire = {id: questionnaire};
            }
            if (!index[questionnaire.id]) {
                index[questionnaire.id] = {};
            }

            if (questionnaire.id) {
                index[questionnaire.id].id = questionnaire.id;
            }
            if (questionnaire.name) {
                index[questionnaire.id].name = questionnaire.name;
            }

            if (!index[questionnaire.id].hFilters) {
                index[questionnaire.id].hFilters = {};
            }

            // assigns root filters to which this filter belongs to.
            _.forEach(questionnaire.hFilters, function(hFilter, hFilterId) {
                index[questionnaire.id].hFilters[hFilterId] = hFilter;
            });

            if (questionnaire.usages) {
                index[questionnaire.id].usages = questionnaire.usages;
            }

            if (ignored) {
                index[questionnaire.id].ignored = true;
            }
            return index[questionnaire.id];
        }
    };

    var indexQuestionnaireAndFilter = function(part, questionnaireId, filter, ignored) {
        if (filter) {
            if (!index[questionnaireId].filters) {
                index[questionnaireId].filters = {};
            }

            if (!index[questionnaireId].filters[filter.filter.id]) {
                index[questionnaireId].filters[filter.filter.id] = {};
                index[questionnaireId].filters[filter.filter.id].filter = {};
                index[questionnaireId].filters[filter.filter.id].values = {};
                index[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored = {};
            }

            if (filter.filter.id) {
                index[questionnaireId].filters[filter.filter.id].filter.id = filter.filter.id;
            }
            if (filter.filter.name) {
                index[questionnaireId].filters[filter.filter.id].filter.name = filter.filter.name;
            }
            if (filter.filter.color) {
                index[questionnaireId].filters[filter.filter.id].filter.color = filter.filter.color;
            }
            if (filter.filter.originalDenomination) {
                index[questionnaireId].filters[filter.filter.id].filter.originalDenomination = filter.filter.originalDenomination;
            }

            if (!index[questionnaireId].filters[filter.filter.id].filter.hFilters) {
                index[questionnaireId].filters[filter.filter.id].filter.hFilters = {};
            }

            if (filter.usages) {
                index[questionnaireId].filters[filter.filter.id].usages = filter.usages;
            }

            // assigns root filters to which this filter belongs to.
            _.forEach(filter.filter.hFilters, function(hFilter, hFilterId) {
                if (_.isNull(hFilter)) {

                    if (!index[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId]) {
                        index[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId] = {};
                    }

                    if (filter.filter.level >= 0) {
                        index[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId].level = filter.filter.level;
                    }
                    if (filter.filter.sorting) {
                        index[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId].sorting = filter.filter.sorting;
                    }
                } else {
                    index[questionnaireId].filters[filter.filter.id].filter.hFilters[hFilterId] = hFilter;
                }
            });

            // if no ignored params and no ignored status specified on filter but questionnaire has one, filter inherits questionnaire status
            if (_.isUndefined(ignored) && !_.isUndefined(index[questionnaireId].ignored) && _.isUndefined(index[questionnaireId].filters[filter.filter.id].filter.ignored)) {
                index[questionnaireId].filters[filter.filter.id].filter.ignored = index[questionnaireId].ignored;

                // if ignored param specified, filter gets its value
            } else if (!_.isUndefined(ignored)) {
                index[questionnaireId].filters[filter.filter.id].filter.ignored = ignored;
            }

            if (filter.values) {
                index[questionnaireId].filters[filter.filter.id].values[part.name] = filter.values[0][part.name];
            }
            if (!_.isUndefined(filter.valuesWithoutIgnored)) {
                index[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored[part.name] = filter.valuesWithoutIgnored[0][part.name];
            } else {
                delete(index[questionnaireId].filters[filter.filter.id].valuesWithoutIgnored[part.name]);
            }
        }
    };

    return {
        cache: cache,
        propagateRetrievedQuestionnaires: propagateRetrievedQuestionnaires,
        reset: function() {
            Utility.resetObject(index);
            firstExecution = true;
        },
        getCache: function() {
            return index;
        }
    };
});
