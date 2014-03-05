/* jasmine specs for controllers go here */


describe('ContributeCtrl', function() {
    'use strict';

    // load the controller's module
    beforeEach(module('myApp'));

    var ctrl, scope;

    beforeEach(inject(function($controller) {
        scope = {};
        ctrl = $controller('ContributeCtrl', {
            $scope: scope
        });
    }));


    it('should ....', function() {
        //spec body
    });
});
