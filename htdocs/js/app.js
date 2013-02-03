'use strict';

/* App Module */

angular.module('albumcat', ['albumcatFilters', 'albumcatServices']).
  config(['$routeProvider', function($routeProvider) {
  $routeProvider.
      when('/album', {templateUrl: '/album/index',   controller: AlbumListCtrl}).
      when('/album/:albumId', {templateUrl: '/album/edit', controller: AlbumDetailCtrl})
  .
      otherwise({redirectTo: '/album'});
}]);
