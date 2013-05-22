
/* Services */

/**
 * Admin Survey service
 */
angular.module('myApp.services')
    .factory('Modal', function ($dialog, $location) {
        'use strict';
        return {
            confirmDelete: function (object, options) {
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
            }
        };
    });