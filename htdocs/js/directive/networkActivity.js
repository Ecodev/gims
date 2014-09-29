/**
 * Directive which is shown only when there is some network activity (anywhere within GIMS)
 */
angular.module('myApp.directives').directive('gimsNetworkActivity', function(requestNotification, $log) {
    return {
        restrict: 'E',
        link: function(scope, element) {
            // hide the element initially
            element.hide();
            var startTime;

            //subscribe to listen when a request starts
            requestNotification.subscribeOnRequest(function() {
                // show the spinner!
                if (!startTime) {
                    startTime = Date.now();
                    element.show();
                }
            }, function() {
                // hide the spinner if there are no more pending requests
                if (requestNotification.getRequestCount() === 0) {
                    element.hide();

                    if (startTime) {
                        var endTime = Date.now();
                        $log.log("last rendering time [ms]: ", endTime - startTime);
                        startTime = null;
                    }
                }
            });
        }
    };
});
