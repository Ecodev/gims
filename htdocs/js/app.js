'use strict';


// Declare app level module which depends on filters, and services
angular.module('myApp', [
        'ngResource',
        'restangular',
        'ui',
        'ui.bootstrap',
        'ngGrid',
        'myApp.filters',
        'myApp.services',
        'myApp.directives',
        'chartsExample.directives',
        '$strap.directives'
    ]).
    config(function ($routeProvider, $locationProvider, $dialogProvider, RestangularProvider) {
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
        $routeProvider.when('/admin/questionnaire/edit/:id', {templateUrl: '/template/admin/questionnaire/crud', controller: 'Admin/Questionnaire/CrudCtrl'});
        $routeProvider.when('/admin/questionnaire/new', {templateUrl: '/template/admin/questionnaire/crud', controller: 'Admin/Questionnaire/CrudCtrl'});
        $routeProvider.when('/admin/survey', {templateUrl: '/template/admin/survey', controller: 'Admin/SurveyCtrl'});
        $routeProvider.when('/admin/survey/edit/:id', {templateUrl: '/template/admin/survey/crud', controller: 'Admin/Survey/CrudCtrl'});
        $routeProvider.when('/admin/survey/new', {templateUrl: '/template/admin/survey/crud', controller: 'Admin/Survey/CrudCtrl'});
        $routeProvider.when('/admin/user', {templateUrl: '/template/admin/user', controller: 'Admin/UserCtrl'});
        $routeProvider.when('/admin/user/edit/:id', {templateUrl: '/template/admin/user/crud', controller: 'Admin/User/CrudCtrl'});
        $routeProvider.when('/admin/user/new', {templateUrl: '/template/admin/user/crud', controller: 'Admin/User/CrudCtrl'});
        $routeProvider.otherwise({redirectTo: '/home'});

        $locationProvider.html5Mode(true);

        $dialogProvider.options({backdropFade: true, dialogFade:true});

        RestangularProvider.setBaseUrl('/api');

    });

// Here we declare all our modules, so we can get them back whenever we want
angular.module('myApp.filters', []);
angular.module('myApp.services', ['ngResource']);
angular.module('myApp.directives', []);