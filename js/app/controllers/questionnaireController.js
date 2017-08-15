angular.module('opalAdmin.controllers.questionnaireController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	// Function to accept/trust html (styles, classes, etc.)
	.filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	})

	.controller('questionnaireController', function ($sce, $scope, $state, $filter, $uibModal, questionnaireAPIservice, filterAPIservice, Session, uiGridConstants) {
		// get current user id
		var user = Session.retrieveObject('user');
		var userid = user.id;

		// navigating functions
		$scope.goToManage = function () {
			$state.go('questionnaire-manage');
		};
		$scope.goToAddQuestionnaire = function () {
			$state.go('questionnaire-add');
		};
		$scope.goToQuestionBank = function () {
			$state.go('questionnaire-bank');
		};
		$scope.goToCompleted = function () {
			$state.go('questionnaire-completed');
		};

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

		// Close Modal
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


		// Filter
		// post table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN'].forEach(function (field) {
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


		$scope.filterQuestionnaire = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table
		// Template
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editQuestionnaire(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteQuestionnaire(row.entity)">Delete</a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestionnaire(row.entity)">' +
			'<a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
		var cellTemplatePublish = '<div class="ui-grid-cell-contents" ng-show="row.entity.publish == 0"><p>No</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.publish == 1"><p>Yes</p></div>';

		var cellTemplateTags = '<div class="ui-grid-cell-contents" ng-repeat="tag in row.entity.tags"' +
			'<p>{{tag.name_EN}} / {{tag.name_FR}} ;</p></div>';

		// Data binding
		$scope.gridOptions = {
			data: 'questionnaireList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '25%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
				{
					field: 'publish', displayName: 'Publish', cellTemplate: cellTemplatePublish, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Yes' }, { value: '0', label: 'No' }]
					}
				},
				{ field: 'created_by', displayName: 'Author', width: '10%' },
				{ field: 'tags', displayName: 'Tags (EN / FR)', cellTemplate: cellTemplateTags, width: '30%', enableFiltering: false },
				{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize object for storing quesitoinnaires
		$scope.questionnaireList = [];

		// Call API to get the list of questionnaires
		questionnaireAPIservice.getQuestionnaire(userid).success(function (response) {
			$scope.questionnaireList = response;
			//console.log($scope.questionnaireList);
		});

		// Initialize the questionnaire to be deleted
		$scope.questionnaireToDelete = {};
		// Initialize a scope variable for a selected questionnaire
		$scope.currentQuestionnaire = {};

		// Function to delete questionnaire
		$scope.deleteQuestionnaire = function (questionnaire) {
			$scope.questionnaireToDelete = questionnaire;
			var modalInstance = $uibModal.open({
				templateUrl: 'deleteQuestionnaireModalContent.htm',
				controller: DeleteQuestionnaireModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the questionnaire list
			modalInstance.result.then(function () {
				questionnaireAPIservice.getQuestionnaire(userid).success(function (response) {
					$scope.questionnaireList = response;
					//console.log($scope.questionnaireList);
				});
			});
		};


		$scope.editQuestionnaire = function (questionnaire) {
			$scope.currentQuestionnaire = questionnaire;

			var modalInstance = $uibModal.open({
				templateUrl: 'editQuestionnaireModalContent.htm',
				controller: EditQuestionnaireModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the post list
			modalInstance.result.then(function () {
				questionnaireAPIservice.getQuestionnaire(userid).success(function (response) {
					$scope.questionnaireList = response;
					//console.log($scope.questionnaireList);
				});
			});
		};
		// Controller for editModal
		var EditQuestionnaireModalInstanceCtrl = function ($scope, $uibModalInstance) {
			// get current user id
			var user = Session.retrieveObject('user');
			var userid = user.id;

			// initialize default variables & lists
			$scope.changesMade = false;
			$scope.questionnaire = {};

			// initialize variables
			$scope.tagList = [];
			$scope.groupList = [];
			$scope.selectedGroups;
			$scope.newgroups = []; // holding new groups that want to be added
			$scope.tagFilter = "";
			$scope.removingGroups = []; // groups to be deleted

			// table
			// Filter in table
			$scope.filterOptions = function (renderableRows) {
				var matcher = new RegExp($scope.filterValue, 'i');
				renderableRows.forEach(function (row) {
					var match = false;
					['name_EN'].forEach(function (field) {
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

			// Template for table
			var cellTemplateName = '<div class="ui-grid-cell-contents" ' +
				'<p>{{row.entity.name_EN}} / {{row.entity.name_FR}}</p></div>';
			var cellTemplateCat = '<div class="ui-grid-cell-contents" ' +
				'<p>{{row.entity.category_EN}} / {{row.entity.category_FR}}</p></div>';
			var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
				'<p>{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</p></div>';
			var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
				'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
			var cellTemplateTags = '<div class="ui-grid-cell-contents" ng-repeat="tag in row.entity.tags"' +
				'<p>{{tag.name_EN}} / {{tag.name_FR}} ;</p></div>';


			// Table Data binding
			$scope.gridGroups = {
				data: 'groupList',
				columnDefs: [
					{ field: 'name_EN', displayName: 'Group (EN / FR)', cellTemplate: cellTemplateName, width: '20%' },
					{ field: 'category_EN', displayName: 'Category (EN / FR)', cellTemplate: cellTemplateCat, width: '25%' },
					{ field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '15%' },
					{
						field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
							type: uiGridConstants.filter.SELECT,
							selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
						}
					},
					{ field: 'tags', displayName: 'Tags (EN / FR)', cellTemplate: cellTemplateTags, enableFiltering: true, width: '30%' }
				],
				enableColumnResizing: true,
				enableFiltering: true,
				enableSorting: true,
				enableRowSelection: true,
				enableSelectAll: true,
				enableSelectionBatchEvent: true,
				showGridFooter: true,
				onRegisterApi: function (gridApi) {
					$scope.gridApi = gridApi;
					gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
					gridApi.selection.on.rowSelectionChanged($scope, function (row) {
						selectUpdate();

					});
				},
			};

			// cancel selection
			$scope.cancelSelection = function () {
				$scope.gridApi.selection.clearSelectedRows();
				selectUpdate();
			};

			// select all rows
			$scope.selectAll = function () {
				$scope.gridApi.selection.selectAllRows();
				selectUpdate();
			};

			// function to update the newQuestionnaire content after changing selection
			var selectUpdate = function () {
				$scope.changesMade = true;
				$scope.selectedGroups = $scope.gridApi.selection.getSelectedGridRows();
				var selectedNum = $scope.gridApi.selection.getSelectedCount();
				console.log($scope.selectedGroups);
				if (selectedNum === 0) {
					$scope.newgroups = [];
				} else {
					var tempGroupArray = [];
					for (var i = 0; i < selectedNum; i++) {
						var currentpos = parseInt(i + $scope.questionnaire.groups.length + 1);
						var group = {
							questionnaire_serNum: $scope.questionnaire.serNum,
							created_by: userid,
							last_updated_by: userid,
							questiongroup_serNum: $scope.selectedGroups[i].entity.serNum,
							optional: 0,
							position: currentpos,
							name_EN: $scope.selectedGroups[i].entity.name_EN,
							name_FR: $scope.selectedGroups[i].entity.name_FR
						};
						tempGroupArray.push(group);
						$scope.newgroups = tempGroupArray.slice(0);
					}
				}
				console.log('new groups');
				console.log($scope.newgroups);
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

			// Show processing dialog
			$scope.showProcessingModal();

			// questionnaire API: retrieve data
			questionnaireAPIservice.getQuestionnaireDetails($scope.currentQuestionnaire.serNum).success(function (response) {
				// parse
				/*for (var g = 0; g < response.groups.length; g++) {
					response.groups[g].position = parseInt(response.groups[g].position);
				}*/
				// Assign value
				$scope.questionnaire = response;
				console.log('questionnairedetails:');
				console.log(response);

				// API getting group list
				questionnaireAPIservice.getGroupsWithQuestions(userid).success(function (response) {
					console.log('grouplist:');
					console.log(response);
					$scope.groupList = response;
				});

				// get tag list
				questionnaireAPIservice.getTag().success(function (response) {
					console.log('taglist:');
					$scope.tagList = checkAdded(response);
					console.log('check added:');
					console.log($scope.tagList);
				});

				processingModal.close(); // hide modal
				processingModal = null; // remove reference

			});

			// Function to assign 1 to existing tags
			function checkAdded(filterList) {
				angular.forEach($scope.questionnaire.tags, function (selectedFilter) {
					var selectedFilterId = selectedFilter.serNum;
					angular.forEach(filterList, function (filter) {
						var filterId = filter.serNum;
						if (filterId == selectedFilterId) {
							filter.added = 1;
						}
					});
				});
				return filterList;
			}

			// update groups to be deleted 
			$scope.selectGroupToDelete = function (group) {
				$scope.changesMade = true;
				if (group.deleted) {
					group.deleted = 0;
				} else {
					group.deleted = 1;
				}
			};

			// assign search field 
			$scope.searchTag = function (field) {
				$scope.tagFilter = field;
			};

			// search filter
			$scope.searchTagFilter = function (Filter) {
				var keyword = new RegExp($scope.tagFilter, 'i');
				return !$scope.tagFilter || keyword.test(Filter.name_EN);
			};

			// Function to toggle Item in a list on/off
			$scope.selectTag = function (tag) {
				$scope.changesMade = true;
				if (tag.added) {
					tag.added = 0;
				} else {
					tag.added = 1;
				}
			};

			// add tags
			function addTags(tagList) {
				angular.forEach(tagList, function (Filter) {
					if (Filter.added) {
						$scope.questionnaire.tags.push(Filter);
					}
				});
			};

			// check if there's any tag added
			$scope.checkTags = function (tagList) {
				var tagsAdded = false;
				angular.forEach(tagList, function (Filter) {
					if (Filter.added)
						tagsAdded = true;
				});
				return tagsAdded;
			};

			$scope.privacyUpdate = function (value) {
				if (value == 0 || value == 1) {
					// update value
					$scope.questionnaire.private = value;
				}
				$scope.changesMade = true;
			};

			$scope.setChangesMade = function () {
				$scope.changesMade = true;
			};

			// Function to close modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};

			// Function to check necessary form fields are complete
			$scope.checkForm = function () {
				if ($scope.questionnaire.name_EN && $scope.questionnaire.name_FR && $scope.questionnaire.tags.length && $scope.questionnaire.groups.length && $scope.changesMade) {
					return true;
				}
				else
					return false;
			};

			// update questionnaire in table
			$scope.updateQuestionnaire = function () {

				addTags($scope.tagList);
				console.log($scope.tagList);
				console.log('groupsfinal');
				console.log($scope.newgroups);
				// update
				$.ajax({
					type: "POST",
					url: "php/questionnaire/updateQuestionnaire.php",
					data: $scope.questionnaire,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value && $scope.newgroups.length) {
							// add new groups
							$.ajax({
								type: "POST",
								url: "php/questionnaire/addQuestionGroupToQuestionnaire.php",
								data: { groupArray: $scope.newgroups },
								success: function (response) {
									response = JSON.parse(response);
									// Show success or failure depending on response
									if (response.value) {
										$scope.setBannerClass('success');
										$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.questionnaire.name_EN + "/ " + $scope.questionnaire.name_FR + "\"!";
									}
									else {
										$scope.setBannerClass('danger');
										$scope.$parent.bannerMessage = response.message;
									}
								}
							});
						}
						else if (response.value && !$scope.newgroups.length) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.questionnaire.name_EN + "/ " + $scope.questionnaire.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}

						$scope.showBanner();
						$uibModalInstance.close();
					}
				});

			};


		};

		var DeleteQuestionnaireModalInstanceCtrl = function ($scope, $uibModalInstance) {
			// Submit delete
			$scope.deleteQuestionnaire = function () {
				$.ajax({
					type: "POST",
					url: "php/questionnaire/deleteQuestionnaire.php",
					data: $scope.questionnaireToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.questionnaireToDelete.name_EN + "/ " + $scope.questionnaireToDelete.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
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
