angular.module('myApp').controller('Contribute/RequestRolesCtrl', function($scope, $http, $location, $routeParams) {
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

    $scope.$watch('types', function() {
        if ($scope.types && $scope.types.length) {
            $location.search('types', $scope.types.join(','));
        } else {
            $location.search('types', null);
        }
    });

    if ($routeParams.types) {
        $scope.types = $routeParams.types.split(',');
    }

    /**
     * @todo : implement confirm action modal
     */
        //$scope.confirmSendRequest = function() {
        //    Modal.confirmAction(???);
        //},

    $scope.sendRequest = function() {
        $http.get('/api/roles-request/sendAccessDemand', {
            params: {
                geonames: _.pluck($scope.geonames, 'id').join(','),
                roles: _.pluck($scope.roles, 'id').join(','),
                types: $scope.types.join(',')
            }
        }).success(function() {});
    };

});
