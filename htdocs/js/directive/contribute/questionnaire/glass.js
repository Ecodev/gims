angular.module('myApp.directives').directive('gimsContributeQuestionnaireGlass', function ()
{
    return {
        restrict: 'E',
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {

            $scope.navigation = []; // used of next + previous buttons
            $scope.hierarchicNavigation = []; // used of menu hierarchy
            $scope.currentIndex = 0;
            $scope.currentQuestion = null;
            $scope.index = {}; // indexed answers


            $scope.$watch('questions', function (questions)
            {
                if (questions.length > 0) {

                    questions[0].active=true;

                    angular.forEach(questions, function (question, index)
                    {
                        // prepare navigation
                        question.index = index;
                        if (question && question.type && !$scope.hasFinalParentChapters(index)) {
                            $scope.navigation.push(question);
                        }

                        // restangularize answers
                        angular.forEach(question.answers, function (answer)
                        {
                            answer = Restangular.restangularizeElement(null, answer, 'answer');
                        });
                    });

                    // end of navigation preparation
                    angular.forEach($scope.navigation, function (question, index)
                    {
                        question.navIndex=index;
                        question['children'] = $scope.getChildren(question.level, index);
                        if (question.level === 0){
                            $scope.hierarchicNavigation.push(question);
                        }
                    });

                    $scope.refreshQuestion();
                }
            });

            $scope.getChildren = function(level, key)
            {
                var elements = [];
                for(var i=key+1; i<$scope.navigation.length-1; i++){
                    var testedEl = $scope.navigation[i];
                    testedEl['children'] = $scope.getChildren(testedEl.level, i);
                    if(testedEl.level == level+1) {
                        elements.push(testedEl);
                    } else if (testedEl.level <= level){
                        break;
                    }
                }
                return elements;
            }

            $scope.$watch('currentIndex', function (newIndex, old)
            {
                if (newIndex !== old) {
                    $scope.refreshQuestion();
                }
            });


            $scope.refreshQuestion = function ()
            {
                $scope.currentQuestion = $scope.questions[$scope.navigation[$scope.currentIndex].index];
                // if question is chapter, retrieve all the subquestions that are contained in the chapter for display.
                if ($scope.currentQuestion.isFinal) {
                    var children = [];
                    for (var i = Number($scope.currentQuestion.index) + 1; i < $scope.questions.length; ++i) {
                        var testedQuestion = $scope.questions[i];
                        if (testedQuestion.level > $scope.currentQuestion.level) {
                            children.push(testedQuestion);
                        } else {
                            break;
                        }
                    }
                    $scope.currentQuestionChildren = children;

                    // if question is not a chapter, there is no subquestions
                } else {
                    $scope.currentQuestionChildren = [];
                }

                // retrieve all parent chapter to display name and description
                $scope.parentChapters = [];
                var firstChapterPerLevel = $scope.getListOfFirstChapterPerLevel($scope.navigation[$scope.currentIndex].index, $scope.questions);
                for (var q in firstChapterPerLevel) {
                    $scope.parentChapters.push($scope.questions[firstChapterPerLevel[q]]);
                }

                // Update nav
                for (var id in $scope.navigation) {
                    var question = $scope.navigation[id];
                    question.active=false;
                    question.active_parent=false;
                }

                var firstChapterPerLevel = $scope.getListOfFirstChapterPerLevel($scope.currentIndex, $scope.navigation);
                for(var i=0; i<firstChapterPerLevel.length; i++){
                    console.info(i);
                    $scope.navigation[firstChapterPerLevel[i]].active_parent = true;
                }
                $scope.navigation[$scope.currentIndex].active = true;

            };


            /**
             *  Navigation
             *  */
            $scope.goToNext = function ()
            {
                if ($scope.currentIndex < $scope.navigation.length - 1) {
                    $scope.currentIndex = $scope.currentIndex + 1;
                }
            };

            $scope.goToPrevious = function ()
            {
                if ($scope.currentIndex > 0) {
                    $scope.currentIndex = $scope.currentIndex - 1;
                }
            };

            $scope.goTo = function (wantedIndex)
            {
                if(Number(wantedIndex)>=0) $scope.currentIndex = wantedIndex;
            };


            $scope.getListOfFirstChapterPerLevel = function (startIndex, questions)
            {
                var askedQuestion = questions[startIndex];
                var firstChapterPerLevel = [];
                for (var j = startIndex; j >= 0; j--) { // go rewind until first question or first zero leveled question
                    var testedQuestion = questions[j];
                    if (testedQuestion.type === 'Chapter' && testedQuestion.level < askedQuestion.level && !firstChapterPerLevel[testedQuestion.level]) {
                        firstChapterPerLevel[testedQuestion.level] = j; // sets the first chapter encontered each level
                    }
                    if (testedQuestion.level === 0) {
                        break;
                    }
                }
                return firstChapterPerLevel;
            };

            $scope.hasFinalParentChapters = function (index)
            {
                var listOfParentChapters = $scope.getListOfFirstChapterPerLevel(index, $scope.questions);
                for (var i in listOfParentChapters) {
                    if ($scope.questions[listOfParentChapters[i]].isFinal) {
                        return true;
                    }
                }
                return false;
            };


        },

        templateUrl: '/template/contribute/questions'
    }
});
