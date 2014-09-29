/* Services
 *
 * Our service Modal.confirmDelete(), prompt the end-user whether he confirms
 * the deletion of an object and it returns immediately a promise of deletion.
 *
 * The promise is resolved if the object is deleted, and rejected otherwise.
 */
angular.module('myApp.services').factory('Modal', function($modal, $location, $q) {
    'use strict';

    var confirmDeleteInternal = function(object, options, deferred) {
        options = options || {};

        var msg;
        var label = options.label || object.code || object.name;
        if (label) {
            msg = 'Do you want to delete "' + label + '" ?';
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
                if (toSplice !== null) {
                    options.objects.splice(toSplice, 1);
                }

                if (options.returnUrl) {
                    $location.path(options.returnUrl);
                }

                // Object was delete, so we resolve our deletion promise
                deferred.resolve();
            });

        }, function() {
            // Deletion was rejected by end-user, so we reject our deletion promise
            deferred.reject();
        });
    };

    return {
        confirmDelete: function(object, options) {
            options = options || {};
            var deferred = $q.defer();
            $q.when(options.objects).then(function(data) {
                options.objects = data;
                confirmDeleteInternal(object, options, deferred);
            });

            return deferred.promise;
        }
    };
});
