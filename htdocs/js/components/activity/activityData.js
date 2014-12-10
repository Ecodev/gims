angular.module('myApp.directives').directive('gimsActivityData', function() {
    return {
        restrict: 'E',
        templateUrl: '/js/components/activity/activityData.phtml',
        controller: function($scope) {
            $scope.apiable = {
                filter: true,
                questionnaire: true,
                filterQuestionnaireUsage: true,
                questionnaireUsage: true,
                rule: true,
                answer: true,
                question: true
            };
        }
    };
});
