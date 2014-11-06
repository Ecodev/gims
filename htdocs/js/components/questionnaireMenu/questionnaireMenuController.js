angular.module('myApp').controller('QuestionnaireMenuCtrl', function($scope, questionnaire, TableFilter, Restangular, $rootScope, DiscussionModal) {
    'use strict';

    $scope.questionnaire = questionnaire;
    $scope.data = TableFilter.getData();
    $scope.saveAll = TableFilter.saveAll;
    $scope.questionnaireCanBeSaved = TableFilter.questionnaireCanBeSaved;
    $scope.toggleShowLabels = TableFilter.toggleShowLabels;
    $scope.openDiscussion = DiscussionModal.open;

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

    $scope.toggleQuestionnaireCompletedPublished = function(questionnaire) {

        if (questionnaire.status == 'published') {
            questionnaire.status = 'completed';
        } else if (questionnaire.status == 'completed') {
            questionnaire.status = 'published';
        }

        // avoid to send all data when just status and id are enough
        var miniQuestionnaire = {
            id: questionnaire.id,
            status: questionnaire.status
        };

        Restangular.restangularizeElement(null, miniQuestionnaire, 'Questionnaire');
        miniQuestionnaire.put({fields: 'permissions'}).then(function(q) {
            questionnaire.permissions = q.permissions;
            $rootScope.$emit('gims-tablefilter-permissions-changed');
        });

    };

});
