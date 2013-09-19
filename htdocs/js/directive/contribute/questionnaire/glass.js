angular.module('myApp.directives').directive('gimsContributeQuestionnaireGlass', function ()
{
    return {
        restrict: 'E',
        templateUrl: '/template/contribute/questions',
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



                    // preparing hierarchy nav
                    $scope.hierarchicNavigation = $scope.getChildren({navIndex:-1,level:-1});


                    $scope.refreshQuestion();
                }
            });

            $scope.getChildren = function(question)
            {
                var elements = [];
                for(var index = question.navIndex+1; index<$scope.navigation.length; index++){
                    var testedQuestion = $scope.navigation[index];
                    testedQuestion.navIndex=index;

                    // si même niveau ou inférieur, inutile de poursuivre, c'est la fin du chapitre
                    if (testedQuestion.level<=question.level) {
                        return elements;
                    }

                    // si niveau directement inférieur = enfant
                    if (testedQuestion.level==question.level+1) {
                        elements.push(testedQuestion);
                    }

                    if (testedQuestion.level>=question.level+1) {
                        testedQuestion['children'] = $scope.getChildren(testedQuestion);
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



            $scope.goToPrintMode = function ()
            {
                var i=0;
                for(i; i<$scope.navigation.length; i++){
                    if($scope.navigation[i].level===0 && ($scope.navigation[i].active_parent || $scope.navigation[i].active)){
                        console.info('id : '+i);
                        $scope.navigation[i].isFinal=true;
                        $scope.currentIndex=$scope.navigation[i].navIndex;
                        break;
                    }
                }
                setTimeout(function()
                {
                    window.print();
                    $scope.navigation[i].isFinal=false;
                    $scope.refreshQuestion();
                },1500);
            }




            $scope.refreshQuestion = function ()
            {
                $scope.currentQuestion = $scope.questions[$scope.navigation[$scope.currentIndex].index];
                // if question is chapter, retrieve all the subquestions that are contained in the chapter for display.
                if ($scope.currentQuestion.isFinal) {
                    var children = [];
                    for (var i=Number($scope.currentQuestion.index)+1; i<$scope.questions.length; ++i) {
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

                // Update nav (active and active_parent class)
                for (var id in $scope.navigation) {
                    var question = $scope.navigation[id];
                    question.active=false;
                    question.active_parent=false;
                }

                var firstChapterPerLevel = $scope.getListOfFirstChapterPerLevel($scope.currentIndex, $scope.navigation);
                for(var i=0; i<firstChapterPerLevel.length; i++){
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


    }
});
