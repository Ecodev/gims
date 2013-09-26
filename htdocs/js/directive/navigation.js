angular.module('myApp.directives').directive('gimsQuestionsNav', function(RecursionHelper)
{
    return {
        restrict: 'E',
        template: "" +
                "   <ul class='nav nav-pills nav-stacked'>" +
                "       <li ng-repeat='question in nav' ng-class='{active:question.active==true, active_parent:question.active_parent==true}'>" +
                "           <a href='#' ng-show='!question.hasFinalParentChapters' ng-click='goToLocal(question.navIndex)'><span class='badge' ng-class=\"{'badge-success':question.status==3, 'badge-warning':question.status==2, 'badge-important':question.status==1}\">&nbsp;</span> {{question.name}}</a>" +
                "               <div class='potentialEmptyUl' ng-class='{mask:question.children.length==0}'>"+
                "                   <gims-questions-nav nav='question.children' go-to='goToLocal(wantedIndex)'></gims-questions-nav>" +
                "               </div>" +
                "       </li>" +
                "   </ul>"
        ,

        scope: {
            nav: '=',
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

