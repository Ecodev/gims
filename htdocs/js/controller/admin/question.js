/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function($scope, $routeParams, $location, Restangular, Modal) {
    "use strict";

    // Default redirect
    var questionFields = {fields: 'metadata,filter,survey,type,choices,parts,chapter,isCompulsory,isMultiple,isFinal,description,questions'};
    var returnUrl = '/';

    $scope.sending = false;

    // @TODO : manage value null and integer value
    $scope.percentages = [
        {text: '100%', value: '1.000'},
        {text: '90%',  value: '0.900'},
        {text: '80%',  value: '0.800'},
        {text: '70%',  value: '0.700'},
        {text: '60%',  value: '0.600'},
        {text: '50%',  value: '0.500'},
        {text: '40%',  value: '0.400'},
        {text: '30%',  value: '0.300'},
        {text: '20%',  value: '0.200'},
        {text: '10%',  value: '0.100'},
        {text: '0%',   value: '0.000'}
    ];

    $scope.params = {fields: 'paths'};

    $scope.select2Template = "" +
            "<div>" +
            "<div class='col-sm-4 col-md-4 select-label select-label-with-icon'>" +
            "    <i class='fa fa-gims-filter'></i> [[item.name]]" +
            "</div>" +
            "<div class='col-sm-7 col-md-7'>" +
            "    <small>" +
            "       [[_.map(item.paths, function(path){return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]" +
            "    </small>" +
            "</div>" +
            "<div class='col-sm-1 col-md-1 hide-in-results' >" +
            "    <a class='btn btn-default btn-sm' href='/admin/filter/edit/[[item.id]][[$scope.currentContextElement]]'>" +
            "        <i class='fa fa-pencil'></i>" +
            "    </a>" +
            "</div>" +
            "<div class='clearfix'></div>" +
            "</div>";

    $scope.compulsory = [
        {text: 'Optional', value: 0},
        {text: 'Compulsory', value: 1}
    ];

    $scope.multiple = [
        {text: 'Single choice', value: false},
        {text: 'Multiple choices', value: true}
    ];

    $scope.initChoices = function()
    {
        $scope.isChoices = false;
        $scope.isChapter = false;

        if ($scope.question.type == 'Choice') {
            $scope.isChoices = true;

            // If we have no choices list at all, then we save the existing
            // question with its new type to reload actual list of choices
            if (!$scope.question.choices && $scope.question.id) {
                $scope.save();
            }
            // Otherwise, if the question is new and no choices exists, we inject an empty one
            else if (!$scope.question.choices || $scope.question.choices.length === 0) {
                $scope.question.choices = [{}];
            }
        }
        if ($scope.question.type == 'Chapter') {
            $scope.isChapter = true;
        }
    };

    $scope.removeChapter = function()
    {
        $scope.question.chapter = null;
    };

    $scope.addOption = function() {
        $scope.question.choices.push({});
    };


    $scope.deleteOption = function(index) {
        $scope.question.choices.splice(index, 1);
    };


    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }


    var redirect = function() {
        $location.url(returnUrl);
    };

    $scope.cancel = function() {
        redirect();
    };

    $scope.saveAndClose = function() {
        this.save(true);
    };
    $scope.save = function(redirectAfterSave) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.question.filter) {
            $scope.question.filter = $scope.question.filter.id;
        }
        
        if ($scope.question.chapter) {
            $scope.question.chapter = $scope.question.chapter.id;
        }

        if ($scope.question.id) {

            // if change type from Choice to another, remove choices from DB
            if ($scope.question.type != 'Choice' && $scope.question.choices && $scope.question.choices.length > 0) {
                delete $scope.question.choices;
            }

            $scope.question.put(questionFields).then(function(question) {
                $scope.sending = false;
                $scope.question = question;
                $scope.questions = question.questions;
                $scope.initChoices();
                if (redirectAfterSave) {
                    redirect();
                }
            });
        }
        else {
            $scope.question.survey = $routeParams.survey;

            delete $scope.question.sorting; // let the server define the sorting value
            Restangular.all('question').post($scope.question).then(function(question) {
                $scope.sending = false;

                if (redirectAfterSave) {
                    redirect();
                } else {
                    // redirect to edit URL
                    $location.path(sprintf('admin/question/edit/%s', question.id));
                }
            });
        }
    };

    $scope.chapterList = [];

    $scope.setParentQuestions = function (survey_id) {
        Restangular.one('survey', survey_id).all('question').getList({fields:'chapter,level,type',perPage:1000}).then(function (questions) {

            angular.forEach(questions, function(question) {
                if (question.type == 'Chapter') {
                    var spacer = '';
                    for (var i = 1; i <= question.level; i++) {
                        spacer += "-- ";
                    }
                    $scope.chapterList.push({id: question.id, name: spacer + " " + question.name});
                }
            });
        });
    };



    // Delete a question
    $scope.delete = function() {
        Modal.confirmDelete($scope.question, {label: $scope.question.name, returnUrl: $location.search().returnUrl});
    };


    // Try loading question if possible...
    if ($routeParams.id) {
        Restangular.one('question', $routeParams.id).get(questionFields).then(function(question) {
            $scope.question = question;
            $scope.survey = question.survey;
            $scope.setParentQuestions($scope.question.survey.id);
            $scope.initChoices();

            angular.forEach(question.questions, function(question) {
                question = Restangular.restangularizeElement(null, question, 'question');
            });
            $scope.questions = question.questions;

        });

    } else {
        $scope.question = {};
        $scope.setParentQuestions($routeParams.survey);
    }





    Restangular.all('questionType').getList().then(function(types) {
        $scope.types = types;
    });

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        $scope.survey = Restangular.one('survey', params.survey).get().$object;
    }
});

