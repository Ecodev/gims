'use strict';

/* Services */

/**
 * Admin Survey service
 */
angular.module('myApp.adminServices', [])
    .factory('Modal', function ($dialog, $location) {
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
                            object.$delete(params, function () {

                                // If we have an array, also remove from the array
                                if (options.objects) {

                                    angular.forEach(options.objects, function (o, i) {
                                        if (object.id == o.id) {
                                            options.objects.splice(i, 1); // remove from local storage
                                        }
                                    });
                                }

                                if (options.returnUrl) {
                                    $location.path(options.returnUrl);
                                }
                            });
                        }
                    });
            }
        };
    })
    .factory('Gui', function ($dialog, $location) {
        return {
            resetSaveButton: function (scope) {
                // Defining label for GUI.
                scope.sending = false;
            }
        };
    });