/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal) {
    "use strict";

	// Default redirect
	var returnUrl = '/';
	var returnTab = '';

	$scope.sending = false;
	$scope.addBtnChoice = false;

//	$scope.types = [
//		{text: 'Info', value: 'info'},
//		{text: 'Multiple Choice Question', value: 'choice'},
//		//{text: 'Percentage', value: 'percentage'}
//	];


	$scope.percentages = [
		{text: '100%', value:'1.000'},
		{text: '90%', value:'0.900'},
		{text: '80%', value:'0.800'},
		{text: '70%', value:'0.700'},
		{text: '60%', value:'0.600'},
		{text: '50%', value:'0.500'},
		{text: '40%', value:'0.400'},
		{text: '30%', value:'0.300'},
		{text: '20%', value:'0.200'},
		{text: '10%', value:'0.100'},
		{text: '0%', value:'0.000'},
		{text: 'Unknown', value:null},
	];


    $scope.initChoices = function(){

		if($scope.question.type == 'info' ){ // hide choices zone
			$scope.question.choices = [];
			$scope.addBtnChoice = false;
		}
		else if($scope.question.type == 'choice' && ( !$scope.question.choices || $scope.question.choices.length == 0) ){
			$scope.question.choices = [{}];
			$scope.addBtnChoice = true;
		}
		else if($scope.question.type == 'choice' &&  $scope.question.choices.length > 0  ){
			$scope.addBtnChoice = true;
		}
    }


	$scope.addChoice = function(){
		$scope.question.choices.push({});
	}


	$scope.deleteChoice = function(index){
		$scope.question.choices.splice(index,1);
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
        if ($scope.question.id)
		{
                $scope.question.put({fields: 'metadata,filter,survey,type,choices,parts'}).then(function(question)
				{
					$scope.sending = false;
					$scope.question= question;
					$scope.initChoices();
					if (redirectAfterSave) {
						redirect();
					}
				});
        }
		else
		{
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


    // Try loading question if possible...
    if ($routeParams.id) {
        Restangular.one('question', $routeParams.id).get({fields: 'metadata,filter,survey,type,choices,parts'}).then(function(question) {
            $scope.question = question;
			$scope.initChoices();
        });
		Restangular.all('part', $routeParams.id).getList().then(function(parts) {
			$scope.parts = parts;
		});

        Restangular.all('questionType', $routeParams.id).getList().then(function(types) {
            $scope.types = types;
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

