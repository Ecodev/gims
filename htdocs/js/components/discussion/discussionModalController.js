/**
 * Controller for user editing within a modal
 */
angular.module('myApp').controller('DiscussionModalCtrl', function($scope, $modalInstance, $q, discussion, Restangular) {
    'use strict';
    var discussionFields = 'name,comments.metadata,comments.creator.gravatar';
    $scope.discussion = discussion;

    $scope.loadDiscussion = function() {

        // Whatever happens, we must exit this function with an array of comments
        $scope.discussion.comments = [];

        // If the discussion exists a direct load is easy
        if ($scope.discussion.id) {
            Restangular.one('discussion', $scope.discussion.id).get({fields: discussionFields}).then(function(loadedDiscussion) {
                $scope.discussion = loadedDiscussion;
            });
        } else {

            var surveyId = $scope.discussion.survey || null;
            var questionnaireId = $scope.discussion.questionnaire || null;
            var filterId = $scope.discussion.filter || null;
            var params = {
                fields: discussionFields + ',survey,questionnaire,filter',
                surveys: surveyId,
                questionnaires: questionnaireId,
                filters: filterId
            };

            // Make a fuzzy search on server, we may not get anything back
            Restangular.all('discussion').getList(params).then(function(discussions) {

                // We may find several fuzzy matches from server, we need the exact match
                _.forEach(discussions, function(discussion) {
                    if (((!discussion.survey && !surveyId) || (discussion.survey && discussion.survey.id == surveyId)) &&
                            ((!discussion.questionnaire && !questionnaireId) || (discussion.questionnaire && discussion.questionnaire.id == questionnaireId)) &&
                            ((!discussion.filter && !filterId) || (discussion.filter && discussion.filter.id == filterId))) {
                        $scope.discussion = discussion;
                        return false;
                    }
                });
            });
        }
    };

    $scope.loadDiscussion();

    $scope.comment = {};
    $scope.postComment = function() {

        // First create discussion if needed
        var discussionPromise;
        if ($scope.discussion.id) {
            var deferred = $q.defer();
            deferred.resolve($scope.discussion);
            discussionPromise = deferred.promise;
        } else {
            discussionPromise = Restangular.all('discussion').post(discussion, {fields: discussionFields});
            discussionPromise.then(function(newDiscussion) {
                $scope.discussion = newDiscussion;
            });
        }

        // Then append comment to discussion
        discussionPromise.then(function(discussion) {
            $scope.comment.discussion = discussion.id;
            Restangular.all('comment').post($scope.comment, {fields: 'metadata,creator.gravatar'}).then(function(comment) {
                discussion.comments.push(comment);
                $scope.comment = {};
            });
        });
    };

    $scope.$dismiss = function() {
        $modalInstance.dismiss();
    };

});
