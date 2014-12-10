angular.module('myApp.directives').directive('gimsActivityRule', function() {
    return {
        restrict: 'E',
        scope: {
            activity: '='
        },
        templateUrl: '/js/components/activity/rule/activityRule.phtml',
    };
});
