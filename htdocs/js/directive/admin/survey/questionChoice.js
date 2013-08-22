angular.module('myApp.directives').directive('gimsChoiQuestion', function () {
    return {
        restrict: 'E',
        template:   "<div class='row-fluid' ng-repeat='choice in question.choices'>"+
                    "   <div class='span1 text-center' ng-repeat='part in question.parts'>"+
                    "       <input type='radio' name='{{part}}-{{question.id}}'/>"+
                    "   </div>"+
                    "   <span class='span9'>"+
                    "       {{choice.label}}"+
                    "   </span>"+
                    "</div>"+
                    "<div class='row-fluid'>"+
                    "   <div class='span1 text-center' ng-repeat='part in question.parts'>"+
                    "       {{part.name}}"+
                    "   </div>"+
                    "</div>",
        scope:{
            question:'='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.$watch('question', function(question) {

            });
        }
    }
});
