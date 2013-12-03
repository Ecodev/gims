angular.module('myApp.directives').directive('gimsUserQuestion', function(QuestionAssistant) {
    return {
        restrict: 'E',
        template: "<ng-form name='innerQuestionForm'> " +
                //"<div class='row'>" +
                "   <div ng-repeat='part in question.parts' class='col-md-4'>" +
                "        <div ng-switch='part.name'>" +
                "              <div ng-switch-when='Total'>National</div>" +
                "              <div ng-switch-when='Urban'>Urban</div>" +
                "              <div ng-switch-when='Rural'>Rural</div>" +
                "        </div>" +
                "        <gims-select style='width:100%' name='user' api='user' model='index[question.id+\"-\"+part.id].valueUser' id='numerical-{{question.id}}-{{part.id}}' disabled='saving'></gims-select>" +
                "        <div class='pull-right' style='margin-top:5px'>" +
                "           <button class='btn btn-default' ng-click='openUserModal(question, part, true)' ><i class='fa fa-plus'></i></button>" +
                "           <button class='btn btn-default' ng-click='openUserModal(question, part, false)' ng-show='index[question.id+\"-\"+part.id].valueUser && index[question.id+\"-\"+part.id].valueUser.lastLogin'><i class='fa fa-eye'></i></button>" +
                "           <button class='btn btn-default' ng-click='openUserModal(question, part, false)' ng-show='index[question.id+\"-\"+part.id].valueUser && !index[question.id+\"-\"+part.id].valueUser.lastLogin'><i class='fa fa-pencil'></i></button>" +
                "           <button class='btn btn-default' ng-click='resetUser(question, part)' ng-show='index[question.id+\"-\"+part.id].valueUser'><i class='fa fa-trash-o'></i></button>" +
                "        </div>" +
                "        <div class='clearfix'></div>" +
                "   </div>" +
                //"</div>" +
                "<div style='line-height:40px'>" +
                "    <span ng-show='question.isCompulsory' class='badge' ng-class=\"{'badge-danger':question.statusCode==1, 'badge-success':question.statusCode==3}\">Required</span>" +
                "    <span ng-show='!question.isCompulsory' class='badge' ng-class=\"{'badge-warning':question.statusCode==2, 'badge-success':question.statusCode==3}\">Optional</span>" +
                "</ng-form>" +
                "",
        scope: {
            index: '=',
            question: '='
        },
        link: function(scope, element, attrs) {
            // nothing to do ?
        },
        controller: function($scope, Restangular, UserModal)
        {
            $scope.saving = false;

            angular.forEach($scope.question.parts, function(part) {
                var question = $scope.question;
                $scope.$watch(function() {
                    return $scope.index[question.id + '-' + part.id].valueUser; // only way to $watch on some array cells
                }, function(newUser, oldUser) {
                    if (!oldUser && newUser && newUser.id ||
                            oldUser && oldUser.id && !newUser ||
                            oldUser && newUser && oldUser.id != newUser.id
                            ) {
                        $scope.save(question, part);
                    }
                });
            }, true);



            $scope.openUserModal = function(question, part, createUser)
            {
                var key = question.id + "-" + part.id;
                var user = {};
                if (!createUser && $scope.index[key].valueUser) {
                    user = $scope.index[key].valueUser;
                }

                UserModal.editUser(user).then(function(newModalUser) {
                    $scope.index[key].valueUser = newModalUser;
                });
            };


            $scope.save = function(question, part)
            {
                $scope.saving = true;
                var newAnswer = $scope.index[question.id + "-" + part.id];

                // if exists but value not empty -> update
                if (newAnswer.id && newAnswer.valueUser) {
                    newAnswer.put().then(function() {
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                    // if dont exists -> create
                } else if (!newAnswer.id && newAnswer.valueUser) {
                    Restangular.all('answer').post(newAnswer).then(function(answer) {
                        $scope.index[question.id + "-" + part.id] = answer;
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                    // if exists but empty -> remove
                } else if (newAnswer.id && (!newAnswer.valueUser || newAnswer.valueUser == "")) {
                    newAnswer.remove().then(function() {
                        $scope.index[question.id + "-" + part.id] = QuestionAssistant.getEmptyTextAnswer(question, part.id);
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });
                } else {
                    $scope.saving = false;
                }
            };

            $scope.resetUser = function(question, part)
            {
                $scope.index[question.id + "-" + part.id].valueUser = null;
            };
        }
    };
});
