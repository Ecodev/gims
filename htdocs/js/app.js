'use strict';


// Declare app level module which depends on filters, and services
angular.module('myApp', ['myApp.filters', 'myApp.services', 'myApp.directives', 'ui.bootstrap']).
        config(['$routeProvider', '$httpProvider', '$locationProvider', function($routeProvider, $httpProvider, $locationProvider) {
        $routeProvider.when('/home', {templateUrl: '/template/application/index/home', controller: MyCtrl1});
        $routeProvider.when('/about', {templateUrl: '/template/application/index/about', controller: MyCtrl2});
        $routeProvider.when('/browse', {templateUrl: '/template/application/index/browse', controller: MyCtrl2});
        $routeProvider.when('/contribute', {templateUrl: '/template/application/index/contribute', controller: MyCtrl2});
        $routeProvider.otherwise({redirectTo: '/home'});

        $locationProvider.html5Mode(true);
    }]);
