angular.module('myApp.directives').directive('gimsAddUser', function () {
    return {
        restrict: 'E',
        template: "<button class='btn' ng-click='openModal()'><i class='icon-user'></i> Add new user</button>"+
                  "<div modal='showModal' class='modal'>"+
                  "      <div class='modal-header'>"+
                  "          <button type='button' class='close' ng-click='showModal=false'>&times;</button>"+
                  "          <h3>Manage users</h3>"+
                  "      </div>"+
                  "      <div class='modal-body'>"+
                  "          <h3>Add user</h3>"+
                  "          <div class='row-fluid'>" +

                      '<form novalidate name="myForm">'+
                      '   <div class="control-group" ng-class="{error: myForm.name.$invalid}">'+
                      '       <label for="user.name">Name</label>'+
                      '       <input id="user.name" type="text" name="name" ng-model="user.name" required class="span6" ng-minlength="3"/>'+
                      '       <span ng-show="myForm.name.$error.required" class="help-inline">Required</span>'+
                      '       <span ng-show="myForm.name.$error.minlength" class="help-inline">It must be at least 3 characters long</span>'+
                      '   </div>'+
                      '   <div class="control-group" ng-class="{error: myForm.email.$invalid}">'+
                      '       <label for="user.email">Email</label>'+
                      '       <input id="user.email" type="email" name="email" ng-model="user.email" required class="span6"  />'+
                      '       <span ng-show="myForm.email.$error.required" class="help-inline">Required</span>'+
                      '   </div>'+
                      '   <div class="control-group" ng-class="{error: myForm.password.$invalid || myForm.passwordVerify.$invalid}">'+
                      '       <label for="user.password">Password</label>'+
                      '       <input id="user.password" name="password" type="password" ng-model="user.password" placeholder="password" class="span6" ng-minlength="6">'+
                      '       <span ng-show="myForm.password.$error.required" class="help-inline">Required</span>'+
                      '       <span ng-show="myForm.password.$error.minlength" class="help-inline">It must be at least 6 characters long</span>'+
                      '       <br>'+
                      '       <input name="passwordVerify" type="password" ng-model="user.passwordVerify" ui-validate=" \'$value==user.password\' " ui-validate-watch=" \'user.password\' " placeholder="confirm password"  class="span6">'+
                      '       <span ng-show="myForm.passwordVerify.$error.validator" class="help-inline">Passwords do not match!</span>'+
                      '   </div>'+
                      '</form>'+

                  "          </div>"+
                  "      </div>"+
                  "      <div class='modal-footer'>"+
                  "          <a href='#' class='btn' ng-click='showModal=false'>Cancel</a>"+
                  "          <a href='#' ng-disabled='myForm.$invalid || myForm.passwordVerify.$error.validator'  ng-click='addUser()' class='btn btn-success'>Add user</a>"+
                  "      </div>"+
                  "  </div>"+
                  "",
        scope : {
            callback : '&'
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.showModal = false;

            $scope.openModal = function() {
                $scope.showModal = true;
                if($scope.user){
                    $scope.user.name = '';
                    $scope.user.email = '';
                    $scope.user.password = '';
                    $scope.user.passwordVerify = '';
                }
            }

            $scope.addUser = function() {
                if(!$scope.myForm.$invalid || $scope.myForm.passwordVerify.$error.validator) {
                    Restangular.all('user').post($scope.user).then(function($data){
                        console.info($data);
                        $scope.showModal = false;
                        $scope.callback();
                    })
                }
            }

        }
    }
});
