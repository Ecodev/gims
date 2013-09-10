angular.module('myApp.directives').directive('gimsNumQuestion', function () {
    return {
        restrict: 'E',
        template: "<div class='span2' ng-repeat='part in question.parts'>"+
                "     <label for='numerical-{{question.id}}-{{part.id}}'>"+
                "         <div ng-switch='part.name'>" +
                "               <div ng-switch-when='Total'>National</div>"+
                "               <div ng-switch-when='Urban'>Urban</div>"+
                "               <div ng-switch-when='Rural'>Rural</div>"+
                "         </div>"+
                "         <input class='span12' type='number' ng-model='index[question.id+\"-\"+part.id].valueAbsolute' ng-blur='save(question.id,part.id,$event)' name='numerical-{{question.id}}-{{part.id}}' id='numerical-{{question.id}}-{{part.id}}'/>"+
                "     </label>"+
                " </div>",

        scope:{
            index:'=',
            question:'='
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.$watch('question', function (question, oldQuestion)
            {
                if( question===oldQuestion ){
                    angular.forEach(question.parts, function(part) {
                        if (!$scope.index[question.id+'-'+part.id] || ($scope.index[question.id+'-'+part.id] && !$scope.index[question.id+'-'+part.id].valueAbsolute)) {
                            $scope.index[question.id+"-"+part.id] = $scope.findAnswer(question, part.id);
                        }
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








            $scope.save = function (question_id, part_id,event)
            {
                if (event) {
                    var newAnswer = $scope.index[question_id+"-"+part_id];

                    // if exists but value not empty -> update
                    if (newAnswer.id && newAnswer.valueAbsolute) {
                        newAnswer.put();

                        // if dont exists -> create
                    } else if (!newAnswer.id && newAnswer.valueAbsolute) {
                        Restangular.all('answer').post(newAnswer).then(function(answer){
                            $scope.index[question_id+"-"+part_id] = answer;
                        });

                        // if exists and empty -> remove
                    } else if (newAnswer.id && newAnswer.valueAbsolute===null ){
                        newAnswer.remove().then(function(){
                            $scope.index[question_id+"-"+part_id] = {
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

