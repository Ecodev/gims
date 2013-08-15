/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal) {
    "use strict";

    // Default redirect
    var questionFields = {fields: 'metadata,filter,survey,type,choices,parts,chapter,compulsory,multiple,questions'};
    var returnUrl = '/';
    var returnTab = '';

    $scope.sending = false;
    $scope.addBtnChoice = false;


    // @TODO : manage value null and integer value
    $scope.percentages = [
        {text: '100%', value: '1.000'},
        {text: '90%', value: '0.900'},
        {text: '80%', value: '0.800'},
        {text: '70%', value: '0.700'},
        {text: '60%', value: '0.600'},
        {text: '50%', value: '0.500'},
        {text: '40%', value: '0.400'},
        {text: '30%', value: '0.300'},
        {text: '20%', value: '0.200'},
        {text: '10%', value: '0.100'},
        {text: '0%', value: '0.000'},
        {text: 'Unknown', value: null},
    ];

    $scope.compulsory = [
        {text: 'Optional', value: 0},
        {text: 'Compulsory', value: 1},
    ];

    $scope.multiple = [
        {text: 'Single choice', value: false},
        {text: 'Multiple choices', value: true},
    ];



    $scope.initChoices = function () {

        $scope.addBtnChoice = false;
        $scope.isChoices = false;
        $scope.isChapter = false;

        if ($scope.question.type == 'Choice') {
            $scope.isChoices = true;
           if (!$scope.question.choices || $scope.question.choices.length == 0)
                $scope.question.choices = [{}];
            $scope.addBtnChoice = true;
        }
        if ($scope.question.type == 'Chapter') {
            $scope.isChapter=true;
        }
//        else if ($scope.question.type == 'text') {
//
//        }
//        else if ($scope.question.type == 'numerical') {
//
//        }



    }


    $scope.addOption = function () {
        $scope.question.choices.push({});
    }


    $scope.deleteOption = function (index) {
        $scope.question.choices.splice(index, 1);
    }


    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
        returnTab = $routeParams.returnTab;
        $('.survey-question-link').attr('href', returnUrl + '#' + returnTab);
    }


    var redirect = function () {
        $location.path(returnUrl).search({}).hash(returnTab);
    };

    $scope.cancel = function () {
        redirect();
    };

    $scope.saveAndClose = function () {
        this.save(true);
    };
    $scope.save = function (redirectAfterSave) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.question.filter) $scope.question.filter = $scope.question.filter.id;
        if ($scope.question.chapter) $scope.question.chapter = $scope.question.chapter.id;
        if ($scope.question.id) {
            $scope.question.put(questionFields).then(function (question) {
                $scope.sending = false;
                $scope.question = question;
                $scope.initChoices();
                if (redirectAfterSave) {
                    redirect();
                }
            });
        }
        else {
            $scope.question.survey = $routeParams.survey;

            delete $scope.question.sorting; // let the server define the sorting value
            Restangular.all('question').post($scope.question).then(function (question) {
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


    $scope.setParentQuestions = function (survey_id) {
        Restangular.one('survey', survey_id).get({fields:'questions,questions.type'}).then(function (survey) {
            var chapterList = [];   // @TODO : find a way to add "none" -> dont work @ save : [{id:0,name:'None'}];

            for (var question in survey.questions) {
                question = survey.questions[question];
                if (question.type == 'Chapter') {
                    chapterList.push({id:question.id, name:question.name});
                }
            }
            $scope.chapterList = chapterList;

        });
    }



    // Delete a question
    $scope.delete = function () {
        Modal.confirmDelete($scope.question, {label: $scope.question.name, returnUrl: $location.search().returnUrl});
    };


    // Try loading question if possible...
    if ($routeParams.id) {
        Restangular.one('question', $routeParams.id).get(questionFields).then(function (question) {
            $scope.question = question;
            $scope.setParentQuestions($scope.question.survey.id);
            $scope.initChoices();
        });
    } else {
        $scope.question = {};
        $scope.setParentQuestions($routeParams.survey);
    }





    Restangular.all('questionType').getList().then(function (types) {
        $scope.types = types;
    });

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        Restangular.one('survey', params.survey).get().then(function (survey) {
            $scope.survey = survey;
        });
    }




});

