angular.module('myApp.directives').directive('gimsQuestionnaireToolBar', function() {
        return {
            restrict: 'E',
            template:"" +
                "<div class='navbar'>"+
                "    <div class='navbar-inner'>"+
                "        <a class='brand' href='#'>Tools : </a>"+
                "        <ul class='nav'>"+
                "            <li>"+
                "                <div class='btn-group'>"+
                "                    <button class='btn btn-primary' ng-click='goToPrevious();'><i class='icon-chevron-left'></i>Previous</button>"+
                "                    <button class='btn btn-primary' ng-click='goToNext();'>Next<i class='icon-chevron-right'></i></button>"+
                "                </div>"+
                "            </li>"+
                "            <li class='divider-vertical'></li>"+
                "            <li><button class='btn btn-primary'><i class='icon-download-alt'></i>Export</button></li>"+
                "            <li class='divider-vertical'></li>"+
                "            <li><button class='btn btn-success'><i class='icon-check'></i>Lock questionnaire and notify validator</button></li>"+
                "        </ul>"+
                "    </div>"+
                "</div>"
        }
});