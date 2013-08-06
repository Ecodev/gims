/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal) {
    "use strict";

	// Default redirect
	var returnUrl = '/';
	var returnTab = '';


	$scope.sending = false;
	//$scope.part = 'null'

	$scope.types = [
		{text: 'Info', value: 'info'},
		{text: 'Numerical (3 answers)', value: 'numerical3'},
		{text: 'Numerical (4 answers)', value: 'numerical4'},
		{text: 'Numerical (5 answers)', value: 'numerical5'},
		{text: 'Percentage', value: 'percentage'}
	];

	/*
	$scope.parts = [
		{text:'Urban', value:'1'},
		{text:'Rural', value:'2'},
		{text:'Total', value:'null'}
	];
	*/

	$scope.percentages = [
		{text: '100%', value:'1'},
		{text: '90%', value:'0.9'},
		{text: '80%', value:'0.8'},
		{text: '70%', value:'0.7'},
		{text: '60%', value:'0.6'},
		{text: '50%', value:'0.5'},
		{text: '40%', value:'0.4'},
		{text: '30%', value:'0.3'},
		{text: '20%', value:'0.2'},
		{text: '10%', value:'0.1'},
		{text: '0%', value:'0'},
		{text: 'Unknown', value:'null'},
	];


    $scope.updateNbQuestions = function()
    {
        var nbChoices = Number($scope.question.type.replace('numerical', ''));
        $scope.question.choices = [];
        if( !isNaN(nbChoices) )
           for( var i=0; i < nbChoices; i++)
               $scope.question.choices.push({});
    }



    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
        returnTab = $routeParams.returnTab;
        $('.survey-question-link').attr('href', returnUrl + '#' + returnTab);
    }


    var redirect = function() {
        $location.path(returnUrl).search({}).hash(returnTab);
    };

    $scope.cancel = function () {
        redirect();
    };

    $scope.saveAndClose = function () {
        this.save(true);
    };
    $scope.save = function (redirectAfterSave)
	{
        $scope.sending = true;

        // First case is for update a question, second is for creating
        $scope.question.filter = $scope.question.filter.id;
        if ($scope.question.id) {
                $scope.question.put({fields: 'metadata,filter,survey,type,choices,parts'}).then(function(question) {
                $scope.sending = false;
                $scope.question= question;
				$scope.updateNbQuestions();
                if (redirectAfterSave) {
                    redirect();
                }
            });
        } else {
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

    // Delete a question
    $scope.delete = function () {
        Modal.confirmDelete($scope.question, {label: $scope.question.name, returnUrl: $location.search().returnUrl});
    };

    // Create object with default value
    $scope.question = {sorting: 0, type: 'numerical3', choices : [{name:'John', age:25}, {name:'Mary', age:28}]};

    // Try loading question if possible...
    if ($routeParams.id) {
        Restangular.one('question', $routeParams.id).get({fields: 'metadata,filter,survey,type,choices,parts'}).then(function(question) {
            $scope.question = question;
			$scope.updateNbQuestions();
        });
		Restangular.all('part', $routeParams.id).getList().then(function(parts) {
			console.info(parts);
			$scope.parts = parts;
		});
    }

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        Restangular.one('survey', params.survey).get().then(function (survey) {
            $scope.survey = survey;
        });
    }
});

