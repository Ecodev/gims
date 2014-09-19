angular.module('myApp').controller('QuestionnaireMenuCtrl', function($scope, questionnaire, TableFilter) {
    'use strict';

    $scope.questionnaire = questionnaire;
    $scope.data = TableFilter.getData();

    /**
     * Remove column (questionnaire)
     * @param questionnaire
     */
    $scope.removeQuestionnaire = function(questionnaire) {
        _.remove($scope.data.questionnaires, function(q) {
            return q.id == questionnaire.id;
        });
        TableFilter.updateUrl('questionnaires');
    };
});
