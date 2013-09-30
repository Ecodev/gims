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
                "            <li><a href='/export/questionnaire/{{questionnaire.id}}' target='_blank' class='btn btn-default'><i class='icon-download-alt'></i> Export</a></li>"+
                "            <li class='divider-vertical'></li>"+
                "            <li><button class='btn btn-default' ng-click='goToPrintMode()'><i class='icon-print'></i> Print</button></li>"+
                "            <li class='divider-vertical'></li>"+
                "            <li><button class='btn btn-default' ng-class=\"{'btn-success':questionnaire.status==3, 'btn-warning':questionnaire.status==2, disabled:questionnaire.status==1}\"><i class='icon-check'></i> Submit for validation</button></li>"+
                "        </ul>"+
                "    </div>"+
                "</div>"
        }
});