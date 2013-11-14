/**
 * Controller for user editing within a modal
 */
angular.module('myApp').controller('UserModalCtrl', function($scope, $modalInstance, Restangular, user) {
    'use strict';

    $scope.$dismiss = function()
    {
        $modalInstance.dismiss();
    };


    $scope.localUser = user;
    var userQueryParams = {fields: 'phone,skype,job,ministry,address,zip,city,country'};


    // Load all information if editing existing user
    if (user && user.id) {
        Restangular.one('user', user.id).get(userQueryParams).then(function(user) {
            $scope.localUser = user;
        });
    }

    $scope.addUser = function() {
        Restangular.all('user').post($scope.localUser).then(function(user) {
            $modalInstance.close(user);
        });
    };

    $scope.saveUser = function() {
        var localUser = Restangular.restangularizeElement(null, $scope.localUser, 'user');
        localUser.put(userQueryParams).then(function(user) {
            $modalInstance.close(user);
        });
    };
});
