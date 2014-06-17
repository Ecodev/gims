
angular.module('myApp').controller('ContributeCtrl', function($scope, $http, $rootScope) {
    'use strict';

    $http.get('/api/user/' + $rootScope.user.id + '/statistics').success(function(data) {
        $scope.statistics = data;
    });
});
