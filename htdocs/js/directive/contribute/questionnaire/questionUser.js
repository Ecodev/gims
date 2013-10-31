angular.module('myApp.directives').directive('gimsUserQuestion', function (QuestionAssistant) {
    return {
        restrict: 'E',
        template: "<ng-form name='innerQuestionForm'> " +
                        "<div class='span12'>" +
                        "<div ng-repeat='part in question.parts' class='span4'>"+
                        "     <div ng-switch='part.name'>" +
                        "           <div ng-switch-when='Total'>National</div>"+
                        "           <div ng-switch-when='Urban'>Urban</div>"+
                        "           <div ng-switch-when='Rural'>Rural</div>"+
                        "     </div>"+
                        "     <gims-select class='span8' name='user' api='user' model='index[question.id+\"-\"+part.id].valueUser' id='numerical-{{question.id}}-{{part.id}}'  disabled='saving'></gims-select>"+
                        "     <button class='span3 btn btn-danger btn-xs' ng-click='resetUser(question, part)'><i class='icon-trash'></i></button>"+
                        "</div></div><br/>"+
                        "<span ng-show='question.isCompulsory' class='badge' ng-class=\"{'badge-important':question.statusCode==1, 'badge-success':question.statusCode==3}\">Required</span>"+
                        "<span ng-show='!question.isCompulsory' class='badge' ng-class=\"{'badge-warning':question.statusCode==2, 'badge-success':question.statusCode==3}\">Optional</span>"+
                        "<br/><br/><gims-add-user callback='updateUsersList()'></gims-add-user></div>"+
            "</ng-form>" +
            "",
        scope:{
            index:'=',
            question:'='
        },
        link: function(scope, element, attrs) {
            // nothing to do ?
        },
        controller: function($scope, $location, $resource, $routeParams, Restangular, Modal)
        {
            $scope.saving=false;
            angular.forEach($scope.question.parts, function(part) {
                var question = $scope.question;
                $scope.$watch(function() {
                        return $scope.index[question.id+'-'+part.id].valueUser; // only way to $watch on some array cells
                }, function(newUser, oldUser){
                    // if different users
                    if(newUser !== oldUser) {
                        // if one user has is null or has no id -> save
                        if( !newUser || !oldUser || !newUser.id || !oldUser.id){
                            $scope.save(question, part);

                        // users can be different but have the same id (happens when create call returne restangularized object)
                        // avoids update after update -> ids have to be different
                        }else if (newUser && oldUser && newUser.id && newUser.id && newUser.id != oldUser.id){
                            $scope.save(question, part);
                        }
                    }
                });
            },true);

            $scope.updateUsersList = function()
            {

                console.info('update user list');
            }

            $scope.save = function(question, part)
            {
                $scope.saving=true;
                var newAnswer = $scope.index[question.id+"-"+part.id];

                // if exists but value not empty -> update
                if (newAnswer.id && newAnswer.valueUser) {
                    newAnswer.put().then(function(){
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                // if dont exists -> create
                } else if (!newAnswer.id && newAnswer.valueUser) {
                    Restangular.all('answer').post(newAnswer).then(function(answer){
                        $scope.index[question.id+"-"+part.id] = answer;
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                // if exists but empty -> remove
                } else if (newAnswer.id && (!newAnswer.valueUser || newAnswer.valueUser == "")) {
                    newAnswer.remove().then(function(){
                        $scope.index[question.id+"-"+part.id] = QuestionAssistant.getEmptyTextAnswer(question, part.id);
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });
                } else {
                    $scope.saving=false;
                }
            };

            $scope.resetUser = function(question, part)
            {
                $scope.index[question.id+"-"+part.id].valueUser = null;
            }
        }
    }
});
