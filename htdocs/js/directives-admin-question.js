/* Directives */
angular.module('myApp.adminQuestionDirectives', [])
    .directive('linkNewQuestion', function ($compile, $routeParams) {
        return {
            restrict: "E",
            rep1ace: true,
            link: function (scope, element) {
                var html = sprintf('<a href="/admin/question/new?returnUrl=/admin/survey/edit/%s&survey=%s">new question</a>',
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