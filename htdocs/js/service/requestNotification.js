/**
 * Provide a service to monitor network activity
 */
angular.module('myApp.services').provider('requestNotification', function() {

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

    // Subscribe to be notified when request starts
    this.subscribeOnRequestStarted = function(listener) {
        onRequestStartedListeners.push(listener);
    };

    // Tell the provider, that the request has started.
    this.fireRequestStarted = function() {
        requestCounter.increment();
        angular.forEach(onRequestStartedListeners, function(listener) {
            listener();
        });
    };

    // this is a complete analogy to the Request START
    this.subscribeOnRequestEnded = function(listener) {
        onRequestEndedListeners.push(listener);
    };

    this.fireRequestEnded = function() {
        requestCounter.decrement();
        angular.forEach(onRequestEndedListeners, function(listener) {
            listener();
        });
    };

    // this is a complete analogy to the Request START
    this.subscribeOnResponseError = function(listener) {
        onResponseErrorListeners.push(listener);
    };
    this.fireResponseError = function(response) {
        angular.forEach(onResponseErrorListeners, function(listener) {
            listener(response);
        });
    };

    //This will be returned as a service
    this.$get = function() {
        var that = this;
        // just pass all the
        return {
            subscribeOnRequestStarted: that.subscribeOnRequestStarted,
            subscribeOnRequestEnded: that.subscribeOnRequestEnded,
            subscribeOnResponseError: that.subscribeOnResponseError,
            fireRequestEnded: that.fireRequestEnded,
            fireRequestStarted: that.fireRequestStarted,
            fireResponseError: that.fireResponseError,
            getRequestCount: requestCounter.getCount
        };
    };
});

