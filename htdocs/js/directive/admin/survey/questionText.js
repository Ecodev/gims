angular.module('myApp.directives').directive('gimsTextQuestion', function () {
    return {
        restrict: 'E',
        template: "<div class='span4' ng-repeat='part in question.parts'>"+
                "     <label for='numerical-{{question.id}}-{{part.id}}'>"+
                "         <div ng-switch='part.name'>" +
                "               <div ng-switch-when='Total'>Urban + Rural</div>"+
                "               <div ng-switch-when='Urban'>Urban</div>"+
                "               <div ng-switch-when='Rural'>Rural</div>"+
                "         </div>"+
                "         <textarea class='span12' name='numerical-{{question.id}}-{{part.id}}' id='numerical-{{question.id}}-{{part.id}}'></textarea>"+
                "     </label>"+
                " </div>",
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
