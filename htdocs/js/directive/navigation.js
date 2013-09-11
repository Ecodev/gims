/**
 * Basic usage is:
 * <gims-questions-nav navigation="navigation"></gims-questions-nav>
 */

angular.module('myApp.directives').directive('gimsQuestionsNav', function() {
        return {
            restrict: 'E',
            template:""+
                    "   <ul class='nav nav-tabs nav-stacked'>"+
                    "       <li ng-repeat='question in navigation'>"+
                    "           <a href='#' ng-class='{question.isFinal: active}' ng-click='goTo(question.id)' style='padding-left:{{question.level+1}}em;'>"+
                    "               {{question.name}}"+
                    "           </a>"+
                    "       </li>"+
                    "   </ul>"
        }
});