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
        'myApp.resourceServices',
        'myApp.adminSurveyServices',
        'chartsExample.directives',
        '$strap.directives'
    ]).
    config(function ($routeProvider, $locationProvider) {
        $routeProvider.when('/home', {templateUrl: '/template/application/index/home', controller: 'MyCtrl1'});
        $routeProvider.when('/about', {templateUrl: '/template/application/index/about', controller: 'MyCtrl1'});
        $routeProvider.when('/browse', {templateUrl: '/template/browse', controller: 'MyCtrl1'});
        $routeProvider.when('/browse/chart', {templateUrl: '/template/browse/chart', controller: 'Browse/ChartCtrl', reloadOnSearch: false});
        $routeProvider.when('/contribute', {templateUrl: '/template/contribute', controller: 'ContributeCtrl'});
        $routeProvider.when('/contribute/questionnaire', {templateUrl: '/template/contribute/questionnaire', controller: 'Contribute/QuestionnaireCtrl'});
        $routeProvider.when('/contribute/questionnaire/:id', {templateUrl: '/template/contribute/questionnaire', controller: 'Contribute/QuestionnaireCtrl'});
        $routeProvider.when('/admin', {redirectTo: '/contribute'});
        $routeProvider.when('/admin/question/edit/:id', {templateUrl: '/template/admin/question/crud', controller: 'Admin/Question/CrudCtrl'});
        $routeProvider.when('/admin/question/new', {templateUrl: '/template/admin/question/crud', controller: 'Admin/Question/CrudCtrl'});
        $routeProvider.when('/admin/survey', {templateUrl: '/template/admin/survey', controller: 'Admin/SurveyCtrl'});
        $routeProvider.when('/admin/survey/edit/:id', {templateUrl: '/template/admin/survey/crud', controller: 'Admin/Survey/CrudCtrl'});
        $routeProvider.when('/admin/survey/new', {templateUrl: '/template/admin/survey/crud', controller: 'Admin/Survey/CrudCtrl'});
        $routeProvider.when('/admin/user', {templateUrl: '/template/admin/user', controller: 'Admin/UserCtrl'});
        $routeProvider.when('/admin/user/edit/:id', {templateUrl: '/template/admin/user/edit', controller: 'Admin/User/CrudCtrl'});
        $routeProvider.when('/admin/user/new', {templateUrl: '/template/admin/user/edit', controller: 'Admin/User/CrudCtrl'});
        $routeProvider.otherwise({redirectTo: '/home'});

        $locationProvider.html5Mode(true);
    });
