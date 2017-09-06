angular.module('opalAdmin.controllers.questionController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('questionController', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Routing to go to add question page
		$scope.goToAddQuestion = function () {
			$state.go('questionnaire-question-add');
		};

		// Table
		// Filter in table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['text_EN', 'group_name_EN', 'answertype_name_EN', 'library_name_EN'].forEach(function (field) {
					if (row.entity[field].match(matcher)) {
						match = true;
					}
				});
				if (!match) {
					row.visible = false;
				}
			});
			return renderableRows;
		};
		$scope.filterLibrary = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		// Templates for main question table
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editQuestion(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteQuestion(row.entity)">Delete</a></strong></div>';
		var cellTemplateGroupName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<a href="">{{row.entity.group_name_EN}} / {{row.entity.group_name_FR}}</a></div>';
		var cellTemplateText = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<a href="">{{row.entity.text_EN}} / {{row.entity.text_FR}}</a></div>';
		var cellTemplateLib = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<a href="">{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</a></div>';
		var cellTemplateAt = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<a href="">{{row.entity.answertype_name_EN}} / {{row.entity.answertype_name_FR}}</a></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';

		// Data binding for question table
		$scope.gridLib = {
			data: 'questionList',
			columnDefs: [
				{ field: 'serNum', displayName: 'ID', width: '4%', enableFiltering: false },
				{ field: 'text_EN', displayName: 'Question (EN / FR)', cellTemplate: cellTemplateText, width: '30%' },
				{ field: 'group_name_EN', displayName: 'Group (EN / FR)', cellTemplate: cellTemplateGroupName, width: '25%' },
				{ field: 'answertype_name_EN', displayName: 'Answer Type (EN / FR)', cellTemplate: cellTemplateAt, width: '13%' },
				{ field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '10%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '8%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
				{ name: 'Operations', width: '10%', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false }
			],
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Call our API service to get the list of existing questions
		questionnaireCollectionService.getQuestions().then(function (response) {
			$scope.questionList = response.data;
			
		}).catch(function(response) {
			console.error('Error occurred getting question list:', response.status, response.data);
		});

		// Banner
		$scope.bannerMessage = "";
		// Function to show page banner 
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 3000);
			});
		};

		// Function to set banner class
		$scope.setBannerClass = function (classname) {
			// Remove any classes starting with "alert-" 
			$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + classname);
		};

		// initialize variable for storing selected question
		$scope.currentQuestion = {};

		// function to edit question
		$scope.editQuestion = function (question) {
			$scope.currentQuestion = question;
			var modalInstance = $uibModal.open({
				templateUrl: 'editQuestionModalContent.htm',
				controller: EditQuestionModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// after update, refresh data
			modalInstance.result.then(function () {
				$scope.questionList = [];
				// Call our API service to get the list of existing questions
				questionnaireCollectionService.getQuestions().then(function (response) {
					$scope.questionList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting question list after modal close:', response.status, response.data);
				});

			});
		};

		// Controller for editing question
		var EditQuestionModalInstanceCtrl = function ($scope, $uibModalInstance) {

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
					templateUrl: 'processingModal.htm',
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
		};

		// initialize variable for storing deleting question
		$scope.questionToDelete = {};

		// function to delete question
		$scope.deleteQuestion = function (currentQuestion) {
			$scope.questionToDelete = currentQuestion;
			var modalInstance = $uibModal.open({
				templateUrl: 'deleteQuestionModalContent.htm',
				controller: DeleteQuestionModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the eduMat list
			modalInstance.result.then(function () {
				$scope.questionList = [];
				// update data
				questionnaireCollectionService.getQuestions().then(function (response) {
					$scope.questionList = response.data;
				}).catch(function (response){
					console.error('Error occurred getting question lsit after modal close:', response.status, response.data);
				});
			});
		};

		// Controller for the delete educational material modal
		var DeleteQuestionModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Submit delete
			$scope.deleteQuestion = function () {
				$.ajax({
					type: "POST",
					url: "php/questionnaire/delete.question.php",
					data: $scope.questionToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.questionToDelete.text_EN + "/ " + $scope.questionToDelete.text_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = response.message;
						}
						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			};

			// Function to close modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};


		};

	});
