
/* Services */

/**
 * Admin Survey service
 */
angular.module('myApp.services')
    .factory('Modal', function ($dialog, $location) {
        'use strict';

        var confirmDeleteInternal = function (object, options) {
            options = options || {};

            var msg, title, buttons;
            if (options.label) {
                msg = 'Do you want to delete "' + options.label + '" ?';
            } else {
                msg = 'Do you want to delete it ?';
            }
            title = 'Confirm deletion';
            buttons = [
                {result: 'cancel', label: 'Cancel'},
                {result: 'delete', label: 'Delete', cssClass: 'btn-danger'}
            ];

            $dialog.messageBox(title, msg, buttons)
                .open()
                .then(function (result) {
                    if (result === 'delete') {
                        var params = options.params || {};
                        params.id = object.id;

                        // If we have an array, look for the element before we delete it
                        var toSplice = null;
                        if (options.objects) {
                            angular.forEach(options.objects, function (o, i) {
                                if (object.id === o.id) {
                                    toSplice = i;
                                }
                            });
                        }

                        object.$delete(params, function () {

                            // remove from local storage
                            if (toSplice != null) {
                                options.objects.splice(toSplice, 1);
                            }

                            if (options.returnUrl) {
                                $location.path(options.returnUrl);
                            }
                        });
                    }
                });
        };

        return {
            confirmDelete: function (object, options) {

                // If we detect objects is a promise, wait for the promise to be completed and then show dialog
                if (options.objects && typeof options.objects.then == 'function') {
                    options.objects.then(function(data){
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