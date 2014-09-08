/**
 * Service similar to $modal to invoke dropdown menu instances.
 * Template and controller will be initiatized on invokation, making
 * this service a good choice for heavy pages where the menu would otherwise
 * be repeated needlessly.
 */
angular.module('myApp.services').provider('$dropdown', function() {

    var $dropdownProvider = {
        $get: ['$injector', '$rootScope', '$q', '$http', '$templateCache', '$controller', '$document', '$compile',
            function($injector, $rootScope, $q, $http, $templateCache, $controller, $document, $compile) {

                function getTemplatePromise(options) {
                    return options.template ? $q.when(options.template) :
                            $http.get(options.templateUrl, {cache: $templateCache}).then(function(result) {
                        return result.data;
                    });
                }

                function getResolvePromises(resolves) {
                    var promisesArr = [];
                    angular.forEach(resolves, function(value, key) {
                        if (angular.isFunction(value) || angular.isArray(value)) {
                            promisesArr.push($q.when($injector.invoke(value)));
                        }
                    });
                    return promisesArr;
                }

                var $dropdown = {};
                $dropdown.open = function(dropdownOptions) {

                    var resultDeferred = $q.defer();
                    var openedDeferred = $q.defer();

                    //prepare an instance of a dropdown to be injected into controllers and returned to a caller
                    var dropdownInstance = {
                        result: resultDeferred.promise,
                        opened: openedDeferred.promise
                    };

                    //merge and clean up options
                    dropdownOptions.resolve = dropdownOptions.resolve || {};

                    //verify options
                    if (!dropdownOptions.template && !dropdownOptions.templateUrl) {
                        throw new Error('One of template or templateUrl options is required.');
                    }

                    var templateAndResolvePromise = $q.all([getTemplatePromise(dropdownOptions)].concat(getResolvePromises(dropdownOptions.resolve)));


                    templateAndResolvePromise.then(function resolveSuccess(tplAndVars) {

                        var dropdownScope = (dropdownOptions.scope || $rootScope).$new();


                        var ctrlInstance, ctrlLocals = {};
                        var resolveIter = 1;

                        //controllers
                        if (dropdownOptions.controller) {
                            ctrlLocals.$scope = dropdownScope;
                            angular.forEach(dropdownOptions.resolve, function(value, key) {
                                ctrlLocals[key] = tplAndVars[resolveIter++];
                            });

                            ctrlInstance = $controller(dropdownOptions.controller, ctrlLocals);
                        }

                        // Create and append element
                        var dopdownDomElement = $compile(tplAndVars[0])(dropdownScope);
                        dropdownOptions.button.after(dopdownDomElement);

                        // Prepare function to close dropdown
                        dropdownInstance.close = function(result) {
                            dropdownScope.$destroy();
                            dopdownDomElement.remove();
                            resultDeferred.resolve(result);
                        };

                        // Expose function on scope of dropdown
                        dropdownScope.$close = dropdownInstance.close;

                        // Register event to close dropdown when clicking anything or ESC
                        $document.one('click', dropdownInstance.close);
                        $document.bind('keydown', function(event) {
                            if (event.which === 27) {
                                event.preventDefault();
                                dropdownInstance.close();
                            }
                        });

                    }, function resolveError(reason) {
                        resultDeferred.reject(reason);
                    });

                    templateAndResolvePromise.then(function() {
                        openedDeferred.resolve(true);
                    }, function() {
                        openedDeferred.reject(false);
                    });

                    return dropdownInstance;
                };

                return $dropdown;
            }]
    };

    return $dropdownProvider;
});
