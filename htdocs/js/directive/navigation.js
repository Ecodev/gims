
angular.module('myApp.directives').directive('gimsQuestionsNav', function() {
        return {
            restrict: 'E',
            template:""+
                    "   <ul class='nav nav-tabs nav-stacked'>"+
                    "       <li ng-repeat='(key, question) in navigation'>"+
                    "           <a href='#'  ng-click='goTo(key)' style='padding-left:{{question.level+1}}em;'>"+
                    "               {{question.name}}"+
                    "           </a>"+
                    "       </li>"+
                    "   </ul>"
        }
});