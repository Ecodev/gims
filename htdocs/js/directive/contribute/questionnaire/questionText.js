angular.module('myApp.directives').directive('gimsTextQuestion', function () {
    return {
        restrict: 'E',
        template: "<div ng-repeat='part in question.parts' ng-class='{span12:question.parts.length==1, span6:question.parts.length==2, span4:question.parts.length==3}'>"+
                "     <label for='numerical-{{question.id}}-{{part.id}}'>"+
                "         <div ng-switch='part.name'>" +
                "               <div ng-switch-when='Total'>National</div>"+
                "               <div ng-switch-when='Urban'>Urban</div>"+
                "               <div ng-switch-when='Rural'>Rural</div>"+
                "         </div>"+
                "         <textarea class='span12' name='numerical-{{question.id}}-{{part.id}}' ng-model='index[question.id+\"-\"+part.id].valueText' ng-blur='save(question.id,part.id,$event)'  id='numerical-{{question.id}}-{{part.id}}' ng-disabled='saving'></textarea>"+
                "     </label>"+
                " </div>",
        scope:{
            index:'=',
            question:'='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.$watch('question', function (question)
            {
                angular.forEach(question.parts, function(part) {
                    if (!$scope.index[question.id+'-'+part.id] || ($scope.index[question.id+'-'+part.id] && !$scope.index[question.id+'-'+part.id].valueText)) {
                        $scope.index[question.id+"-"+part.id] = $scope.findAnswer(question, part.id);
                    }
                });
            });

            $scope.findAnswer = function (question, pid)
            {
                for(var key in question.answers){
                    var testedAnswer = question.answers[key];
                    if (testedAnswer.part && testedAnswer.part.id==pid) {
                        delete testedAnswer.valueChoice;
                        return testedAnswer;
                    }
                }
                return {
                    questionnaire : Number(question.parentResource.id),
                    part : pid,
                    question : question.id
                };
            };

            $scope.saving=false;
            $scope.save = function (question_id, part_id, event)
            {
                $scope.saving=true;
                var newAnswer = $scope.index[question_id+"-"+part_id];

                // if exists but value not empty -> update
                if (newAnswer.id && newAnswer.valueText) {
                    newAnswer.put().then(function(){$scope.saving=false;});

                // if dont exists -> create
                } else if (!newAnswer.id && newAnswer.valueText) {
                    Restangular.all('answer').post(newAnswer).then(function(answer){
                        $scope.index[question_id+"-"+part_id] = answer;
                        $scope.saving=false;
                    });

                // if exists and empty -> remove
                } else if (newAnswer.id && newAnswer.valueText=="" ){
                    newAnswer.remove().then(function(){
                        $scope.index[question_id+"-"+part_id] = {
                            questionnaire : Number($scope.question.parentResource.id),
                            part : part_id,
                            question : $scope.question.id
                        };
                        $scope.saving=false;
                    });
                }

            };




        }
    }
});
