angular.module('myApp.directives').directive('gimsQuestionnaire', function () {
    return {
        restrict: 'E',
        templateUrl: '/template/contribute/questions',

        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal) {

            $scope.currentIndex = 0;
            $scope.currentQuestion = null;

            $scope.$watch('questions', function(questions) {
                //console.info('watch questions');
                if( questions.length>0 ){
                    $scope.refreshQuestion();
                }
            });

            $scope.$watch('currentIndex', function(newIndex, old){
                //console.info('watch index');
                if(newIndex !== old)
                    $scope.refreshQuestion();
            });

            $scope.refreshQuestion = function(){
                $scope.currentQuestion = $scope.questions[$scope.currentIndex];
                if($scope.currentQuestion.final){
                    var children = [];
                    for(var i=Number($scope.currentIndex)+1; i<$scope.questions.length; ++i){
                        var testedQuestion = $scope.questions[i];
                        if(testedQuestion.level > $scope.currentQuestion.level ){
                            children.push(testedQuestion);
                        }else{
                            break;
                        }
                    }
                    $scope.currentQuestionChildren = children;
                }else{
                    $scope.currentQuestionChildren = [];
                }
            }















            /**
             *  Navigation
             *  */

            $scope.goToNext = function(){
                if($scope.currentIndex < $scope.questions.length-1)
                    $scope.currentIndex = $scope.getFinalQuestion(Number($scope.currentIndex)+1, true);
            }

            $scope.goToPrevious = function(){
                if($scope.currentIndex > 0)
                    $scope.currentIndex = $scope.getFinalQuestion(Number($scope.currentIndex)-1, false);
            }

            $scope.goTo = function(){
                // if the setted id is bigger than size of question array, find the index for the question that has the id
                var found = true;
                if($scope.wantedId > $scope.questions.length){
                    found = false;
                    for(var question in $scope.questions){
                        if( question>=0 && $scope.questions[question] && $scope.questions[question].id == $scope.wantedId){
                            found=true;
                            $scope.wantedId = question;
                            break;
                        }
                    }
                }
                if(found) $scope.currentIndex = $scope.getFinalQuestion(Number($scope.wantedId), false);
            }



            /**
             * This method return the real index for a wanted question (taking care of chapters that are finals (used like a single question)
             *  @param i :      the index of the wanted next question
             *  @param next :   true if we are asking for a future question in the array list. In case of Chapter is final,
             *                  don't jump to next question but jump until the next chapter.
             *  @return index : the index of the question you want to display

             * */
            $scope.getFinalQuestion = function(i, next){
                var wantedQuestion = $scope.questions[i];
                if(wantedQuestion.level == 0 && wantedQuestion.type=='Chapter')
                    return i;
                else{
                    if(next){ // we want to go to next question
                        /*  @todo : dont allow to go next when a final is the last question. Actually behaves has folder */
                        if(wantedQuestion.chapter!=null && Number(wantedQuestion.chapter.id)>0){
                            for(var j=i-1; j>=0; j--){ // loop rewind to find parent and know if its final or not
                                var testedQuestion = $scope.questions[j];
                                if(testedQuestion.type=='Chapter'){
                                    if(testedQuestion.final){ // if its final,
                                        for(var k=i; k<$scope.questions.length; k++){ // loop forward to find next chapter (we want to go to next question)
                                            var newTestedQuestion = $scope.questions[k];
                                            if(newTestedQuestion.id!=testedQuestion.id && newTestedQuestion.type=='Chapter' && newTestedQuestion.level == testedQuestion.level){
                                                return k;
                                            }
                                        }
                                    }else{
                                        break;
                                    }
                                }
                            }
                        }
                        return i; // if parent not final, return next id for next question / chapter in the list.

                    }else{ // we want to go to previous question
                        if(wantedQuestion.chapter!=null && Number(wantedQuestion.chapter.id)>0){
                            var lastFinal = i;
                            var lastLevel = wantedQuestion.level;
                            for(var j=i-1; j>=0; j--){ // loop rewind to find parent and know if its final or not
                                var testedQuestion = $scope.questions[j];
                                if(testedQuestion.level==0 && !testedQuestion.final){
                                    lastFinal= i;
                                    break;
                                }
                                else if(testedQuestion.level==0 && testedQuestion.final){
                                    lastFinal = j;
                                    break;
                                }
                                else if(testedQuestion.type=='Chapter'  ){
                                    if(testedQuestion.final && testedQuestion.level < lastLevel){ // if its final,
                                        lastFinal = j;
                                        lastLevel = testedQuestion.level;

                                    }
                                }
                            }
                            return lastFinal;
                        }
                        return i;
                    }
                }
                return i; // never should be called
            }



        }
    }
});




























