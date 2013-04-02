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


describe('MyCtrl2', function() {
    // load the controller's module
    beforeEach(module('myApp'));
    
    var myCtrl2, scope;

    beforeEach(inject(function($controller) {
        scope = {};
        myCtrl2 = $controller('MyCtrl2', {
            $scope: scope
        });
    }));


    it('should ....', function() {
        //spec body
    });
});
