angular.module('myApp').controller('Admin/RolesRequestsCtrl', function($scope, $http, $location, $routeParams, Restangular) {
    'use strict';

    $scope.surveyTypesList = [
        {
            value: 'jmp',
            display: 'JMP'
        }, {
            value: 'glaas',
            display: 'Glaas'
        }, {
            value: 'nsa',
            display: 'Nsa'
        }
    ];

    if ($routeParams.geonames && $routeParams.roles && $routeParams.types && $routeParams.user) {

        $scope.user = Restangular.one('user', $routeParams.user).get({fields: 'gravatar'}).$object;

        // /api/roles-request/getRequests filter results by current user
        $http.get('/api/roles-request/getRequests', {
            params: {
                geonames: $routeParams.geonames,
                roles: $routeParams.roles,
                types: $routeParams.types,
                user: $routeParams.user
            }
        }).success(function(users) {

            // Receive two sets who contain each a list of user_questionnaire and user_survey grouped by questionnaire (and geoname)
            // these are all user_questionnaire and user_survey that correspond to application, and on which current logged user has rights.

            // relations on which admin has rights
            $scope.adminRelations = users.admin;

            // relations on which applicant has rights (used to inform admin that some of the applicated roles already have been granted by someone else)
            $scope.applicantRelations = users.applicant;
        });
    }

    $scope.createRelation = function(userId, questionnaireId, roleId, geonameId) {

        var userQuestionnaire = {
            user: userId,
            questionnaire: questionnaireId,
            role: roleId
        };

        Restangular.all("user_questionnaire").post(userQuestionnaire).then(function(data) {

            if (!$scope.applicantRelations[geonameId]) {
                $scope.applicantRelations[geonameId] = {
                    questionnaires: {}
                };
            }

            if (!$scope.applicantRelations[geonameId].questionnaires[questionnaireId]) {
                $scope.applicantRelations[geonameId].questionnaires[questionnaireId] = {
                    roles: {}
                };
            }

            if (!$scope.applicantRelations[geonameId].questionnaires[questionnaireId].roles[roleId]) {
                $scope.applicantRelations[geonameId].questionnaires[questionnaireId].roles[roleId] = {};
            }

            data.modifier = {
                name: 'you'
            };

            data.type = "user_questionnaire";

            $scope.applicantRelations[geonameId].questionnaires[questionnaireId].roles[roleId] = {
                userRelation: data
            };
        });
    };

    $scope.revokeAccess = function(questionnaireId, roleId, geonameId) {
        var userRelation = $scope.applicantRelations[geonameId].questionnaires[questionnaireId].roles[roleId].userRelation;
        Restangular.restangularizeElement(null, userRelation, userRelation.type);

        userRelation.remove().then(function() {
            delete ($scope.applicantRelations[geonameId].questionnaires[questionnaireId].roles[roleId]);
        });
    };

});
