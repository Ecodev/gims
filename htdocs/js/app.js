// Declare app level module which depends on filters, and services
angular.module('myApp', [
    'ngRoute',
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
    'ui.ace',
    'angulartics',
    'angulartics.google.analytics',
    'ui.sortable',
    'vs-repeat',
    'ui.slider',
    'angularMoment'
]).
        config(function($routeProvider, $locationProvider, RestangularProvider, $httpProvider, requestNotificationProvider, datepickerPopupConfig) {
            'use strict';

            $routeProvider.when('/home', {templateUrl: '/template/application/index/home'});
            $routeProvider.when('/about', {templateUrl: '/template/application/index/about'});
            $routeProvider.when('/browse', {templateUrl: '/template/browse'});
            $routeProvider.when('/browse/chart', {templateUrl: '/template/browse/chart', controller: 'Browse/ChartCtrl', reloadOnSearch: false});
            $routeProvider.when('/browse/table/questionnaire', {templateUrl: '/template/browse/table/questionnaire', controller: 'Browse/Table/QuestionnaireCtrl', reloadOnSearch: false});
            $routeProvider.when('/browse/table/country', {templateUrl: '/template/browse/table/country', controller: 'Browse/Table/CountryCtrl', reloadOnSearch: false});
            $routeProvider.when('/contribute', {templateUrl: '/template/contribute', controller: 'ContributeCtrl'});
            $routeProvider.when('/admin', {templateUrl: '/template/admin'});
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
            $routeProvider.when('/browse/table/filter', {templateUrl: '/template/browse/table/filter', controller: 'Browse/FilterCtrl', reloadOnSearch: false});
            $routeProvider.when('/contribute/jmp', {templateUrl: '/template/browse/table/filter', controller: 'Browse/FilterCtrl', reloadOnSearch: false});
            $routeProvider.when('/contribute/nsa', {templateUrl: '/template/browse/table/filter', controller: 'Browse/FilterCtrl', reloadOnSearch: false});
            $routeProvider.when('/contribute/glaas/', {templateUrl: '/template/contribute/glaas', controller: 'Contribute/GlaasCtrl'});
            $routeProvider.when('/contribute/glaas/:id', {templateUrl: '/template/contribute/glaas', controller: 'Contribute/GlaasCtrl'});
            $routeProvider.when('/contribute/request-roles', {templateUrl: '/template/contribute/request-roles/request-roles', controller: 'Contribute/RequestRolesCtrl', reloadOnSearch: false});
            $routeProvider.when('/admin/roles-requests', {templateUrl: '/template/admin/roles-requests/roles-requests', controller: 'Admin/RolesRequestsCtrl'});

            $routeProvider.otherwise({redirectTo: '/home'});

            // general config
            $locationProvider.html5Mode(true);
            RestangularProvider.setBaseUrl('/api');

            // Configure Restangular for our pagination structure
            RestangularProvider.setResponseExtractor(function(response, operation) {
                var newResponse;
                if (operation === "getList" && !_.isUndefined(response.metadata)) {

                    // Here we're returning an Array which has one special property metadata with our extra information
                    newResponse = response.items;
                    newResponse.metadata = response.metadata;
                } else {
                    // This is an element
                    newResponse = response;
                }
                return newResponse;
            });

            // Configure requestNotificationProvider
            $httpProvider.defaults.transformRequest.push(function(data) {
                requestNotificationProvider.fireRequestStarted();
                return data;
            });
            $httpProvider.defaults.transformResponse.push(function(data) {
                requestNotificationProvider.fireRequestEnded();
                return data;
            });
            $httpProvider.interceptors.push(function($q) {
                return {
                    responseError: function(rejection) {

                        // If there error did not happen because we specifically asked for validation, then fire the event
                        if (!_.has(rejection.config.params, 'validate')) {
                            requestNotificationProvider.fireResponseError(rejection);
                        }

                        return $q.reject(rejection);
                    }
                };
            });

            datepickerPopupConfig.datepickerPopup = 'yyyy-MM-dd';
        });

// Here we declare all our modules, so we can get them back whenever we want
angular.module('myApp.filters', []);
angular.module('myApp.services', []);
angular.module('myApp.directives', []);
