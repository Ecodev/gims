/**
 * Contribute Discussion Controller
 */
angular.module('myApp').controller('Contribute/ActivityCtrl', function($scope, $routeParams, $location, Restangular) {
    'use strict';

    $scope.page = 1;

    $scope.toggleAllDetails = function() {
        var bool = !$scope.activities[0].showDetails;
        _.forEach($scope.activities, function(activity) {
            activity.showDetails = bool;
        });
    };

    $scope.$watch('page', function() {
        retrieveData();
    });

    var retrieveData = _.debounce(function() {
        if ($routeParams.id) {
            $scope.activities = Restangular.one('user', $routeParams.id).getList('activity', {fields: 'creator.gravatar,dateCreated,recordType', perPage:10, page:$scope.page}).$object;
        } else {
            $scope.activities = Restangular.all('activity').getList({fields: 'creator.gravatar,dateCreated,recordType', perPage:10, page:$scope.page}).$object;
        }
    }, 100);

    function valid() {
        $scope.page = Math.floor($scope.page);
        if ($scope.page < 1) {
            $scope.page = 1;
        }
    }

    $scope.previous = function() {
        $scope.page--;
        valid();
    };

    $scope.next = function() {
        $scope.page++;
        valid();
    };

});
