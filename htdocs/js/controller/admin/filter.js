/* Controllers */

angular.module('myApp').controller('Admin/Filter/CrudCtrl', function ($scope, $location, $routeParams, Modal, Restangular) {
    "use strict";

    $scope.fields = {fields:'children,children.paths,children.color,parents,parents.paths,parents.color,summands,summands.paths,summands.color,paths,color'};
    $scope.params = {fields:'paths,color,genericColor'};

    $scope.select2Template = "" +
        "<div>" +
            "<div class='col-sm-4 col-md-4 select-label select-label-with-icon'>"+
            "    <i class='fa fa-gims-filter' style='color:[[item.color]];' ></i> [[item.name]]"+
            "</div>"+
            "<div class='col-sm-7 col-md-7'>"+
            "    <small>"+
            "       [[_.map(item.paths, function(path){return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]"+
            "    </small>"+
            "</div>"+
            "<div class='col-sm-1 col-md-1 hide-in-results' >"+
            "    <a class='btn btn-default btn-sm' href='/admin/filter/edit/[[item.id]][[$scope.currentContextElement]]'>"+
            "        <i class='fa fa-pencil'></i>"+
            "    </a>"+
            "</div>"+
            "<div class='clearfix'></div>"+
        "</div>";


    var redirectTo = '/admin/filter';
    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.saveAndClose = function () {
        this.save(redirectTo);
    };

    $scope.cancel = function () {
        $location.path(redirectTo).search('returnUrl', null).hash(null);
    };


    if ($routeParams.id) {
        Restangular.one('filter', $routeParams.id).get($scope.fields).then(function (filter) {
            $scope.filter = filter;
        });

    } else {
        $scope.filter = {};
    }


    $scope.save = function (redirectTo) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.filter.id) {
            $scope.filter.put($scope.fields).then(function (filter) {
                $scope.sending = false;
                console.log(filter);
                $scope.filter = filter;
                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        }
        else {
            Restangular.all('filter').post($scope.filter).then(function (filter) {
                $scope.sending = false;
                if (!redirectTo) {
                    redirectTo = '/admin/filter/edit/' + filter.id;
                }
                $location.path(redirectTo);
            });
        }
    };

});



/**
 * Admin filter Controller
 */
angular.module('myApp').controller('Admin/FilterCtrl', function ($scope, $location, Modal, Restangular) {
    "use strict";

    // Initialize
    $scope.filters = Restangular.all('filter').getList({fields:'color'});
    $scope.params = {fields:'paths'};

    $scope.select2Template = "" +
        "<div>" +
            "<div class='col-sm-4 col-md-4 select-label select-label-with-icon'>"+
            "    <i class='fa fa-gims-filter'></i> [[item.name]]"+
            "</div>"+
            "<div class='col-sm-7 col-md-7'>"+
            "    <small>"+
            "       [[_.map(item.paths, function(path){return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]"+
            "    </small>"+
            "</div>"+
            "<div class='col-sm-1 col-md-1 hide-in-results' >"+
            "    <a class='btn btn-default btn-sm' href='/admin/filter/edit/[[item.id]]'>"+
            "        <i class='fa fa-pencil'></i>"+
            "    </a>"+
            "</div>"+
            "<div class='clearfix'></div>"+
        "</div>";

    // Keep track of the selected row.
    $scope.selectedRow = [];

    $scope.$watch('selectedFilter', function(selectedFilter)
    {
        if(selectedFilter) {
            $location.path('/admin/filter/edit/'+selectedFilter.id);
        }
    });

    // Configure ng-grid.
    $scope.gridOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 800})],
        data: 'filters',
        enableCellSelection: true,
        showFooter: false,
        selectedItems: $scope.selectedRow,
        filterOptions: {},
        multiSelect: false,
        columnDefs: [
            {
                field: 'name',
                displayName: 'Name',
                cellTemplate:   ''+
                    '<div class="ngCellText" ng-class="col.colIndex()">' +
                    '   <span style="padding-left: {{row.entity.level * 2}}em;">' +
                    '       <span style="display:inline-block;vertical-align:middle;width:5px;height:18px;background:{{row.entity.color}}"></span>'+
                    '       <span style="display:inline-block;vertical-align:middle;">{{row.entity.name}}</span>'+
                    '   </span>' +
                    '</div>'
            },
            {
                displayName: '',
                width: '70px',
                cellTemplate: '' +
                    '<div class="btn-group" style="margin:4px 0 0 4px;">'+
                    '   <a class="btn btn-default btn-xs" href="/admin/filter/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>'+
                    '   <button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'+
                    '</div>'
            }
        ]
    };

    // <button type="button" class="btn btn-default btn-xs" ng-click="edit(row)" ><i class="fa fa-pencil fa-lg"></i></button>
    $scope.remove = function (row) {
        Modal.confirmDelete(row.entity, {objects: $scope.filters, label: row.entity.code, returnUrl: '/admin/filter'});
    };

    $scope.edit = function (row) {
        $location.path('/admin/filter-set/edit/' + row.entity.id);
    };

});