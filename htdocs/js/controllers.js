'use strict';

/* Controllers */

function AlbumListCtrl($scope, Album, $log) {
  $scope.albums = Album.query();
}

//AlbumListCtrl.$inject = ['$scope', 'Album'];



function AlbumDetailCtrl($scope, $routeParams, Album, $log) {
  $scope.album = Album.get({albumId: $routeParams.albumId}, function(album) {
    $scope.album = album;
  });

  $scope.submit = function() {
	  $scope.album.$save();
  }
}

//AlbumDetailCtrl.$inject = ['$scope', '$routeParams', 'Album'];
