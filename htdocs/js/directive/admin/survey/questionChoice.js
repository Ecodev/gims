angular.module('myApp.directives').directive('gimsChoiQuestion', function () {
    return {
        restrict: 'E',
        template:   "<div class='row-fluid'>"+
                    "   <div class='span1 text-center' ng-repeat='part in question.parts'>"+
                    "         <div ng-switch='part.name'>" +
                    "               <div ng-switch-when='Total'>National</div>"+
                    "               <div ng-switch-when='Urban'>Urban</div>"+
                    "               <div ng-switch-when='Rural'>Rural</div>"+
                    "         </div>"+
                    "   </div>"+
                    "</div>"+
                    "<div class='row-fluid' ng-repeat='choice in question.choices'>"+
                    "   <div class='span1 text-center' ng-repeat='part in question.parts'>"+
                    "       <div ng-switch='question.isMultiple'>"+
                    "           <div ng-switch-when='true'>" +
                    //ng-true-value='{{choice.id}}'
                    "               <input type='checkbox' ng-model='index[question.id+\"-\"+choice.id+\"-\"+part.id].isCheckboxChecked' ng-click='save(question.id,choice.id,part.id)' name='{{part.id}}-{{choice.id}}' />"+
                    "           </div>"+
                    "           <div ng-switch-when='false'>" +
                    "               <input type='radio' ng-model='index[question.id+\"-\"+part.id].valuePercent' value='{{choice.value}}' ng-click='save(question.id,choice.id,part.id)' name='{{part.id}}-{{question.id}}'/>"+
                    "           </div>"+
                    "       </div>"+
                    "   </div>"+
                    "   <span class='span9'>"+
                    "       {{choice.label}}"+
                    "   </span>"+
                    "</div>",

        scope:{
            index:'=',
            question:'='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.$watch('question', function(question) 
            {
                angular.forEach(question.parts, function(part) {
                    if(question.isMultiple){
                        angular.forEach(question.choices, function(choice) {
                            var identifier = question.id+"-"+choice.id+"-"+part.id;
                            if (!$scope.index[identifier] || ($scope.index[identifier] && $scope.index[identifier].isCheckboxChecked===null)) {
                                $scope.index[identifier] = $scope.findAnswer(question, part.id, choice.id);
                            }
                        });
                    }else{
                        var identifier = question.id+"-"+part.id;
                        if (!$scope.index[identifier] || 
                                ($scope.index[identifier] && !$scope.index[identifier].valuePercent)) {
                            $scope.index[identifier] = $scope.findAnswer(question, part.id);
                        }
                    }
                });
            });






            $scope.findAnswer = function (question, pid, cid)
            {

                for(var key in question.answers){
                    var testedAnswer = question.answers[key];
                    if (testedAnswer.part && testedAnswer.part.id==pid) {
                        if (!question.isMultiple) {
                            return testedAnswer;
                        }else if (question.isMultiple &&  testedAnswer.valueChoice==cid) {
                            testedAnswer.isCheckboxChecked = true;
                            return testedAnswer;
                        }
                    }
                }
                var emptyChoice = {
                    questionnaire : Number(question.parentResource.id),
                    part : pid,
                    question : question.id,
                    isCheckboxChecked :null
                }
                if(cid) emptyChoice.valueChoice=cid;
                return emptyChoice;
            }




            $scope.save = function (question_id, choice_id, part_id)
            {
                console.info(question_id+"-"+choice_id+"-"+part_id);
                if($scope.question.isMultiple){
                    var identifier = question_id+"-"+choice_id+'-'+part_id;
                }else{
                    var identifier = question_id+"-"+part_id;
                }

                var newAnswer = $scope.index[identifier];
                newAnswer.valueChoice=choice_id;

                // if id is setted, that means it has just been removed (click event)
                if (newAnswer.id && !$scope.question.isMultiple) {
                    newAnswer.put();
                }else if (newAnswer.id && $scope.question.isMultiple) {
                    newAnswer.remove().then(function(){
                        $scope.index[identifier] = {
                            questionnaire : Number($scope.question.parentResource.id),
                            part : part_id,
                            valueChoice:choice_id,
                            question : $scope.question.id,
                            isCheckboxChecked: false
                        };

                    });

                // if don't exists -> create
                } else if (!newAnswer.id) {
                    Restangular.all('answer').post(newAnswer).then(function(answer){
                        if($scope.question.isMultiple) answer.isCheckboxChecked = true;
                        $scope.index[identifier] = answer;

                    });
                }

            }






        }
    }
});
