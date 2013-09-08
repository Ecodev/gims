angular.module('myApp.directives').directive('gimsChoiQuestion', function () {
    return {
        restrict: 'E',
        template:   "<div class='row-fluid'>"+
                    "   <div class='span1 text-center' ng-repeat='part in question.parts'>"+
                    "         <div ng-switch='part.name'>" +
                    "               <div ng-switch-when='Total'>Urban + Rural</div>"+
                    "               <div ng-switch-when='Urban'>Urban</div>"+
                    "               <div ng-switch-when='Rural'>Rural</div>"+
                    "         </div>"+
                    "   </div>"+
                    "</div>"+
                    "<div class='row-fluid' ng-repeat='choice in question.choices'>"+
                    "   <div class='span1 text-center' ng-repeat='part in question.parts'>"+
                    "       <div ng-switch='question.isMultiple'>"+
                    "           <div ng-switch-when='true'>" +
                    "               <input type='checkbox' ng-model='indexedAnswers[choice.id+\"-\"+part.id].isCheckboxChecked' ng-click='save(choice.id, part.id)' name='{{part.id}}-{{choice.id}}' />"+
                    "           </div>"+
                    "           <div ng-switch-when='false'>" +
                    "               <input type='radio' ng-model='indexedAnswers[part.id].valuePercent' value='{{choice.value}}' ng-click='save(choice.id,part.id)' name='{{part.id}}-{{question.id}}'/>"+
                    "           </div>"+
                    "       </div>"+
                    "   </div>"+
                    "   <span class='span9'>"+
                    "       {{choice.label}}"+
                    "   </span>"+
                    "</div>",
                    //"<div class='span11'><pre>{{indexedAnswers|json}}</pre></div>",

        scope:{
            question:'='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.indexedAnswers = {};
            $scope.$watch('question', function(question) {

                for(var i=0; i<question.parts.length; i++ ){
                    if(question.isMultiple){
                        for(var j=0; j<question.choices.length; j++ ){
                            var identifier = question.choices[j].id+"-"+question.parts[i].id;
                            $scope.indexedAnswers[identifier] = $scope.findAnswer(question, question.parts[i].id, question.choices[j].id);
                        }
                    }else{
                        var identifier = question.parts[i].id;
                        $scope.indexedAnswers[identifier] = $scope.findAnswer(question, question.parts[i].id);
                    }
                }

            });






            $scope.findAnswer = function (question, pid, cid)
            {

                for(var key in question.answers){
                    var testedAnswer = question.answers[key];
                    if (testedAnswer.part && testedAnswer.part.id==pid) {
                        if (!question.isMultiple) {
                            return testedAnswer;
                        }else if (question.isMultiple &&  testedAnswer.valueChoice==cid) {
                            return testedAnswer;
                        }
                    }
                }
                var emptyChoice = {
                    questionnaire : Number(question.parentResource.id),
                    part : pid,
                    question : question.id
                }
                if(cid) emptyChoice.valueChoice=cid;
                return emptyChoice;
            }




            $scope.save = function (choice_id, part_id)
            {
                if($scope.question.isMultiple){
                    var identifier = choice_id+'-'+part_id;
                }else{
                    var identifier = part_id;
                }

                var newAnswer = $scope.indexedAnswers[identifier];
                newAnswer.valueChoice=choice_id;

                // if id is setted, that means it has just been removed (click event)
                if (newAnswer.id && !$scope.question.isMultiple) {
                    newAnswer.put();
                }else if (newAnswer.id && $scope.question.isMultiple) {
                    newAnswer.remove().then(function(){
                        $scope.indexedAnswers[identifier] = {
                            questionnaire : Number($scope.question.parentResource.id),
                            part : part_id,
                            valueChoice:choice_id,
                            question : $scope.question.id
                        };

                    });

                // if don't exists -> create
                } else if (!newAnswer.id) {
                    Restangular.all('answer').post(newAnswer).then(function(answer){
                        $scope.indexedAnswers[identifier] = answer;

                    });
                }

            }






        }
    }
});
