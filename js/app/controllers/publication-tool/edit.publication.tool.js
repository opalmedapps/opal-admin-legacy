angular.module('opalAdmin.controllers.publication.tool.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('publication.tool.edit', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		// initialize default variables & lists
		$scope.changesMade = false;
		$scope.questionnaire = {};

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Default toolbar for wysiwyg
		$scope.toolbar = [
			['h1', 'h2', 'h3', 'p'],
			['bold', 'italics', 'underline', 'ul', 'ol'],
			['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
			['html', 'insertLink']
		];

		// initialize variables
		$scope.groupList = [];
		$scope.groupListReferenced = [];
		$scope.selectedGroups;
		$scope.previewQuestions = [];
		$scope.tagFilter = "";
		$scope.anyPrivate = false;
		var publicPrivateWarning = false;

		function decodeQuestions(questions) {
			questions.forEach(function(entry) {
				if(entry.typeId === "2") {
					var increment = parseFloat(entry.options.increment);
					var minValue = parseFloat(entry.options.minValue);
					if (minValue === 0.0) minValue = increment;
					var maxValue = parseFloat(entry.options.maxValue);

					var radiostep = new Array();
					for(var i = minValue; i <= maxValue; i += increment) {
						radiostep.push({"description":" " + i,"description_EN":" " + i,"description_FR":" " + i});
					}
					radiostep[0]["description"] += " " + entry.options.minCaption_EN + " / " + entry.options.minCaption_FR;
					radiostep[0]["description_EN"] += " " + entry.options.minCaption_EN;
					radiostep[0]["description_FR"] += " " + entry.options.minCaption_FR;
					radiostep[radiostep.length - 1]["description"] += " " + entry.options.maxCaption_EN + " / " + entry.options.maxCaption_FR;
					radiostep[radiostep.length - 1]["description_EN"] += " " + entry.options.maxCaption_EN;
					radiostep[radiostep.length - 1]["description_FR"] += " " + entry.options.maxCaption_FR;
					entry.subOptions = radiostep;
				}
			});
			return questions;
		}

		questionnaireCollectionService.getFinalizedQuestions(OAUserId).then(function (response) {
			$scope.groupList = decodeQuestions(response.data);
		}).catch(function (response) {
			alert('Error occurred getting group list. Code ' + response.status  + ".\r\n" + response.data);
		});

		// table
		// Filter in table
		$scope.filterOptions = function (renderableRows) {
			return renderableRows;
		};

		// Template for group table
		var cellTemplateName = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.question_EN}} / {{row.entity.question_FR}}</p></div>';
		var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</p></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';

		// Table Data binding
		$scope.gridGroups = {
			data: 'groupList',
			columnDefs: [
				{ field: 'question_EN', displayName: 'Name (EN / FR)', cellTemplate: cellTemplateName, width: '54%' },
				{ field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '30%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '13%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
			],
			enableColumnResizing: true,
			enableFiltering: true,
			enableSorting: true,
			enableRowSelection: true,
			enableSelectAll: true,
			enableSelectionBatchEvent: true,
			showGridFooter: false,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
				gridApi.selection.on.rowSelectionChanged($scope, function (row) {
					selectUpdate(row);

				});
			},
		};

		// Function to update the questionnaire after changing selection
		var selectUpdate = function (row) {

			// get selected rows
			$scope.selectedGroups = $scope.gridApi.selection.getSelectedGridRows();

			// sort question groups by retrieved position
			$scope.questionnaire.questions.sort(function(a,b){
				return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
			});

			// Check to see if current row was (de)selected
			var wasSelected = false;
			angular.forEach($scope.selectedGroups, function(selectedGroup) {
				if (row.entity.ID == selectedGroup.entity.ID) {
					wasSelected = true;
				}
			});

			var groupIndex = null;
			if (!wasSelected) {  // Deselected
				angular.forEach($scope.questionnaire.questions, function(group, index) {
					if(group.ID == row.entity.ID) {
						groupIndex = index; // array index of the group that was removed
					}
				});
				$scope.questionnaire.questions.splice(groupIndex, 1); // Take it out of the array
				angular.forEach($scope.questionnaire.questions, function(group, index) {
					group.order = index + 1; // Refactor the positions of the leftover groups
				});
				$scope.changesMade = true; // set changes made
			}
			else {
				// Check to see if added row exists already in the groups
				var inGroups = false;
				angular.forEach($scope.questionnaire.questions, function(group){
					if (row.entity.ID == group.ID) {
						inGroups = true;
					}
				});

				if (!inGroups) { // If not, append it to existing groups
					row.entity.order = $scope.questionnaire.questions.length + 1;
					row.entity.optional = '0';
					$scope.questionnaire.questions.push(row.entity);
					$scope.changesMade = true; // set changes made
				}
			}

			var anyPrivate = false;
			$scope.questionnaire.questions.forEach(function(entry) {
				if(parseInt(entry.private) === 1)
					anyPrivate = true;
			});

			$scope.anyPrivate = anyPrivate;
			if (anyPrivate) {
				if(publicPrivateWarning && $scope.questionnaire.private !== 1) {
					publicPrivateWarning = false;
					alert("When selecting a private question, a questionnaire has to be set to private.");
				}
				$scope.questionnaire.private = 1;
			}
			else {
				publicPrivateWarning = true;
			}
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

		// Show processing dialog upon first load
		$scope.showProcessingModal();

		// Call our API service to get questionnaire details
		questionnaireCollectionService.getQuestionnaireDetails($scope.currentQuestionnaire.ID, OAUserId).then(function (response) {

			// Assign value
			$scope.questionnaire = response.data;
			$scope.questionnaire.questions = decodeQuestions($scope.questionnaire.questions);

		}).catch(function (e) {
			alert('Error occurred getting questionnaire details after modal open. Code ' + e.status + ".\r\n" + e.data);
		}).finally(function () {
			$timeout(function () {
				if ($scope.gridApi.selection.selectRow) {
					angular.forEach($scope.questionnaire.questions, function (selectedGroup) {
						angular.forEach($scope.groupList, function (group) {
							if (selectedGroup.ID == group.ID) {
								$scope.gridApi.selection.selectRow(group);
							}
						});
					});
				}
			});

			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			$scope.changesMade = true;
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

		// Function called when changing the questionnaire privacy flag
		$scope.privacyUpdate = function (value) {
			if(!$scope.anyPrivate && !$scope.questionnaire.readOnly && !$scope.questionnaire.final) {
				if (value == 0 || value == 1) {
					// update value
					$scope.questionnaire.private = value;
				}
				$scope.changesMade = true;
			}
		};

		// Function called whenever there has been a change in the form
		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		$scope.orderPreview = function () {
			$scope.questionnaire.questions.sort(function(a,b){
				return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
			});
		}

		// Function to close edit modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.questionnaire.title_EN && $scope.questionnaire.title_FR && $scope.questionnaire.questions.length && $scope.changesMade) {
				return true;
			}
			else
				return false;
		};

		// Function for updating the questionnaire 
		$scope.updateQuestionnaire = function () {

			if ($scope.checkForm()) {
				$scope.questionnaire.OAUserId = OAUserId;

				// ajax POST
				$.ajax({
					type: "POST",
					url: "php/questionnaire/update.questionnaire.php",
					data: $scope.questionnaire,
					success: function (response) {
						response = JSON.parse(response);

						// Show success or failure depending on response
						if (response.code === 200) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.questionnaire.title_EN + "/ " + $scope.questionnaire.title_FR + "\"!";
							$uibModalInstance.close();
							$scope.showBanner();
						}
						else
							alert("An error occurred, code "+response.code+". Please review the error message below.\r\n" + response.message);
					}
				});
			}
		};

	});