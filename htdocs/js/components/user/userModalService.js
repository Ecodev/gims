/* Services */
angular.module('myApp.services').factory('UserModal', function($modal) {
    'use strict';
    var editUser = function(user) {

        var modalInstance = $modal.open({
            controller: 'UserModalCtrl',
            resolve: {
                user: function() {
                    return user;
                }
            },
            template: "<div class='modal-header'>" +
                    "    <button type='button' class='close' ng-click='$dismiss()'>&times;</button>" +
                    "    <h3>Manage user</h3>" +
                    "    </div>" +
                    "<div class='modal-body'>" +
                    '<form name="myForm">' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.name.$invalid}">' +
                    '       <label class="control-label" for="localUser.name">Name (first name and last name)</label>' +
                    '       <input id="localUser.name" type="text" name="name" ng-model="localUser.name" required ng-minlength="3" ng-disabled="localUser.lastLogin" />' +
                    '       <span ng-show="myForm.name.$error.required" class="help-block">Required</span>' +
                    '       <span ng-show="myForm.name.$error.minlength" class="help-block">It must be at least 3 characters long</span>' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.email.$invalid}">' +
                    '       <label class="control-label" for="localUser.email">Email</label>' +
                    '       <input id="localUser.email" type="email" name="email" ng-model="localUser.email" required ng-disabled="localUser.lastLogin" />' +
                    '       <span ng-show="myForm.email.$error.required" class="help-block">Required</span>' +
                    '       <span ng-show="myForm.email.$error.email" class="help-block">Enter a valid email address</span>' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.phone.$invalid}">' +
                    '       <label class="control-label" for="localUser.phone">Phone</label>' +
                    '       <input id="localUser.phone" type="text" name="phone" ng-model="localUser.phone" ng-disabled="localUser.lastLogin" />' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.skype.$invalid}">' +
                    '       <label class="control-label" for="localUser.skype">Skype</label>' +
                    '       <input id="localUser.skype" type="text" name="skype" ng-model="localUser.skype" ng-disabled="localUser.lastLogin" />' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.job.$invalid}">' +
                    '       <label class="control-label" for="localUser.job">Job title</label>' +
                    '       <input id="localUser.job" type="text" name="job" ng-model="localUser.job" ng-disabled="localUser.lastLogin" />' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.ministry.$invalid}">' +
                    '       <label class="control-label" for="localUser.ministry">Ministry / Departement</label>' +
                    '       <input id="localUser.ministry" type="text" name="ministry" ng-model="localUser.ministry" ng-disabled="localUser.lastLogin" />' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.address.$invalid}">' +
                    '       <label class="control-label" for="localUser.address">Address</label>' +
                    '       <textarea id="localUser.address" type="text" name="address" ng-model="localUser.address" ng-disabled="localUser.lastLogin" ></textarea>' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.zip.$invalid}">' +
                    '       <label class="control-label" for="localUser.zip">ZIP</label>' +
                    '       <input id="localUser.zip" type="text" name="zip" ng-model="localUser.zip" ng-disabled="localUser.lastLogin" />' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.city.$invalid}">' +
                    '       <label class="control-label" for="localUser.city">City</label>' +
                    '       <input id="localUser.city" type="text" name="city" ng-model="localUser.city" ng-disabled="localUser.lastLogin" />' +
                    '   </div>' +
                    '   <div class="form-group" ng-class="{\'has-error\': myForm.geoname.$invalid}">' +
                    '       <label class="control-label" for="geoname">Country</label>' +
                    '       <gims-select id="geoname" api="geoname" name="geoname" model="localUser.geoname" style="width:100%" disabled="localUser.lastLogin" ></gims-select>' +
                    '   </div>' +
                    '</form>' +
                    "</div>" +
                    "<div class='modal-footer'>" +
                    "    <a href='#' class='btn btn-default' ng-click='$dismiss()'>Cancel</a>" +
                    "    <a href='#' ng-disabled='myForm.$invalid' ng-show='!localUser || !localUser.id' ng-click='addUser()' class='btn btn-success'>Create</a>" +
                    "    <a href='#' ng-disabled='myForm.$invalid' ng-show='localUser && localUser.id && !localUser.lastLogin'  ng-click='saveUser()' class='btn btn-success'>Save</a>" +
                    "</div>"
        });
        return modalInstance.result;
    };
    return {
        editUser: editUser
    };
});
