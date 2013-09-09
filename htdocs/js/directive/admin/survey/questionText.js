angular.module('myApp.directives').directive('gimsTextQuestion', function () {
    return {
        restrict: 'E',
        template: "<div class='span4' ng-repeat='part in question.parts'>"+
                "     <label for='numerical-{{question.id}}-{{part.id}}'>"+
                "         <div ng-switch='part.name'>" +
                "               <div ng-switch-when='Total'>Urban + Rural</div>"+
                "               <div ng-switch-when='Urban'>Urban</div>"+
                "               <div ng-switch-when='Rural'>Rural</div>"+
                "         </div>"+
                "         <textarea class='span12' ng-model='indexedAnswers[part.id].valueText' ng-blur='save(part.id,$event)' name='numerical-{{question.id}}-{{part.id}}' id='numerical-{{question.id}}-{{part.id}}'></textarea>"+
                "     </label>"+
                " </div>",
                //"<div class='span11'><pre>{{indexedAnswers|json}}</pre></div>",
        scope:{
            question:'='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.indexedAnswers = {};

            $scope.$watch('question', function (question, oldQuestion)
            {
                if( question == oldQuestion ){
                    angular.forEach(question.parts, function(part) {
                        $scope.indexedAnswers[part.id] = $scope.findAnswer(question, part.id);
                    });
                }
            });

            $scope.findAnswer = function (question, pid)
            {
                for(var key in question.answers){
                    var testedAnswer = question.answers[key];
                    if (testedAnswer.part && testedAnswer.part.id==pid) {
                        return testedAnswer;
                    }
                }
                return {
                    questionnaire : Number(question.parentResource.id),
                    part : pid,
                    question : question.id
                }
            }


            $scope.save = function (part_id, event)
            {
                if (event) {
                    var newAnswer = $scope.indexedAnswers[part_id];

                    // if exists but value not empty -> update
                    if (newAnswer.id && newAnswer.valueText) {
                        newAnswer.put();

                        // if dont exists -> create
                    } else if (!newAnswer.id && newAnswer.valueText) {

                        Restangular.all('answer').post(newAnswer).then(function(answer){
                            $scope.indexedAnswers[part_id] = answer;
                        });

                        // if exists and empty -> remove
                    } else if (newAnswer.id && newAnswer.valueText=="" ){
                        newAnswer.remove().then(function(){
                            $scope.indexedAnswers[part_id] = {
                                questionnaire : Number($scope.question.parentResource.id),
                                part : part_id,
                                question : $scope.question.id
                            };
                        });
                    }
                }
            }




        }
    }
});
