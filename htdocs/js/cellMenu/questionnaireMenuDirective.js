angular.module('myApp.directives').directive('gimsQuestionnaireMenu', function($dropdown) {

    return {
        restrict: 'E',
        template: '<div class="btn-group" style="width: 40px">' +
                '    <button type="button" class="btn btn-default dropdown-toggle btn-sm">' +
                '        <i class="fa fa-angle-down"></i>' +
                '    </button><span class="input-group-btn">' +
                '</div>',
        scope: {
            questionnaire: '='
        },
        link: function(scope, element) {

            var button = element.find('button');
            button.bind('click', function() {
                $dropdown.open({
                    button: button,
                    templateUrl: '/template/browse/cell-menu/questionnaire-menu',
                    controller: 'QuestionnaireMenuCtrl',
                    resolve: {
                        questionnaire: function() {
                            return scope.questionnaire;
                        }
                    }
                });
            });

        }
    };
});
