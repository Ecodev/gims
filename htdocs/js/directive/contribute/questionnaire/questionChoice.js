angular.module('myApp.directives').directive('gimsChoiQuestion', function (QuestionAssistant) {
    return {
        restrict: 'E',
        template:   "<ng-form name='innerQuestionForm'>" +
                        "<table>"+
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
                            "               <input type='checkbox' ng-disabled='saving' ng-model='index[question.id+\"-\"+choice.id+\"-\"+part.id].isCheckboxChecked' ng-click='save(question,choice,part)' name='{{part.id}}-{{choice.is}}' />"+
                            "           </div>"+
                            "           <div ng-switch-when='false'>" +
                            "               <input type='radio' ng-disabled='saving' ng-required='question.isCompulsory' ng-model='index[question.id+\"-\"+part.id].valueChoice.id' value='{{choice.id}}' ng-click='save(question,choice,part)' name='{{part.id}}-{{question.id}}'/>"+
                            "           </div>"+
                            "       </div>"+
                            "   </td>"+
                            "   <td><div style='padding-top:5px'>{{choice.name}}</div></td>"+
                            "</tr>"+
                        "</table><br/>"+
                        "<span ng-show='question.isCompulsory' class='badge' ng-class=\"{'badge-danger':question.statusCode==1, 'badge-success':question.statusCode==3}\">Required</span>"+
                        "<span ng-show='!question.isCompulsory' class='badge' ng-class=\"{'badge-warning':question.statusCode==2, 'badge-success':question.statusCode==3}\">Optional</span>"+
                    "</ng-form>",

        scope: {
            index: '=',
            question: '='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.saving = false;
            $scope.save = function (question, choice, part)
            {
                $scope.saving = true;
                if ($scope.question.isMultiple) {
                    var identifier = question.id + "-" + choice.id + "-" + part.id;
                } else {
                    var identifier = question.id + "-" + part.id;
                }

                var newAnswer = $scope.index[identifier];
                newAnswer.valueChoice = choice;
                newAnswer.valuePercent = choice.value;


                // if id setted on radio button, update
                if (newAnswer.id && !$scope.question.isMultiple) {
                    newAnswer.put().then(function ()
                    {
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                    // if id is setted on checkbox element, that means that there already is a result and we want to remove it
                } else if (newAnswer.id && $scope.question.isMultiple) {
                    newAnswer.remove().then(function ()
                    {
                        $scope.index[identifier] = QuestionAssistant.getEmptyChoiceAnswer(question, part, choice);
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                    // if don't exists -> create
                } else if (!newAnswer.id) {
                    Restangular.all('answer').post(newAnswer).then(function (answer)
                    {
                        if ($scope.question.isMultiple) {
                            answer.isCheckboxChecked = true;
                        }
                        $scope.index[identifier] = answer;
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });
                }

            }


        }
    }
});
