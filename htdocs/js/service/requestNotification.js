/**
 * Provide a service to monitor network activity
 */
angular.module('myApp.services').provider('requestNotification', function() {

    //This will be returned as a service
    this.$get = function($timeout) {

        // This is where we keep subscribed listeners
        var onRequestStartedListeners = [];
        var onRequestEndedListeners = [];
        var onResponseErrorListeners = [];

        // This is a utility to easily increment the request count
        var count = 0;
        var requestCounter = {
            increment: function() {
                count++;
            },
            decrement: function() {
                if (count > 0) {
                    count--;
                }
            },
            getCount: function() {
                return count;
            }
        };

        // Tell the provider, that the request has started.
        this.fireRequestStarted = function() {

            requestCounter.increment();
            angular.forEach(onRequestStartedListeners, function(listener) {
                listener();
            });
        };

        this.fireRequestEnded = function() {
            $timeout(function() {
                requestCounter.decrement();
                angular.forEach(onRequestEndedListeners, function(listener) {
                    listener();
                });
            }, 50); // Wait a short time to give a chance to code to start another request without flickering loading state
        };

        this.fireResponseError = function(response) {
            angular.forEach(onResponseErrorListeners, function(listener) {
                listener(response);
            });
        };

        // Subscribe to be notified when request starts and ends
        function subscribeOnRequest(listenerStarted, listenerEnded) {
            onRequestStartedListeners.push(listenerStarted);
            onRequestEndedListeners.push(listenerEnded);
        }

        // Subscribe to be notified when request errors
        function subscribeOnResponseError(listener) {
            onResponseErrorListeners.push(listener);
        }

        // Expose service's public function
        return {
            subscribeOnRequest: subscribeOnRequest,
            subscribeOnResponseError: subscribeOnResponseError,
            getRequestCount: requestCounter.getCount
        };
    };
});
