angular.module('myApp.directives').directive('gimsContributeQuestionnaireGlass', function ()
{
    return {
        restrict: 'E',
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {

            $scope.navigation = [];
            $scope.currentIndex = 0;
            $scope.currentQuestion = null;
            $scope.index = {}; // indexed answers


            $scope.$watch('questions', function (questions)
            {
                if (questions.length > 0) {
                    angular.forEach(questions, function (question, index)
                    {
                        question.index = index;
                        if (question && question.type && !$scope.hasFinalParentChapters(index)) {
                            $scope.navigation.push(question);
                        }
                        angular.forEach(question.answers, function (answer)
                        {
                            answer = Restangular.restangularizeElement(null, answer, 'answer');
                        });
                    });

                    $scope.refreshQuestion();
                }
            });

            $scope.$watch('currentIndex', function (newIndex, old)
            {
                if (newIndex !== old) {
                    $scope.refreshQuestion();
                }
            });


            $scope.refreshQuestion = function ()
            {
                $scope.currentQuestion = $scope.questions[$scope.navigation[$scope.currentIndex].index];

                // if question is chapter, retrieve all the questions that are contained in the chapter for display.
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
                var firstChapterPerLevel = $scope.getListOfFirstChapterPerLevel(Number($scope.currentIndex));
                for (var q in firstChapterPerLevel) {
                    $scope.parentChapters.push($scope.questions[firstChapterPerLevel[q]]);
                }
            };


            /**
             *  Navigation
             *  */
            $scope.goToNext = function ()
            {
                if ($scope.currentIndex < $scope.navigation.length - 1) {
                    $scope.currentIndex = $scope.currentIndex+1;
                }
            };

            $scope.goToPrevious = function ()
            {
                if ($scope.currentIndex > 0) {
                    $scope.currentIndex = $scope.currentIndex-1;
                }
            };

            $scope.goTo = function (index)
            {
                $scope.currentIndex = index;
            };





            $scope.getListOfFirstChapterPerLevel = function (startIndex)
            {
                var askedQuestion = $scope.questions[startIndex];
                var firstChapterPerLevel = [];
                for (var j = startIndex; j >= 0; j--) { // go rewind until first question or first zero leveled question
                    var testedQuestion = $scope.questions[j];
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
                var listOfParentChapters = $scope.getListOfFirstChapterPerLevel(index);
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
