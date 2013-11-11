angular.module('myApp.directives').directive('gimsUserQuestion', function (QuestionAssistant) {
    return {
        restrict: 'E',
        template: "<ng-form name='innerQuestionForm'> " +
                        "<div class='row-fluid show-grid'>" +
                        "   <div ng-repeat='part in question.parts' class='span4'>"+
                        "        <div ng-switch='part.name'>" +
                        "              <div ng-switch-when='Total'>National</div>"+
                        "              <div ng-switch-when='Urban'>Urban</div>"+
                        "              <div ng-switch-when='Rural'>Rural</div>"+
                        "        </div>"+
                        "        <gims-select style='width:100%' name='user' api='user' model='index[question.id+\"-\"+part.id].valueUser' id='numerical-{{question.id}}-{{part.id}}' disabled='saving'></gims-select>"+
                        "        <div class='pull-right' style='margin-top:5px'>"+
                        "           <button class='btn' ng-click='openModal(question, part, true)' ><i class='icon-plus'></i></button>"+
                        "           <button class='btn' ng-click='openModal(question, part, false)' ng-show='index[question.id+\"-\"+part.id].valueUser && index[question.id+\"-\"+part.id].valueUser.lastLogin'><i class='icon-eye-open'></i></button>"+
                        "           <button class='btn' ng-click='openModal(question, part, false)' ng-show='index[question.id+\"-\"+part.id].valueUser && !index[question.id+\"-\"+part.id].valueUser.lastLogin'><i class='icon-edit'></i></button>"+
                        "           <button class='btn' ng-click='resetUser(question, part)' ng-show='index[question.id+\"-\"+part.id].valueUser'><i class='icon-trash'></i></button>"+
                        "        </div>"+
                        "        <div class='clearfix'></div>"+
                        "   </div>"+
                        "</div><br/>"+
                        "<div style='line-height:40px'>"+
                        "    <span ng-show='question.isCompulsory' class='badge' ng-class=\"{'badge-important':question.statusCode==1, 'badge-success':question.statusCode==3}\">Required</span>"+
                        "    <span ng-show='!question.isCompulsory' class='badge' ng-class=\"{'badge-warning':question.statusCode==2, 'badge-success':question.statusCode==3}\">Optional</span>"+
                        "<gims-add-user show-modal='showModal' user='modalUser'></gims-add-user>"+
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

            $scope.showModal = false;
            $scope.saving = false;

            angular.forEach($scope.question.parts, function(part) {
                var question = $scope.question;
                $scope.$watch(function() {
                        return $scope.index[question.id+'-'+part.id].valueUser; // only way to $watch on some array cells
                }, function(newUser, oldUser){
                    if (!oldUser && newUser && newUser.id ||
                        oldUser && oldUser.id && !newUser ||
                        oldUser && newUser && oldUser.id != newUser.id
                        ){
                        $scope.save(question, part);
                    }
                });
            },true);



            $scope.openModal = function(question, part, createUser)
            {
                $scope.showModal = true;

                var modalUserListener = $scope.$watch('modalUser', function(newModalUser) { // $watch return a function that can stop listening
                    $scope.index[question.id+"-"+part.id].valueUser = newModalUser;
                    modalUserListener(); // stop the listener cause we don't want to add much listeners
                },true);

                if (createUser) {
                    $scope.modalUser = {};
                } else if ($scope.index[question.id + "-" + part.id].valueUser) {
                    $scope.modalUser = $scope.index[question.id + "-" + part.id].valueUser;
                }
            }


            $scope.save = function(question, part)
            {
                $scope.saving=true;
                var newAnswer = $scope.index[question.id + "-" + part.id];

                // if exists but value not empty -> update
                if (newAnswer.id && newAnswer.valueUser) {
                    newAnswer.put().then(function(){
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                // if dont exists -> create
                } else if (!newAnswer.id && newAnswer.valueUser) {
                    Restangular.all('answer').post(newAnswer).then(function(answer){
                        $scope.index[question.id + "-" + part.id] = answer;
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                // if exists but empty -> remove
                } else if (newAnswer.id && (!newAnswer.valueUser || newAnswer.valueUser == "")) {
                    newAnswer.remove().then(function(){
                        $scope.index[question.id + "-" + part.id] = QuestionAssistant.getEmptyTextAnswer(question, part.id);
                        $scope.saving=false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });
                } else {
                    $scope.saving=false;
                }
            };

            $scope.resetUser = function(question, part)
            {
                $scope.index[question.id + "-" + part.id].valueUser = null;
            }
        }
    }
});
