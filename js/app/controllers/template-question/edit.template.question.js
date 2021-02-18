angular.module('opalAdmin.controllers.template.question.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('template.question.edit', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, uiGridConstants, Session, ErrorHandler) {
		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		// initialize default variables & lists
		$scope.templateQuestion = {};
		$scope.changesMade = false;
		$scope.validSlider = true;
		$scope.preview = [];
		$scope.language = Session.retrieveObject('user').language;

		// Initialize variables for holding selected answer type & group
		$scope.selectedLibrary = [];

		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		// Filter lists initialized
		$scope.libraryFilterList = [];

		// Initialize search field variables
		$scope.libEntered = '';

		// assign functions
		$scope.searchLib = function (field) {
			$scope.libEntered = field;
		};

		$scope.orderPreview = function () {
			$scope.templateQuestion.subOptions.sort(function(a,b){
				return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
			});
		};

		$scope.updateSlider = function () {
			var radiostep = new Array();
			var increment = parseFloat($scope.templateQuestion.options.increment);
			var minValue = parseFloat($scope.templateQuestion.options.minValue);
			var maxValue = parseFloat($scope.templateQuestion.options.maxValue);

			if (minValue <= 0.0 || maxValue <= 0.0 || increment <= 0 || minValue >= maxValue)
				$scope.validSlider = false;
			else {
				maxValue = (Math.floor((maxValue - minValue) / increment) * increment) + minValue;
				$scope.validSlider = true;
				for(var i = minValue; i <= maxValue; i += increment) {
					radiostep.push({"description":" " + i,"description_EN":" " + i,"description_FR":" " + i});
				}
				radiostep[0]["description_EN"] += " " + $scope.templateQuestion.options.minCaption_EN;
				radiostep[0]["description_FR"] += " " + $scope.templateQuestion.options.minCaption_FR;
				radiostep[radiostep.length - 1]["description_EN"] += " " + $scope.templateQuestion.options.maxCaption_EN;
				radiostep[radiostep.length - 1]["description_FR"] += " " + $scope.templateQuestion.options.maxCaption_FR;
			}
			$scope.preview = radiostep;
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

		$scope.checkForm = function () {
			if ($scope.templateQuestion.name_EN && $scope.templateQuestion.name_FR && $scope.changesMade) {
				if($scope.templateQuestion.typeId === "2") {
					if ($scope.templateQuestion.options.increment <= 0 || $scope.templateQuestion.options.minValue <= 0 || $scope.templateQuestion.options.maxValue <= 0 || $scope.templateQuestion.options.minValue > $scope.templateQuestion.options.maxValue || $scope.templateQuestion.options.minCaption_EN === "" || $scope.templateQuestion.options.minCaption_FR === "" || $scope.templateQuestion.options.maxCaption_EN === "" || $scope.templateQuestion.options.maxCaption_FR === "" )
						return false;
					else
						return true;
				} else if ($scope.templateQuestion.typeId === "4" || $scope.templateQuestion.typeId === "1") {
					var loopResult = true;
					$scope.templateQuestion.subOptions.forEach(function(entry) {
						if (entry.description_EN ==="" || entry.description_FR ==="" || entry.order === "")
							loopResult = false;
					});
					return loopResult;
				}
				else
					return true;
			}
			else
				return false;
		};

		// Show processing dialog on load
		$scope.showProcessingModal();

		// Call our API service to get the questionnaire details
		questionnaireCollectionService.getTemplateQuestionDetails($scope.currentTemplateQuestion.ID).then(function (response) {
			// Assign value
			$scope.templateQuestion = response.data;

			if($scope.language.toUpperCase() === "FR")
				$scope.templateQuestion.category_display = $scope.templateQuestion.category_FR;
			else
				$scope.templateQuestion.category_display = $scope.templateQuestion.category_EN;

			if($scope.templateQuestion.typeId === "2") {
				$scope.templateQuestion.options.minValue = parseInt($scope.templateQuestion.options.minValue);
				$scope.templateQuestion.options.maxValue = parseInt($scope.templateQuestion.options.maxValue);
				$scope.templateQuestion.options.increment = parseInt($scope.templateQuestion.options.increment);
				$scope.updateSlider();
			}
			else if($scope.templateQuestion.subOptions !== null) {
				$scope.templateQuestion.subOptions.forEach(function(entry) {
					entry.order = parseInt(entry.order);
				});
			}

			$scope.templateQuestion.private = parseInt($scope.templateQuestion.private);
			$scope.templateQuestion.isOwner = parseInt($scope.templateQuestion.isOwner);

			$scope.templateQuestion.OAUserId = OAUserId;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_EDIT.ERROR_GET_TEMPLATE_DETAILS'));
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
			$uibModalInstance.close();
		});

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


		$scope.addOptions = function () {
			$scope.templateQuestion.subOptions.push({
				description_EN: "",
				description_FR: "",
				order: $scope.templateQuestion.subOptions.length+1,
				OAUserId: OAUserId
			});
		};

		// delete options
		$scope.deleteOptions = function (optionToDelete) {
			var index = $scope.templateQuestion.subOptions.indexOf(optionToDelete);
			if (index > -1) {
				$scope.templateQuestion.subOptions.splice(index, 1);
				$scope.changesMade = true;
			}
		};


		// Submit changes
		$scope.updateTemplateQuestion = function () {
			// Submit form
			$.ajax({
				type: "POST",
				url: "template-question/update/template-question",
				data: $scope.templateQuestion,
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_EDIT.SUCCESS');
					$scope.showBanner();
				},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_EDIT.ERROR_SET_TEMPLATE_QUESTION'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		};
	});