'use strict';

/* Services */

angular.module('albumcatServices', ['ngResource']).
	factory('Album', function($resource){
		return $resource('album-rest/:albumId');
	});
