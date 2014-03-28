// Declare app level module which depends on filters, and services
angular.module('myApp', [
    'ngRoute',
    'ngResource',
    'restangular',
    'ui.utils',
    'ui.select2',
    'ui.bootstrap',
    'ngGrid',
    'myApp.filters',
    'myApp.services',
    'myApp.directives',
    'http-auth-interceptor',
    'chartsExample.directives',
    'colorpicker.module',
    'ngAnimate',
    'ui.ace'
]).
        config(function($routeProvider, $locationProvider, RestangularProvider) {
            'use strict';

            $routeProvider.when('/home', {templateUrl: '/template/application/index/home'});
            $routeProvider.when('/about', {templateUrl: '/template/application/index/about'});
            $routeProvider.when('/browse', {templateUrl: '/template/browse'});
            $routeProvider.when('/browse/chart', {templateUrl: '/template/browse/chart', controller: 'Browse/ChartCtrl', reloadOnSearch: false});
            $routeProvider.when('/browse/table/filter', {templateUrl: '/template/browse/table/filter', controller: 'Browse/Table/FilterCtrl', reloadOnSearch: false});
            $routeProvider.when('/browse/table/questionnaire', {templateUrl: '/template/browse/table/questionnaire', controller: 'Browse/Table/QuestionnaireCtrl', reloadOnSearch: false});
            $routeProvider.when('/browse/table/country', {templateUrl: '/template/browse/table/country', controller: 'Browse/Table/CountryCtrl', reloadOnSearch: false});
            $routeProvider.when('/contribute', {templateUrl: '/template/contribute', controller: 'ContributeCtrl'});
            $routeProvider.when('/contribute/questionnaire', {templateUrl: '/template/contribute/questionnaire', controller: 'Contribute/QuestionnaireCtrl'});
            $routeProvider.when('/contribute/questionnaire/:id', {templateUrl: '/template/contribute/questionnaire', controller: 'Contribute/QuestionnaireCtrl'});
            $routeProvider.when('/admin', {redirectTo: '/contribute'});
            $routeProvider.when('/admin/filter-set', {templateUrl: '/template/admin/filter-set', controller: 'Admin/FilterSetCtrl'});
            $routeProvider.when('/admin/filter-set/new', {templateUrl: '/template/admin/filter-set/crud', controller: 'Admin/FilterSet/CrudCtrl'});
            $routeProvider.when('/admin/filter-set/edit/:id', {templateUrl: '/template/admin/filter-set/crud', controller: 'Admin/FilterSet/CrudCtrl', reloadOnSearch: false});
            $routeProvider.when('/admin/filter', {templateUrl: '/template/admin/filter', controller: 'Admin/FilterCtrl'});
            $routeProvider.when('/admin/filter/new', {templateUrl: '/template/admin/filter/crud', controller: 'Admin/Filter/CrudCtrl'});
            $routeProvider.when('/admin/filter/edit/:id', {templateUrl: '/template/admin/filter/crud', controller: 'Admin/Filter/CrudCtrl'});
            $routeProvider.when('/admin/question/edit/:id', {templateUrl: '/template/admin/question/crud', controller: 'Admin/Question/CrudCtrl'});
            $routeProvider.when('/admin/question/new', {templateUrl: '/template/admin/question/crud', controller: 'Admin/Question/CrudCtrl'});
            $routeProvider.when('/admin/questionnaire/edit/:id', {templateUrl: '/template/admin/questionnaire/crud', controller: 'Admin/Questionnaire/CrudCtrl'});
            $routeProvider.when('/admin/questionnaire/new', {templateUrl: '/template/admin/questionnaire/crud', controller: 'Admin/Questionnaire/CrudCtrl'});
            $routeProvider.when('/admin/survey', {templateUrl: '/template/admin/survey', controller: 'Admin/SurveyCtrl'});
            $routeProvider.when('/admin/survey/edit/:id', {templateUrl: '/template/admin/survey/crud', controller: 'Admin/Survey/CrudCtrl', reloadOnSearch: false});
            $routeProvider.when('/admin/survey/new', {templateUrl: '/template/admin/survey/crud', controller: 'Admin/Survey/CrudCtrl'});
            $routeProvider.when('/admin/user', {templateUrl: '/template/admin/user', controller: 'Admin/UserCtrl'});
            $routeProvider.when('/admin/user/edit/:id', {templateUrl: '/template/admin/user/crud', controller: 'Admin/User/CrudCtrl'});
            $routeProvider.when('/admin/user/new', {templateUrl: '/template/admin/user/crud', controller: 'Admin/User/CrudCtrl'});
            $routeProvider.when('/admin/rule', {templateUrl: '/template/admin/rule', controller: 'Admin/RuleCtrl'});
            $routeProvider.when('/admin/rule/edit/:id', {templateUrl: '/template/admin/rule/crud', controller: 'Admin/Rule/CrudCtrl'});
            $routeProvider.when('/admin/rule/new', {templateUrl: '/template/admin/rule/crud', controller: 'Admin/Rule/CrudCtrl'});
            $routeProvider.otherwise({redirectTo: '/home'});

            $locationProvider.html5Mode(true);


            RestangularProvider.setBaseUrl('/api');

            // Configure Restangular for our pagination structure
            RestangularProvider.setResponseExtractor(function(response, operation) {
                var newResponse;
                if (operation === "getList") {

                    // Here we're returning an Array which has one special property metadata with our extra information
                    newResponse = response.items;
                    newResponse.metadata = response.metadata;
                } else {
                    // This is an element
                    newResponse = response;
                }
                return newResponse;
            });

        });

// Here we declare all our modules, so we can get them back whenever we want
angular.module('myApp.filters', []);
angular.module('myApp.services', ['ngResource']);
angular.module('myApp.directives', []);
