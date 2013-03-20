'use strict';


// Declare app level module which depends on filters, and services
angular.module('myApp', [
        'ngResource',
        'ui',
        'ui.bootstrap',
        'ngGrid',
        'myApp.filters',
        'myApp.services',
        'myApp.directives',
        'myApp.answerService',
        'myApp.questionnaireService',
        'myApp.questionnaireAnswerService'
    ]).
    config(function ($routeProvider, $locationProvider) {
        $routeProvider.when('/home', {templateUrl: '/template/application/index/home', controller: 'MyCtrl1'});
        $routeProvider.when('/about', {templateUrl: '/template/application/index/about', controller: 'MyCtrl2'});
        $routeProvider.when('/browse', {templateUrl: '/template/browse', controller: 'MyCtrl2'});
        $routeProvider.when('/contribute', {templateUrl: '/template/contribute', controller: 'MyCtrl2'});
        $routeProvider.when('/contribute/questionnaire', {templateUrl: '/template/contribute/questionnaire', controller: 'QuestionnaireCtrl'});
        $routeProvider.when('/contribute/questionnaire/:id', {templateUrl: '/template/contribute/questionnaire', controller: 'QuestionnaireCtrl'});
        $routeProvider.otherwise({redirectTo: '/home'});

        $locationProvider.html5Mode(true);
    });
