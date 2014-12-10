angular.module('myApp.directives').directive('gimsActivityUsage', function() {
    return {
        restrict: 'E',
        scope: {
            activity: '='
        },
        templateUrl: '/js/components/activity/usage/activityUsage.phtml'
    };
});
