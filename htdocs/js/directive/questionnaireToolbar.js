angular.module('myApp.directives').directive('gimsQuestionnaireToolBar', function() {
        return {
            restrict: 'E',
            template:"" +
                "<div class='navbar hidden-print'>"+
                "    <div class='navbar-inner'>"+
                "        <a class='brand' href='#'>Tools : </a>"+
                "        <ul class='nav'>"+
                "            <li>"+
                "                <div class='btn-group'>"+
                "                    <button class='btn btn-default' ng-click='goToPrevious();'><i class='icon-chevron-left'></i>Previous</button>"+
                "                    <button class='btn btn-default' ng-click='goToNext();'>Next<i class='icon-chevron-right'></i></button>"+
                "                </div>"+
                "            </li>"+
                "            <li class='divider-vertical'></li>"+
                "            <li><a href='/export/questionnaire/{{questionnaire.id}}/{{questionnaire.name}}.xslx' target='_blank' class='btn btn-default'><i class='icon-download-alt'></i> Export</a></li>"+
                "            <li class='divider-vertical'></li>"+
                "            <li><button class='btn btn-default' ng-click='goToPrintMode()'><i class='icon-print'></i> Print</button></li>"+
                "            <li class='divider-vertical'></li>"+
                "            <li><button class='btn btn-default' ng-show='questionnaire.status==\"new\"' ng-click='markQuestionnaireAs(\"completed\")' ng-class=\"{'btn-success':questionnaire.statusCode==3, 'btn-warning':questionnaire.statusCode==2, disabled:questionnaire.statusCode==1}\"><i class='icon-check'></i> Submit for validation</button></li>"+
                "            <li><button class='btn btn-default' ng-show='questionnaire.status==\"completed\"' ng-click='markQuestionnaireAs(\"validated\")' ng-class=\"{'btn-success':questionnaire.statusCode==3, 'btn-warning':questionnaire.statusCode==2, disabled:questionnaire.statusCode==1}\"><i class='icon-check'></i> Validate questionnaire</button></li>"+
                "        </ul>"+
                "    </div>"+
                "</div>"
        }
});