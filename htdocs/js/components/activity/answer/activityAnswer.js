angular.module('myApp.directives').directive('gimsActivityAnswer', function() {
    return {
        restrict: 'E',
        scope: {
            activity: '='
        },
        templateUrl: '/js/components/activity/answer/activityAnswer.phtml'
    };
});
