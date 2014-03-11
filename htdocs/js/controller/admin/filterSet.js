/* Controllers */

angular.module('myApp').controller('Admin/FilterSet/CrudCtrl', function($scope, $location, $routeParams, Restangular) {
    "use strict";

    $scope.fields = {fields: 'filters,filters.paths,filters.children,filters.color,filter.genericColor'};
    $scope.params = {fields: 'paths,color,genericColor'};
    $scope.select2Template = "" +
        "<div>" +
        "<div class='col-sm-4 col-md-4 select-label select-label-with-icon'>" +
        "    <i class='fa fa-gims-filter' style='color:[[item.color]];' ></i> [[item.name]]" +
        "</div>" +
        "<div class='col-sm-7 col-md-7'>" +
        "    <small>[[console.info(item);]]" +
        "       [[_.map(item.paths, function(path){ return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]" +
        "    </small>" +
        "</div>" +
        "<div class='col-sm-1 col-md-1 hide-in-results' >" +
        "    <a class='btn btn-default btn-sm' href='/admin/filter/edit/[[item.id]][[$scope.currentContextElement]]'>" +
        "        <i class='fa fa-pencil'></i>" +
        "    </a>" +
        "</div>" +
        "<div class='clearfix'></div>" +
        "</div>";


    var redirectTo = '/admin/filter-set';
    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.saveAndClose = function() {
        this.save(redirectTo);
    };

    $scope.cancel = function() {
        $location.path(redirectTo).search('returnUrl', null).hash(null);
    };


    if ($routeParams.id) {
        Restangular.one('filter-set', $routeParams.id).get($scope.fields).then(function(filterSet) {
            $scope.filterSet = filterSet;
        });

    } else {
        $scope.filterSet = {};
    }


    $scope.save = function(redirectTo) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.filterSet.id) {
            $scope.filterSet.put({fields: 'filters'}).then(function(filterSet) {
                $scope.sending = false;
                $scope.filterSet = filterSet;
                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        }
        else {
            Restangular.all('filter-set').post($scope.filterSet).then(function(filterSet) {
                $scope.sending = false;
                if (!redirectTo) {
                    redirectTo = '/admin/filter-set/edit/' + filterSet.id;
                }
                $location.path(redirectTo);
            });
        }
    };

});






/**
 * Admin filterset Controller
 */
angular.module('myApp').controller('Admin/FilterSetCtrl', function($scope) {
    "use strict";

    // Configure gims-grid.
    $scope.gridOptions = {
        columnDefs: [
            {field: 'name', displayName: 'Name'},
            {displayName: '', width: '70px', cellTemplate: '' +
                        '<div class="btn-group">' +
                        '   <a class="btn btn-default btn-xs" href="/admin/filter-set/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>' +
                        '   <button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>' +
                        '</div>'
            }
        ]
    };

});