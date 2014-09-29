
angular.module('myApp').controller('ContributeCtrl', function($scope, $http, $rootScope) {
    'use strict';

    if ($rootScope.user) {
        $http.get('/api/user/' + $rootScope.user.id + '/statistics').success(function(data) {
            $scope.statistics = data;
        });
    }
});
