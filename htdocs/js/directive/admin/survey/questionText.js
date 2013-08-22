angular.module('myApp.directives').directive('gimsTextQuestion', function () {
    return {
        restrict: 'E',
        template: "<div class='span4' ng-repeat='part in question.parts'>"+
            "     <label for='numerical-{{question.id}}-{{part.id}}'>"+
            "         {{part.name}}<br/>"+
            "         <input class='span12' type='text' name='numerical-{{question.id}}-{{part.id}}' id='numerical-{{question.id}}-{{part.id}}'/>"+
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
