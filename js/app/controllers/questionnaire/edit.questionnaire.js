// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.questionnaire.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('questionnaire.edit', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, questionnaireCollectionService, Session, uiGridConstants, ErrorHandler) {

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
				entry.question_EN = entry.question_EN.replace(/(<([^>]+)>)/ig,"");
				entry.question_FR = entry.question_FR.replace(/(<([^>]+)>)/ig,"");
				if (Session.retrieveObject('user').language.toUpperCase() === "FR") {
					entry.questionDisplay = entry.question_FR;
					entry.libraryDisplay = entry.library_name_FR;
				}
				else {
					entry.questionDisplay = entry.question_EN;
					entry.libraryDisplay = entry.library_name_EN;
				}

				if(entry.typeId === "2") {
					var increment = parseFloat(entry.options.increment);
					var minValue = parseFloat(entry.options.minValue);
					if (minValue < 0) minValue = 0;
					var maxValue = parseFloat(entry.options.maxValue);

					var radiostep = [];
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

		questionnaireCollectionService.getFinalizedQuestions().then(function (response) {
			$scope.groupList = decodeQuestions(response.data);
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.ERROR_QUESTION_LIST'));
			$uibModalInstance.close();
		});

		// table
		// Filter in table
		$scope.filterOptions = function (renderableRows) {
			return renderableRows;
		};

		// Template for group table
		var cellTemplateName = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.questionDisplay}}</p></div>';
		var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.libraryDisplay}}</p></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PUBLIC')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PRIVATE')+'</p></div>';

		// Table Data binding
		$scope.gridGroups = {
			data: 'groupList',
			columnDefs: [
				{ field: 'questionDisplay', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.QUESTION'), cellTemplate: cellTemplateName, width: '57%' },
				{ field: 'libraryDisplay', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.LIBRARY'), cellTemplate: cellTemplateLib, width: '20%' },
				{
					field: 'private', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PRIVACY'), cellTemplate: cellTemplatePrivacy, width: '20%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PRIVATE') }, { value: '0', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PUBLIC') }]
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
					alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PRIVATE_QUESTION'));
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
		// $scope.showProcessingModal();

		questionnaireCollectionService.getPurposesRespondents().then(function (response) {
			response.data.purposes.forEach(function(row) {
				if(user.language.toUpperCase() === "FR") {
					row.title_display = row.title_FR;
					row.description_display = row.description_FR;
				}
				else {
					row.title_display = row.title_EN;
					row.description_display = row.description_EN;
				}
			});
			response.data.respondents.forEach(function(row) {
				if(user.language.toUpperCase() === "FR") {
					row.title_display = row.title_FR;
					row.description_display = row.description_FR;
				}
				else {
					row.title_display = row.title_EN;
					row.description_display = row.description_EN;
				}
			});
			$scope.purposeList = response.data.purposes;
			$scope.respondentList = response.data.respondents;
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.ERROR_PURPOSES_RESPONDENTS'));
			$state.go('questionnaire');
		});

		// Call our API service to get questionnaire details
		questionnaireCollectionService.getQuestionnaireDetails($scope.currentQuestionnaire.ID).then(function (response) {
			// Assign value
			$scope.questionnaire = response.data;
			$scope.questionnaire.questions = decodeQuestions($scope.questionnaire.questions);
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.ERROR_QUESTIONNAIRE_DETAILS'));
			$uibModalInstance.close();
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
		};

		// Function to close edit modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.questionnaire.title_EN && $scope.questionnaire.title_FR && /*$scope.questionnaire.short_name_EN && $scope.questionnaire.short_name_FR &&*/ $scope.questionnaire.description_EN && $scope.questionnaire.description_FR && $scope.questionnaire.questions.length && $scope.changesMade) {
				return true;
			}
			else
				return false;
		};

		// Function for updating the questionnaire 
		$scope.updateQuestionnaire = function () {

			if ($scope.checkForm()) {
				var formatData = copyQuestionnaireData($scope.questionnaire);

				// ajax POST
				$.ajax({
					type: "POST",
					url: "questionnaire/update/questionnaire",
					data: formatData,
					success: function () {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.SUCCESS_UPDATE');
						$scope.showBanner();
					},
					error: function(err) {
						alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.ERROR_UPDATE_QUESTIONNAIRE') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
					},
					complete: function() {
						$uibModalInstance.close();
					}
				});
			}
		};

		function copyQuestionnaireData(oldData) {
			var newFormat = {
				ID : oldData.ID,
				OAUserId : oldData.OAUserId,
				title_EN : oldData.title_EN,
				title_FR : oldData.title_FR,
				//short_name_EN : oldData.short_name_EN,
				//short_name_FR : oldData.short_name_FR,
				description_EN : oldData.description_EN,
				description_FR : oldData.description_FR,
				purpose : oldData.purpose.ID,
				respondent : oldData.respondent.ID,
				private : oldData.private,
				final : oldData.final,
				questions : []
			};
			var temp;
			angular.forEach(oldData.questions, function(item) {
				temp = {
					ID : item.ID,
					order : item.order,
					optional : item.optional,
					typeId : item.typeId,
				};
				newFormat.questions.push(temp);
			});
			return newFormat;
		}

	});