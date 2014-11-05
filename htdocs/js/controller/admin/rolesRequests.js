angular.module('myApp').controller('Admin/RolesRequestsCtrl', function($scope, $http, $location, $routeParams, Restangular) {
    'use strict';

    $scope.surveyTypesList = [
        {
            value: 'jmp',
            display: 'JMP'
        }, {
            value: 'glaas',
            display: 'Glaas'
        }, {
            value: 'nsa',
            display: 'Nsa'
        },
    ];

    if ($routeParams.geonames && $routeParams.roles && $routeParams.types && $routeParams.user) {

        $scope.user = Restangular.one('user', $routeParams.user).get().$object;

        // /api/roles-request/getRequests filter results by current user
        $http.get('/api/roles-request/getRequests', {
            params: {
                geonames: $routeParams.geonames,
                roles: $routeParams.roles,
                types: $routeParams.types
            }
        }).success(function(geonames) {
            $scope.geonames = geonames;
        });
    }

});
