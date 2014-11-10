angular.module('myApp.directives').directive('gimsDiscussionMenu', function($dropdown) {

    return {
        restrict: 'E',
        template: '<div class="btn-group dropdown">' +
                '    <button class="btn btn-default dropdown-toggle" type="button" aria-haspopup="true" aria-expanded="false">' +
                '        <i class="fa fa-comments"></i> Discussions <span class="caret"></span>' +
                '    </button>' +
                '</div>',
        scope: {
            surveys: '=?',
            questionnaires: '=?',
            filters: '=?'
        },
        link: function(scope, element) {

            var button = element.find('button');
            button.bind('click', function() {
                $dropdown.open({
                    button: button,
                    templateUrl: '/template/browse/discussion/menu',
                    controller: 'DiscussionMenuCtrl',
                    resolve: {
                        menuData: function() {
                            return {
                                surveys: scope.surveys,
                                questionnaires: scope.questionnaires,
                                filters: scope.filters
                            };
                        }
                    }
                });
            });
        }
    };
});
