angular.module('myApp.directives').directive('gimsFilterGenerator', function($modal, $timeout) {
    return {
        restrict: 'E',
        template: "<span class='btn btn-default' ng-class=\"{'disabled':!part || !country}\" ng-click='openModal()'><i class='fa fa-bar-chart-o'></i> Add new dataset</span>",
        link: function(scope, element, attrs) {
            // nothing to do ?
        },
        controller: function($scope, Restangular, $http) {
            $scope.openModal = function() {
                var modalInstance = $modal.open({
                    controller: $scope.modalController,
                    resolve: {
                        line: function() {
                            return {};
                        },
                        Restangular: function() {
                            return Restangular;
                        },
                        part: function() {
                            return $scope.part;
                        },
                        country: function() {
                            return $scope.country;
                        }
                    },
                    template: "" +
                        "<div class='modal-header'>" +
                        "    <button type='button' class='close' ng-click='close()'>&times;</button>" +
                        "    <h3>Add custom filter</h3>" +
                        "    </div>" +
                        "<div class='modal-body row'>" +
                        '<form name="myForm" class="col-md-12">' +
                        '   <div class="form-group" ng-class="{\'has-error\': myForm.name.$invalid}">' +
                        '       <label class="control-label" for="line.name"><i class="fa fa-gims-filter"></i> Filter name</label>' +
                        '       <div class="row">'+
                        '           <div class="col-md-10">'+
                        '               <input id="line.name" type="text" name="name" ng-model="line.name" required ng-minlength="3" ng-disabled="line.lastLogin" />' +
                        '           </div>'+
                        '           <div class="col-md-2 text-right">'+
                        '               <div class=" btn btn-default" colorpicker ng-model="line.color" style="background-color:{{line.color}}"><i class="fa fa-magic"></i></div>'+
                        '           </div>'+
                        '       </div>'+
                        '       <span ng-show="myForm.name.$error.required" class="help-block">Required</span>' +
                        '       <span ng-show="myForm.name.$error.minlength" class="help-block">It must be at least 3 characters long</span>' +
                        '   </div>' +
                        '   <div class="gridStyle show-grid" ng-grid="gridOptions"></div>' +
                        '</form>' +
                        "</div>" +
                        "<div class='modal-footer'>" +
                        "   <a href='#' class='btn btn-default' ng-click='close()'>Cancel</a>" +
                        "   <a href='#' ng-disabled='myForm.$invalid || !line.surveys[0].year || !line.surveys[0].value || !part || !country' ng-click='generate()' class='btn btn-success'>Generate filter</a>" +
                        "</div>"
                });
            };

            $scope.modalController = function($scope, $modalInstance, $location, line, Restangular, part, country) {

                $scope.line = line;
                $scope.part = part;
                $scope.country = country;

                if (!$scope.line.surveys) {
                    $scope.line.surveys = [
                        {}
                    ];
                }

                $scope.$watch('line.surveys', function() {
                    $scope.addRow();
                }, true);

                $scope.generate = function() {

                    var surveys = _.map($scope.line.surveys, function(survey) {
                        if (survey.year && survey.value) {
                            return survey.year + ':' + (survey.value / 100);
                        }
                    });
                    surveys.pop();

                    $scope.isLoading = true;

                    $http.get('/api/chart/generateFilter', {
                        params: {
                            name: $scope.line.name,
                            part: part.id,
                            country: country.id,
                            surveys: surveys.join(',')
                        }
                    }).success(function(filterSet) {
                        console.log(filterSet);
                        $scope.isLoading = false;
                        var filterSets = $location.search()['filterSet'] ? $location.search()['filterSet'].split(',') :
                            [];
                        filterSets.push(filterSet.id);
                        $location.search('filterSet', filterSets.join(','));

                        $timeout(function() {
                            window.location.reload();
                        }, 0);
                    });

                }

                $scope.close = function() {
                    $modalInstance.close($scope.line);
                }

                $scope.deleteOption = function(index) {
                    $scope.line.surveys.splice(index, 1);
                }

                $scope.addRow = function() {
                    var lastIndex = $scope.line.surveys.length - 1;
                    if ($scope.line.surveys.length == 0 || $scope.line.surveys[lastIndex].year || $scope.line.surveys[lastIndex].value) {
                        $scope.line.surveys.push({});
                    }
                }

                $scope.gridOptions = {
                    plugins: [new ngGridFlexibleHeightPlugin({minHeight: 0})],
                    data: 'line.surveys',
                    enableCellSelection: true,
                    enableRowSelection: false,
                    enableCellEditOnFocus: true,
                    showFooter: false,
                    columnDefs: [
                        {
                            field: 'year',
                            displayName: 'Year',
                            editableCellTemplate: '<input type="number"  min="1980" max="3000" ng-class="\'colt\' + col.index" ng-input="COL_FIELD" ng-model="COL_FIELD" required/>'
                        },
                        {
                            field: 'value',
                            displayName: 'Percent value',
                            enableCellEdit: true,
                            editableCellTemplate: '<input type="number" max="100" ng-class="\'colt\' + col.index" ng-input="COL_FIELD" ng-model="COL_FIELD" required/> %',
                            cellTemplate: '<div class="ngCellText" ng-class="col.colIndex()"><span ng-cell-text>{{COL_FIELD}}</span> <span ng-show="COL_FIELD">%</span></div>'
                        },
                        {
                            field: '',
                            displayName: '',
                            width: '60px',
                            enableCellEdit: false,
                            cellTemplate: '<div style="padding:5px"><div ng-hide="row.rowIndex == line.surveys.length-1" class="btn btn-default btn-xs" ng-click="deleteOption(row.rowIndex)"><i class="fa fa-trash-o"></i></div></div>'
                        }
                    ]
                };
            }
        }
    }
});