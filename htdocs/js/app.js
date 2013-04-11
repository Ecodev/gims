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
        'myApp.questionService',
        'myApp.questionnaireService'
    ]).
    config(function ($routeProvider, $locationProvider) {
        $routeProvider.when('/home', {templateUrl: '/template/application/index/home', controller: 'MyCtrl1'});
        $routeProvider.when('/about', {templateUrl: '/template/application/index/about', controller: 'MyCtrl2'});
        $routeProvider.when('/browse', {templateUrl: '/template/browse', controller: 'MyCtrl2'});
        $routeProvider.when('/contribute', {templateUrl: '/template/contribute', controller: 'MyCtrl2'});
        $routeProvider.when('/contribute/questionnaire', {templateUrl: '/template/contribute/questionnaire', controller: 'Contribute/QuestionnaireCtrl'});
        $routeProvider.when('/contribute/questionnaire/:id', {templateUrl: '/template/contribute/questionnaire', controller: 'Contribute/QuestionnaireCtrl'});
        $routeProvider.when('/admin', {templateUrl: '/template/admin', controller: 'AdminCtrl'});
        $routeProvider.when('/admin/survey', {templateUrl: '/template/admin/survey', controller: 'Admin/SurveyCtrl'});
//        $routeProvider.when('/admin/survey/edit/:id', {templateUrl: '/template/admin/survey/edit', controller: 'Admin/Survey/EditCtrl'});
        $routeProvider.otherwise({redirectTo: '/home'});

        $locationProvider.html5Mode(true);
    });
