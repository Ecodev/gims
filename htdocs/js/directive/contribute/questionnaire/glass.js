angular.module('myApp.directives').directive('gimsContributeQuestionnaireGlass', function (QuestionAssistant)
{
    return {
        restrict: 'E',
        templateUrl: '/template/contribute/questions',
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.navigation = []; // used in next + previous buttons
            $scope.hierarchicQuestions = []; // used in hierarchic menu
            $scope.currentIndex = 0;
            $scope.currentQuestion = 0;
            $scope.index = {}; // indexed answers
            $scope.score = {};


            $scope.$watch('questionnaire', function (){
                $scope.initializeQuestionnaire();
            });

            $scope.$watch('questions', function ()
            {
                $scope.initializeQuestionnaire();
            });

            $scope.$watch('currentIndex', function (newIndex, old)
            {
                if (newIndex !== old) {
                    $scope.refreshQuestion();
                }
            });

            $scope.initializeQuestionnaire = function()
            {
                if ($scope.questionnaire && $scope.questions.length > 0) {

//                    var note = {question : 7, questionnaire : 1}
//                    Restangular.all('note').post(note).then(function(note){console.log(note);});

                    $scope.questionnaire.level = -1;
                    $scope.questionnaire.index = -1;
                    $scope.questionnaire.statusCode= 4;

                    $scope.questions[0].active=true;

                    angular.forEach($scope.questions, function (question, index) {

                        // assign key to each question -> navigation menu bar uses it to avoid loops
                        question.index = index;

                        // prepare navigation
                        question.hasFinalParentChapters = $scope.hasFinalParentChapters(index);
                        if (question && question.type && !question.hasFinalParentChapters ) {
                            question.navIndex = $scope.navigation.length;
                            $scope.navigation.push(question);
                        }

                        // restangularize answers
                        angular.forEach(question.answers, function (answer) {
                            answer = Restangular.restangularizeElement(null, answer, 'answer');
                        });
                    });

                    // preparing hierarchic questions : used for nav and for validation form
                    $scope.hierarchicQuestions = $scope.getChildren(_.cloneDeep($scope.questionnaire), $scope.questions);
                    $scope.questionnaire.children = $scope.hierarchicQuestions;
                    QuestionAssistant.updateQuestion($scope.questionnaire, $scope.index, true);
                    $scope.refreshQuestion();
                }

            }



            $scope.markQuestionnaireAs = function(newStatus)
            {
                if($scope.questionnaire.statusCode==2 || $scope.questionnaire.statusCode==3) {
                    if (newStatus === 'completed' && $scope.questionnaire.status === 'new' ||
                            newStatus === 'validated' && $scope.questionnaire.permissions.validate && $scope.questionnaire.status === 'completed') {
                        $scope.questionnaire.status = newStatus;

                        // -> cyclic structure error -> remove children
                        var children = $scope.questionnaire.children;
                        delete $scope.questionnaire.children;

                        $scope.questionnaire.put().then(function(questionnaire){
                            $scope.questionnaire.status = questionnaire.status
                            $scope.questionnaire.children = children;
                        });

                    }
                }
            }


            /**
             *
             * @param question that may contain children
             * @param list a flat array with list of all questions
             * @returns {Array} a list of children
             */
            $scope.getChildren = function(question, list)
            {
                var elements = [];
                if (list && list.length>0) {
                    for(var index = question.index+1; index<list.length; index++){
                        var testedQuestion = list[index];

                        // si même profondeur ou inférieure, inutile de poursuivre, c'est la fin du chapitre
                        if (testedQuestion.level<=question.level) {
                            return elements;
                        }
                        // si profondeur plus grande de 1 = enfant
                        if (testedQuestion.level==question.level+1) {
                            testedQuestion.parent = question;
                            elements.push(testedQuestion);
                        }

                        if (testedQuestion.level>=question.level+1) {
                            testedQuestion['children'] = $scope.getChildren(testedQuestion, list);
                        }
                    }

                    return elements;
                }
            }



            $scope.goToPrintMode = function ()
            {
                var i=0;
                for(i; i<$scope.navigation.length; i++){
                    if($scope.navigation[i].level===0 && ($scope.navigation[i].active_parent || $scope.navigation[i].active)){
                        $scope.navigation[i].isFinal=true;
                        if (i==$scope.currentIndex) {
                            $scope.refreshQuestion();
                        } else {
                            $scope.currentIndex=$scope.navigation[i].navIndex;
                        }
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
                $scope.currentQuestion = $scope.navigation[$scope.currentIndex];
                // if question is chapter, retrieve all the subquestions that are contained in the chapter for display.
                if ($scope.currentQuestion.isFinal) {
                    var children = [];
                    for (var i=$scope.currentQuestion.index+1; i<$scope.questions.length; ++i) {
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
                var firstChapterPerLevel = $scope.getListOfFirstChapterPerLevel($scope.currentQuestion.index, $scope.questions);
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
                $scope.currentQuestion.active = true;
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
        }
    }
});
