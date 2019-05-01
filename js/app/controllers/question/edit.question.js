angular.module('opalAdmin.controllers.question.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.edit', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// get current user id
		var user = Session.retrieveObject('user');
		var userId = user.id;

		$scope.changesMade = false;

		// initialize default variables & lists
		$scope.changesMade = false;
		$scope.question = {};
		$scope.libraries = [];

		// Initialize variables for holding selected answer type & group
		$scope.selectedAt = null;
		$scope.selectedLibrary = [];

		// Filter lists initialized
		$scope.atFilterList = [];
		$scope.libraryFilterList = [];
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
		questionnaireCollectionService.getQuestionDetails($scope.currentQuestion.serNum, userId).then(function (response) {

			// Assign value
			$scope.question = response.data;

			if($scope.question.options !== null) {
				$scope.toMinValue = parseInt($scope.question.options.minValue);
				$scope.toMaxValue = parseInt($scope.question.options.maxValue);
				$scope.toIncrement = parseInt($scope.question.options.increment);
			}

			if (response.data.private === "1")
				$scope.question.private = true;
			else
				$scope.question.private = false;
			if (response.data.final === "1")
				$scope.question.final = true;
			else
				$scope.question.final = false;
			$scope.libraryFilterList = response.data.libraries;
			$scope.selectedLibrary = response.data.libSelected;
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function (err) {
			alert('Error occurred getting question details.\r\nCode ' + err.status + " " + err.data);
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});

		// Call our API service to get the list of existing answer types 
		questionnaireCollectionService.getQuestionTypes(userId).then(function (response) {
			$scope.atFilterList = response.data;
		}).catch(function (response){
			console.error('Error occurred getting response types:', response.status, response.data);
		});

		// Call our API service to get the list of existing groups
		/*questionnaireCollectionService.getQuestionGroups(userId).then(function (response) {
			$scope.groupFilterList = response.data;
		}).catch(function (response){
			console.error('Error occurred getting question groups:', response.status, response.data);
		});*/

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

		$scope.updateLibrary = function (selectedLibrary) {
			var idx = $scope.selectedLibrary.indexOf(selectedLibrary.serNum);
			if (idx > -1)
				$scope.selectedLibrary.splice(idx, 1);
			else
				$scope.selectedLibrary.push(selectedLibrary.serNum);
		};

		$scope.addOptions = function () {
			console.log("passed");
			$scope.question.subOptions.push({
				description_EN: "",
				description_FR: "",
				position: undefined,
				userId: userId
			});
		};

		// delete options
		$scope.deleteOptions = function (optionToDelete) {
			var index = $scope.question.subOptions.indexOf(optionToDelete);
			if (index > -1) {
				$scope.question.subOptions.splice(index, 1);
			}
		};

		questionnaireCollectionService.getLibraries(userId).then(function (response) {
			$scope.groupFilterList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting question libraries:', response.status, response.data);
		});

		$scope.newLibrary = {
			name_EN: "",
			name_FR: "",
			private: 0,
			userid: userId
		};
		$scope.addNewLib = function () {
			// Prompt to confirm user's action
			var confirmation = confirm("Are you sure you want to create new library " + $scope.newLibrary.name_EN + " / "+$scope.newLibrary.name_FR+ "?");
			if (confirmation) {
				// write in to db
				$.ajax({
					type: "POST",
					url: "php/questionnaire/insert.library.php",
					data: $scope.newLibrary,
					success: function (result) {
						result = JSON.parse(result);
						if(result.code === 200) {
							alert('Successfully added the new library. Please find your new library in the panel above.');
							questionnaireCollectionService.getLibraries(userId).then(function (response) {
								$scope.libraryFilterList = response.data;
							}).catch(function (response) {
								alert('Error occurred getting libraries. Code '+ response.status +"\r\n" + response.data);
							});
						}
						else {
							alert("Unable to create the library. Code " + result.code + ".\r\nError message: " + result.message);
						}
					},
					error: function () {
						alert("Something went wrong.");
					}
				});
			}
		};


		// Submit changes
		$scope.updateQuestion = function () {
			console.log($scope.question);
			/*if ($scope.checkForm()) {
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
			}*/
		};
	});