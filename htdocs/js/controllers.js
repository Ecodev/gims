'use strict';

/* Controllers */

function AlbumListCtrl($scope, Album, $log) {
//  $scope.albums = Album.query();
  $scope.albums = Album.query();
  $scope.orderProp = 'age';
}

//AlbumListCtrl.$inject = ['$scope', 'Album'];



function AlbumDetailCtrl($scope, $routeParams, Album) {
  $scope.album = Album.get({albumId: $routeParams.albumId}, function(album) {
    $scope.album = album;
  });

  $scope.setImage = function(imageUrl) {
    $scope.mainImageUrl = imageUrl;
  }
}

//AlbumDetailCtrl.$inject = ['$scope', '$routeParams', 'Album'];
