/**
 * Directive very highly inspired by core ngIf but evaluate only on events instead of $watch()
 */
angular.module('myApp.directives').directive('gimsIf', function($animate, $rootScope) {

    /**
     * Return the DOM siblings between the first and last node in the given array.
     * @param {Array} nodes array like object
     * @returns {DOMElement} object containing the elements
     */
    function getBlockElements(nodes) {
        var startNode = nodes[0],
                endNode = nodes[nodes.length - 1];
        if (startNode === endNode) {
            return $(startNode);
        }

        var element = startNode;
        var elements = [element];

        do {
            element = element.nextSibling;
            if (!element) {
                break;
            }
            elements.push(element);
        } while (element !== endNode);

        return $(elements);
    }

    function evalCondition(block, childScope, previousElements,$scope, $element, $attr, ctrl, $transclude) {
        var value = $scope.$eval($attr.gimsIf);

        if (value) {
            if (!childScope) {
                childScope = $scope.$new();
                $transclude(childScope, function(clone) {
                    clone[clone.length++] = document.createComment(' end gimsIf: ' + $attr.gimsIf + ' ');
                    // Note: We only need the first/last node of the cloned nodes.
                    // However, we need to keep the reference to the jqlite wrapper as it might be changed later
                    // by a directive with templateUrl when it's template arrives.
                    block = {
                        clone: clone
                    };
                    $animate.enter(clone, $element.parent(), $element);
                });
            }
        } else {
            if (previousElements) {
                previousElements.remove();
                previousElements = null;
            }
            if (childScope) {
                childScope.$destroy();
                childScope = null;
            }
            if (block) {
                previousElements = getBlockElements(block.clone);
                $animate.leave(previousElements, function() {
                    previousElements = null;
                });
                block = null;
            }
        }
    }

    return {
        transclude: 'element',
        priority: 600,
        terminal: true,
        restrict: 'A',
        $$tlb: true,
        link: function($scope, $element, $attr, ctrl, $transclude) {
            var block, childScope, previousElements;

            $rootScope.$on('gims-tablefilter-show-labels-toggled', function gimsIfWatchAction() {
                evalCondition(block, childScope, previousElements,$scope, $element, $attr, ctrl, $transclude);
            });

            evalCondition(block, childScope, previousElements,$scope, $element, $attr, ctrl, $transclude);
        }
    };
});
