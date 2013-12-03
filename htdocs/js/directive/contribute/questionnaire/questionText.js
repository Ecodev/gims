angular.module('myApp.directives').directive('gimsTextQuestion', function (QuestionAssistant) {
    return {
        restrict: 'E',
        scope:{
            index:'=',
            question:'='
        },
        template:
            "<ng-form name='innerQuestionForm'> " +
            '<div ng-repeat="part in question.parts" ng-class="{\'col-md-12\': question.parts.length==1, \'col-md-6\': question.parts.length==2, \'col-md-4\': question.parts.length==3}">' +
            "     <div ng-switch='part.name'>" +
            "           <div ng-switch-when='Total'>National</div>"+
            "           <div ng-switch-when='Urban'>Urban</div>"+
            "           <div ng-switch-when='Rural'>Rural</div>"+
            "     </div>"+
            "     <textarea ng-required='question.isCompulsory' name='text' ng-model='index[question.id+\"-\"+part.id].valueText' ng-blur='save(question,part)'  id='numerical-{{question.id}}-{{part.id}}' ng-disabled='saving'></textarea>"+
            "</div>"+
            "<div class='col-md-12'>"+
            "   <span ng-show='question.isCompulsory' class='badge' ng-class=\"{'badge-danger':question.statusCode==1, 'badge-success':question.statusCode==3}\">Required</span>"+
            "   <span ng-show='!question.isCompulsory' class='badge' ng-class=\"{'badge-warning':question.statusCode==2, 'badge-success':question.statusCode==3}\">Optional</span>"+
            "</div>"+
            "</ng-form>",

        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.saving=false;
            $scope.save = function (question, part)
            {
                $scope.saving=true;
                var newAnswer = $scope.index[question.id+"-"+part.id];

                // if exists but value not empty -> update
                if (newAnswer.id && newAnswer.valueText) {
                    newAnswer.put().then(function(){
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                // if dont exists -> create
                } else if (!newAnswer.id && newAnswer.valueText) {
                    Restangular.all('answer').post(newAnswer).then(function(answer){
                        $scope.index[question.id+"-"+part.id] = answer;
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                // if exists but empty -> remove
                } else if (newAnswer.id && (!newAnswer.valueText || newAnswer.valueText=="")) {
                    newAnswer.remove().then(function(){
                        $scope.index[question.id+"-"+part.id] = QuestionAssistant.getEmptyTextAnswer(question, part.id);
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });
                } else {
                    $scope.saving=false;
                }
            };

        }
    }
});
