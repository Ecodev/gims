angular.module('myApp.directives').directive('gimsQuestionnaireToolBar', function() {
        return {
            restrict: 'E',
            template:"" +
                "<div class='navbar navbar-default hidden-print'>"+
                "    <div class='navbar-header'>"+
                "    <a class='navbar-brand' href='#'>Tools </a>"+
                "    </div>"+
                "    <form class='navbar-form'>"+
                "        <div class='form-group'>"+
                "            <div class='btn-group'>"+
                "                <button class='btn btn-default' ng-click='goToPrevious();'><i class='icon-chevron-left'></i> Previous</button>"+
                "                <button class='btn btn-default' ng-click='goToNext();'>Next <i class='icon-chevron-right'></i></button>"+
                "            </div>"+
                "        </div>"+
                "        <div class='form-group'><a href='/export/questionnaire/{{questionnaire.id}}/{{questionnaire.name}}.xslx' target='_blank' class='btn btn-default'><i class='icon-download-alt'></i> Export</a></div>"+
                "        <div class='form-group'><button class='btn btn-default' ng-click='goToPrintMode()'><i class='icon-print'></i> Print</button></div>"+
                "        <div class='form-group'><button class='btn btn-default' ng-show='questionnaire.status==\"new\"' ng-click='markQuestionnaireAs(\"completed\")' ng-class=\"{'btn-success':questionnaire.statusCode==3, 'btn-warning':questionnaire.statusCode==2, disabled:questionnaire.statusCode==1}\"><i class='icon-check'></i> Submit for validation</button></div>"+
                "        <div class='form-group'><button class='btn btn-default' ng-show='questionnaire.status==\"completed\" && questionnaire.permissions.validate' ng-click='markQuestionnaireAs(\"validated\")' ng-class=\"{'btn-success':questionnaire.statusCode==3, 'btn-warning':questionnaire.statusCode==2, disabled:questionnaire.statusCode==1}\"><i class='icon-check'></i> Validate questionnaire</button></div>"+
                "    </form>"+
                "</div>"
        }
});