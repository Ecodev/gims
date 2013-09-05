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
                    "               <input type='checkbox' name='{{part.id}}-{{choice.id}}'  ng-model='indexedAnswers[choice.id+\"-\"+part.id].value' ng-blur='save(choice.id,part.id,choice.id)'/>"+
                    "           </div>"+
                    "           <div ng-switch-when='false'>" +
                    "               <input type='radio' name='{{part.id}}-{{question.id}}'/>"+
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
                    for(var j=0; j<question.choices.length; j++ ){
                        var question_identifier = question.choices[j].id +"-"+ question.parts[i].id;
                        $scope.indexedAnswers[question_identifier] = $scope.findAnswer(question.choices[j].id, question.parts[i].id);
                    }
                }
            });

            $scope.save = function(choice_id, part_id){
                console.info('save - qid:'+$scope.question.id+" cid:"+choice_id+" pid:"+part_id);
            }


            $scope.findAnswer = function(cid, pid)
            {
                for(var i=0; i<$scope.question.answers.length; i++ ){
                    var testedAnswer = $scope.question.answers[i];
                    if(testedAnswer.choice.id==cid && testedAnswer.part.id==pid) return testedAnswer;
                }
                return {};
            }


        }
    }
});
