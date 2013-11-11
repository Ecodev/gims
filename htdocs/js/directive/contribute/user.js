angular.module('myApp.directives').directive('gimsAddUser', function () {
    return {
        restrict: 'E',
        template: ""+
                  "<div modal='showModal' class='modal'>"+
                  "      <div class='modal-header'>"+
                  "          <button type='button' class='close' ng-click='showModal=false'>&times;</button>"+
                  "          <h3>Manage user</h3>"+
                  "      </div>"+
                  "      <div class='modal-body'>"+
                  "          <div class='row-fluid'>" +

                  '<form novalidate name="myForm">'+
                  '   <div class="control-group" ng-class="{error: myForm.name.$invalid}">'+
                  '       <label for="localUser.name">Name (first name and last name)</label>'+
                  '       <input id="localUser.name" type="text" name="name" ng-model="localUser.name" required ng-minlength="3" class="span8" ng-disabled="localUser.lastLogin" />'+
                  '       <span ng-show="myForm.name.$error.required" class="help-inline">Required</span>'+
                  '       <span ng-show="myForm.name.$error.minlength" class="help-inline">It must be at least 3 characters long</span>'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.email.$invalid}">'+
                  '       <label for="localUser.email">Email</label>'+
                  '       <input id="localUser.email" type="email" name="email" ng-model="localUser.email" required class="span8" ng-disabled="localUser.lastLogin" />'+
                  '       <span ng-show="myForm.email.$error.required" class="help-inline">Required</span>'+
                  '   </div>'+

                  '   <div class="control-group" ng-class="{error: myForm.phone.$invalid}">'+
                  '       <label for="localUser.phone">Phone</label>'+
                  '       <input id="localUser.phone" type="text" name="phone" ng-model="localUser.phone" class="span8" ng-disabled="localUser.lastLogin" />'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.skype.$invalid}">'+
                  '       <label for="localUser.skype">Skype</label>'+
                  '       <input id="localUser.skype" type="text" name="skype" ng-model="localUser.skype" class="span8" ng-disabled="localUser.lastLogin" />'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.job.$invalid}">'+
                  '       <label for="localUser.job">Job title</label>'+
                  '       <input id="localUser.job" type="text" name="job" ng-model="localUser.job" class="span8" ng-disabled="localUser.lastLogin" />'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.ministry.$invalid}">'+
                  '       <label for="localUser.ministry">Ministry / Departement</label>'+
                  '       <input id="localUser.ministry" type="text" name="ministry" ng-model="localUser.ministry" class="span8" ng-disabled="localUser.lastLogin" />'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.address.$invalid}">'+
                  '       <label for="localUser.address">Address</label>'+
                  '       <textarea id="localUser.address" type="text" name="address" ng-model="localUser.address" class="span8" ng-disabled="localUser.lastLogin" ></textarea>'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.zip.$invalid}">'+
                  '       <label for="localUser.zip">ZIP</label>'+
                  '       <input id="localUser.zip" type="text" name="zip" ng-model="localUser.zip" class="span8" ng-disabled="localUser.lastLogin" />'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.city.$invalid}">'+
                  '       <label for="localUser.city">City</label>'+
                  '       <input id="localUser.city" type="text" name="city" ng-model="localUser.city" class="span8" ng-disabled="localUser.lastLogin" />'+
                  '   </div>'+
                  '   <div class="control-group" ng-class="{error: myForm.country.$invalid}">'+
                  '       <label for="country">Country</label>'+
                  '       <gims-select id="country" api="country" name="country" model="localUser.country" style="width:66%" disabled="localUser.lastLogin" ></gims-select>'+
                  '   </div>'+
                  '</form>'+

                  "          </div>"+
                  "      </div>"+
                  "      <div class='modal-footer'>"+
                  "          <a href='#' class='btn' ng-click='showModal=false'>Cancel</a>"+
                  "          <a href='#' ng-disabled='myForm.$invalid' ng-show='!localUser || !localUser.id' ng-click='addUser()' class='btn btn-success'>Add new user</a>"+
                  "          <a href='#' ng-disabled='myForm.$invalid' ng-show='localUser && localUser.id && !localUser.lastLogin'  ng-click='saveUser()' class='btn btn-success'>Save user</a>"+
                  "      </div>"+
                  "  </div>"+
                  "",
        scope : {
            user : '=',
            showModal : '='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.localUser = {};
            $scope.userQueryParams = {fields : 'phone,skype,job,ministry,address,zip,city,country'};

            $scope.$watch('showModal', function(newShow){
                if (newShow) {
                    $scope.show();
                } else {
                    $scope.hide();
                }
            });

            $scope.show = function()
            {
                if ($scope.user && $scope.user.id) {
                    Restangular.one('user', $scope.user.id).get($scope.userQueryParams).then(function(user){
                        $scope.localUser = user;
                    });
                } else {
                    $scope.localUser = {};
                }
            }

            $scope.hide = function() {
                $scope.localUser = {};
            }

            $scope.addUser = function() {
                if(!$scope.myForm.$invalid) {
                    Restangular.all('user').post($scope.localUser).then(function(user){
                        $scope.user = user;
                        $scope.showModal = false;
                    })
                }
            }

            $scope.saveUser = function() {
                if(!$scope.myForm.$invalid) {
                    var localUser = Restangular.restangularizeElement(null, $scope.localUser, 'user');
                    localUser.put($scope.userQueryParams).then(function(user){
                        $scope.user = user;
                        $scope.showModal = false;
                    });
                }
            }
        }
    }
});
