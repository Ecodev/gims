angular.module('myApp.directives').directive('gimsCellMenu', function($rootScope, $dropdown, TableFilter) {

    return {
        restrict: 'E',
        template: '<span class="input-group-btn">' +
                '    <button type="button" class="btn btn-default dropdown-toggle">' +
                '        <i class="type-icon fa fa-fw fa-angle-down"></i>' +
                '    </button>' +
                '</span>',
        replace: true,
        scope: {
            questionnaire: '=',
            filter: '=',
            part: '='
        },
        link: function(scope, element) {

            scope.getCellType = TableFilter.getCellType;

            var button = element.find('button');
            button.bind('click', function() {
                $dropdown.open({
                    button: button,
                    templateUrl: '/template/browse/cell-menu/menu',
                    controller: 'CellMenuCtrl',
                    resolve: {
                        questionnaire: function() {
                            return scope.questionnaire;
                        },
                        filter: function() {
                            return scope.filter;
                        },
                        part: function() {
                            return scope.part;
                        }
                    }
                });
            });

            var icon = element.find('.type-icon').get(0);
            /**
             * Apply custom visual style
             */
            function updateVisualStyle() {
                var type = TableFilter.getCellType(scope.questionnaire, scope.filter, scope.part.id);

                var classes;
                switch (type) {
                    case 'loading':
                        classes = 'fa fa-fw fa-gims-loading';
                        break;
                    case 'error':
                        classes = 'fa fa-fw fa-warning text-warning';
                        break;
                    case 'answer':
                        classes = 'fa fa-fw fa-question';
                        break;
                    case 'rule':
                        classes = 'fa fa-fw fa-gims-rule computable';
                        break;
                    case 'summand':
                        classes = 'fa fa-fw fa-gims-summand computable';
                        break;
                    case 'child':
                        classes = 'fa fa-fw fa-gims-child computable';
                        break;
                    default:
                        classes = 'fa fa-fw fa-angle-down';
                }

                icon.className = classes;
            }

            // When we get all data, apply custom visual style
            $rootScope.$on('gims-tablefilter-computed', updateVisualStyle);
        }
    };
});
