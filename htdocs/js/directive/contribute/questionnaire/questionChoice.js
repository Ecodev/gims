angular.module('myApp.directives').directive('gimsChoiQuestion', function () {
    return {
        restrict: 'E',
        template:   "<table>"+
                        "<tr>"+
                        "   <td class='text-center' ng-repeat='part in question.parts' ng-switch='part.name'>"+
                        "       <div ng-switch-when='Total'>National</div>"+
                        "       <div ng-switch-when='Urban'>Urban</div>"+
                        "       <div ng-switch-when='Rural'>Rural</div>"+
                        "   </td><td></td>"+
                        "</tr>"+
                        "<tr ng-repeat='choice in question.choices' >"+
                        "   <td class='text-center' ng-repeat='part in question.parts' >"+
                        "       <div ng-switch='question.isMultiple'>"+
                        "           <div ng-switch-when='true'>" +
                        "               <input type='checkbox' ng-disabled='saving' ng-model='index[question.id+\"-\"+choice.id+\"-\"+part.id].isCheckboxChecked' ng-click='save(question.id,choice,part.id)' name='{{part.id}}-{{choice.id}}' />"+
                        "           </div>"+
                        "           <div ng-switch-when='false'>" +
                        "               <input type='radio' ng-disabled='saving' ng-model='index[question.id+\"-\"+part.id].valuePercent' value='{{choice.value}}' ng-click='save(question.id,choice,part.id)' name='{{part.id}}-{{question.id}}'/>"+
                        "           </div>"+
                        "       </div>"+
                        "   </td>"+
                        "   <td><div style='padding-top:5px'>{{choice.name}}</div></td>"+
                        "</tr>"+
                    "</table>",

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
                        if (!$scope.index[identifier] || ($scope.index[identifier] && ($scope.index[identifier].valuePercent===null || $scope.index[identifier].valuePercent===undefined))) {
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
                        } else if (question.isMultiple &&  testedAnswer.valueChoice.id==cid) {
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
                if(cid) emptyChoice.valueChoice = {id:cid};
                return emptyChoice;
            }



            $scope.saving = false;
            $scope.save = function (question_id, choice, part_id)
            {
                $scope.saving = true;
                if($scope.question.isMultiple){
                    var identifier = question_id+"-"+choice.id+"-"+part_id;
                }else{
                    var identifier = question_id+"-"+part_id;
                }

                var newAnswer = $scope.index[identifier];
                newAnswer.valueChoice=choice;

                // if id is setted, that means it has just been removed (click event)
                if (newAnswer.id && !$scope.question.isMultiple) {
                    newAnswer.put().then(function(){$scope.saving=false;});
                }else if (newAnswer.id && $scope.question.isMultiple) {
                    newAnswer.remove().then(function(){
                        $scope.index[identifier] = {
                            questionnaire : Number($scope.question.parentResource.id),
                            part : part_id,
                            valueChoice : choice,
                            question : $scope.question.id,
                            isCheckboxChecked: false
                        };
                        $scope.saving=false;
                    });

                // if don't exists -> create
                } else if (!newAnswer.id) {
                    Restangular.all('answer').post(newAnswer).then(function(answer){
                        if($scope.question.isMultiple) answer.isCheckboxChecked = true;
                        $scope.index[identifier] = answer;
                        $scope.saving=false;
                    });
                }

            }






        }
    }
});
