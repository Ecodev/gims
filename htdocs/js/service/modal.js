/* Services */
angular.module('myApp.services')
        .factory('Modal', function($modal, $location) {
            'use strict';

            var confirmDeleteInternal = function(object, options) {
                options = options || {};

                var msg;
                if (options.label) {
                    msg = 'Do you want to delete "' + options.label + '" ?';
                } else {
                    msg = 'Do you want to delete it ?';
                }

                var modalInstance = $modal.open({
                    controller: 'SimpleModalCtrl',
                    template: '<div class="modal-header">' +
                            '   <h3 class="modal-title">Confirm deletion</h3>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            '   <p>' + msg + '</p>' +
                            '</div>' +
                            '<div class="modal-footer">' +
                            '   <button type="button" class="btn btn-default"ng-click="$dismiss()">Cancel</button>' +
                            '   <button type="button" class="btn btn-danger" ng-click="$close()">Delete</button>' +
                            '</div>'

                });

                modalInstance.result.then(function() {

                    var params = options.params || {};
                    params.id = object.id;

                    // If we have an array, look for the element before we delete it
                    var toSplice = null;
                    if (options.objects) {
                        angular.forEach(options.objects, function(o, i) {
                            if (object.id === o.id) {
                                toSplice = i;
                            }
                        });
                    }

                    object.remove(params).then(function() {

                        // remove from local storage
                        if (toSplice != null) {
                            options.objects.splice(toSplice, 1);
                        }

                        if (options.returnUrl) {
                            $location.path(options.returnUrl);
                        }
                    });
                });
            };

            return {
                confirmDelete: function(object, options) {

                    // If we detect objects is a promise, wait for the promise to be completed and then show dialog
                    if (options.objects && typeof options.objects.then == 'function') {
                        options.objects.then(function(data) {
                            options.objects = data;
                            confirmDeleteInternal(object, options);
                        });
                    }
                    // Otherwise show dialog directly
                    else {
                        confirmDeleteInternal(object, options);
                    }
                }
            };
        });