angular.module('opalAdmin.controllers.question.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.edit', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// get current user id
		var user = Session.retrieveObject('user');
		var userId = user.id;

		$scope.changesMade = false;

		// initialize default variables & lists
		$scope.changesMade = false;
		$scope.question = {};

		// Initialize variables for holding selected answer type & group
		$scope.selectedAt = null;
		$scope.selectedGroup = null;

		// Filter lists initialized
		$scope.atFilterList = [];
		$scope.libFilterList = [];
		$scope.groupFilterList = [];
		$scope.atCatList = [];

		// Initialize search field variables
		$scope.atEntered = '';
		$scope.libEntered = '';
		$scope.catEntered = '';
		$scope.groupEntered = '';

		// assign functions
		$scope.searchAt = function (field) {
			$scope.atEntered = field;
		};
		$scope.searchLib = function (field) {
			$scope.libEntered = field;
		};
		$scope.searchCat = function (field) {
			$scope.catEntered = field;
		};
		$scope.searchGroup = function (field) {
			$scope.groupEntered = field;
		};

		// search function
		$scope.searchAtFilter = function (Filter) {
			var keyword = new RegExp($scope.atEntered, 'i');
			return !$scope.atEntered || keyword.test(Filter.name_EN);
		};
		$scope.searchLibFilter = function (Filter) {
			var keyword = new RegExp($scope.libEntered, 'i');
			return !$scope.libEntered || keyword.test(Filter.name_EN);
		};
		$scope.searchCatFilter = function (Filter) {
			var keyword = new RegExp($scope.catEntered, 'i');
			return !$scope.catEntered || keyword.test(Filter.category_EN);
		};
		$scope.searchGroupFilter = function (Filter) {
			var keyword = new RegExp($scope.groupEntered, 'i');
			return !$scope.groupEntered || keyword.test(Filter.name_EN);
		};

		// function to update selected group/at in view
		$scope.updateGroup = function (groupSelected) {
			$scope.changesMade = true; // Set changes made
			$scope.groupSelected_name_EN = groupSelected.name_EN;
			$scope.groupSelected_name_FR = groupSelected.name_FR;
		};

		$scope.updateAt = function (atSelected) {
			$scope.changesMade = true; // set changes made
			$scope.atSelected_name_EN = atSelected.name_EN;
			$scope.atSelected_name_FR = atSelected.name_FR;
		};

		/* Function for the "Processing" dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};

		// Show processing dialog on load
		$scope.showProcessingModal();

		// Call our API service to get the questionnaire details
		questionnaireCollectionService.getQuestionDetails($scope.currentQuestion.serNum).then(function (response) {
			// Assign value
			$scope.question = response.data;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function (response) {
			console.error('Error occurred getting question details:', response.status, response.data);
		});

		// Call our API service to get the list of existing answer types 
		questionnaireCollectionService.getAnswerTypes(userId).then(function (response) {
			$scope.atFilterList = response.data;
		}).catch(function (response){
			console.error('Error occurred getting answer types:', response.status, response.data);
		});

		// Call our API service to get the list of existing groups
		questionnaireCollectionService.getQuestionGroups(userId).then(function (response) {
			$scope.groupFilterList = response.data;
		}).catch(function (response){
			console.error('Error occurred getting question groups:', response.status, response.data);
		});

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.question.text_EN && $scope.question.text_FR && $scope.question.answertype_serNum && $scope.question.questiongroup_serNum && $scope.changesMade) {
				return true;
			}
			else
				return false;
		};

		// Function to set changes made to true
		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};

		// Submit changes
		$scope.updateQuestion = function () {

			if ($scope.checkForm()) {
				// update last_updated_by
				$scope.question.last_updated_by = userId;

				// Submit form
				$.ajax({
					type: "POST",
					url: "php/questionnaire/update.question.php",
					data: $scope.question,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.question.text_EN + "/ " + $scope.question.text_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}

						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			}
		};
	});