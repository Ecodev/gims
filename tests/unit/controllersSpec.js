'use strict';

/* jasmine specs for controllers go here */

describe('MyCtrl1', function() {

    // load the controller's module
    beforeEach(module('myApp'));

    var myCtrl1, scope;

    beforeEach(inject(function($controller) {
        scope = {};
        myCtrl1 = $controller('MyCtrl1', {
            $scope: scope
        });
    }));


    it('should ....', function() {
        //spec body
    });
});


describe('ContributeCtrl', function() {
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
