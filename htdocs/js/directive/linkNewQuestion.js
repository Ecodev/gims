/* Directives */
angular.module('myApp.directives')
    .directive('linkNewQuestion', function ($compile, $routeParams) {
        return {
            restrict: "E",
            replace: true,
            link: function (scope, element) {
                var html = sprintf('<a class="link-new" href="/admin/question/new?returnUrl=/admin/survey/edit/%s&survey=%s">new question</a>',
                    $routeParams.id,
                    $routeParams.id
                );
                element.html(html).show();
                $compile(element.contents())(scope);
            },
            scope: {
                content: '='
            }
        };
    });