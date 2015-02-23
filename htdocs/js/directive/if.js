/**
 * Directive very highly inspired by core ngIf but evaluate only on events instead of $watch()
 */
angular.module('myApp.directives').directive('gimsIf', function($animate, $rootScope) {

    /**
     * Return the DOM siblings between the first and last node in the given array.
     * @param {Array} nodes array like object
     * @returns {jqLite} jqLite collection containing the nodes
     */
    function getBlockNodes(nodes) {
        // TODO(perf): just check if all items in `nodes` are siblings and if they are return the original
        //             collection, otherwise update the original collection.
        var node = nodes[0];
        var endNode = nodes[nodes.length - 1];
        var blockNodes = [node];

        do {
            node = node.nextSibling;
            if (!node) {
                break;
            }
            blockNodes.push(node);
        } while (node !== endNode);

        return $(blockNodes);
    }

    return {
        multiElement: true,
        transclude: 'element',
        priority: 600,
        terminal: true,
        restrict: 'A',
        $$tlb: true,
        link: function($scope, $element, $attr, ctrl, $transclude) {
            var block, childScope, previousElements;

            function evalCondition() {
                var value = $scope.$eval($attr.gimsIf);
                if (value) {
                    if (!childScope) {
                        $transclude(function(clone, newScope) {
                            childScope = newScope;
                            clone[clone.length++] = document.createComment(' end gimsIf: ' + $attr.gimsIf + ' ');
                            // Note: We only need the first/last node of the cloned nodes.
                            // However, we need to keep the reference to the jqlite wrapper as it might be changed later
                            // by a directive with templateUrl when its template arrives.
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
                        previousElements = getBlockNodes(block.clone);
                        $animate.leave(previousElements).then(function() {
                            previousElements = null;
                        });
                        block = null;
                    }
                }
            }

            $rootScope.$on('gims-tablefilter-show-labels-toggled', function gimsIfWatchAction() {
                evalCondition();
            });

            evalCondition();
        }
    };
});
