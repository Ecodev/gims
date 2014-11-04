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

    $scope.$watch('surveyTypes', function() {
        if ($scope.surveyTypes && $scope.surveyTypes.length) {
            $location.search('surveyTypes', $scope.surveyTypes.join(','));
        } else {
            $location.search('surveyTypes', null);
        }
    });

    if ($routeParams.surveyTypes) {
        $scope.surveyTypes = $routeParams.surveyTypes.split(',');
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
                types: $scope.surveyTypes.join(',')
            }
        }).success(function() {});
    };

});
