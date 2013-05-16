
angular.module('myApp').controller('ContributeCtrl', function($scope, $http) {
    'use strict';

    // TODO: replace with actual logged in user ID
    $http.get('/api/user/1/statistics').success(function(data) {
        $scope.statistics = data;
    });
});