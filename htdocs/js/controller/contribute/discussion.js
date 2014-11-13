/**
 * Contribute Discussion Controller
 */
angular.module('myApp').controller('Contribute/DiscussionCtrl', function($scope, $routeParams, $location, DiscussionModal) {
    'use strict';

    // If discussion specified, open it
    if ($routeParams.id) {
        DiscussionModal.open({id: $routeParams.id}).finally(function() {
            $location.path('/contribute/discussion');
            $location.hash(null);

        });
    }

    $scope.queryparams = {fields: 'name,metadata,lastComment.metadata'};
    // Configure gims-grid.
    $scope.gridOptions = {
        columnDefs: [
            {field: 'name', displayName: 'Name'},
            {displayName: 'Last commenter', field: 'lastComment.creator.name', width: '250px'},
            {displayName: 'Last comment', width: '200px', field: 'lastComment.dateCreated', cellTemplate: '<div class="ngCellText"><span am-time-ago="row.entity.lastComment.dateCreated"></span></div>'},
            {displayName: '', width: '70px', cellTemplate: '<a class="btn btn-default btn-xs" href="/contribute/discussion/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>'}
        ]
    };

});
