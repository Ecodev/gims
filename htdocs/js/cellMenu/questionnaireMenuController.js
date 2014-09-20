angular.module('myApp').controller('QuestionnaireMenuCtrl', function($scope, questionnaire, TableFilter) {
    'use strict';

    $scope.questionnaire = questionnaire;
    $scope.data = TableFilter.getData();
    $scope.saveAll = TableFilter.saveAll;
    $scope.questionnaireCanBeSaved = TableFilter.questionnaireCanBeSaved;

    /**
     * Remove column (questionnaire)
     * @param questionnaire
     */
    $scope.removeQuestionnaire = function(questionnaire) {
        _.remove($scope.data.questionnaires, function(q) {
            return q === questionnaire;
        });
        TableFilter.updateUrl('questionnaires');
    };
});
