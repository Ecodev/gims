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
            {displayName: 'Last commenter', field: 'lastComment.creator.name', width: 250},
            {displayName: 'Last comment', width: 200, field: 'lastComment.dateCreated', cellTemplate: '<div class="ui-grid-cell-contents"><span am-time-ago="row.entity.lastComment.dateCreated"></span></div>'},
            {name: 'buttons', displayName: '', width: 70, cellTemplate: '<a class="btn btn-default btn-xs" href="/contribute/discussion/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>'}
        ]
    };

});
