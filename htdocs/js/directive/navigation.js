angular.module('myApp.directives').directive('gimsQuestionsNav', function(RecursionHelper)
{
    return {
        restrict: 'E',
        template: "" +
            //nav nav-pills nav-stacked
                "   <ul class='nav nav-pills nav-stacked'>" +
                "       <li ng-repeat='question in nav' ng-class='{active:flatNav[question.navIndex].active==true, active_parent:flatNav[question.navIndex].active_parent==true}'>" +
                "           <a href='#' ng-click='goToLocal(question.navIndex)'>{{question.name}}</a>" +
                "               <div class='potentialEmptyUl' ng-class='{mask:question.children.length==0}'>"+
                "                   <gims-questions-nav nav='question.children' flat-nav='flatNav' go-to='goToLocal(wantedIndex)'></gims-questions-nav>" +
                "               </div>" +
                "       </li>" +
                "   </ul>",

        scope: {
            nav: '=',
            flatNav: '=',
            goTo: '&'
        },

        compile: function (element, $scope, tAttrs)
        {
            return RecursionHelper.compile(element);
        },

        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {

            $scope.goToLocal = function(index){
                $scope.goTo({wantedIndex:index});
            }
        }

    }
});

