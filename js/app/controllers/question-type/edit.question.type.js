angular.module('opalAdmin.controllers.question.type.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.type.edit', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {
		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;


		// initialize default variables & lists
		$scope.questionType = {};
		$scope.changesMade = false;
		$scope.validSlider = true;


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
			$scope.questionType.subOptions.sort(function(a,b){
				return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
			});
		};

		$scope.updateSlider = function () {
			var radiostep = new Array();
			var increment = parseFloat($scope.questionType.options.increment);
			var minValue = parseFloat($scope.questionType.options.minValue);
			var maxValue = parseFloat($scope.questionType.options.maxValue);

			if (minValue <= 0.0 || maxValue <= 0.0 || increment <= 0 || minValue >= maxValue)
				$scope.validSlider = false;
			else {
				maxValue = (Math.floor((maxValue - minValue) / increment) * increment) + minValue;
				$scope.validSlider = true;
				for(var i = minValue; i <= maxValue; i += increment) {
					radiostep.push({"description":" " + i,"description_EN":" " + i,"description_FR":" " + i});
				}
				radiostep[0]["description_EN"] += " " + $scope.questionType.options.minCaption_EN;
				radiostep[0]["description_FR"] += " " + $scope.questionType.options.minCaption_FR;
				radiostep[radiostep.length - 1]["description_EN"] += " " + $scope.questionType.options.maxCaption_EN;
				radiostep[radiostep.length - 1]["description_FR"] += " " + $scope.questionType.options.maxCaption_FR;
			}
			$scope.questionType.subOptions = radiostep;
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
			if ($scope.questionType.name_EN && $scope.questionType.name_FR && $scope.changesMade) {
				if($scope.questionType.typeId === "2") {
					if ($scope.questionType.options.increment <= 0 || $scope.questionType.options.minValue <= 0 || $scope.questionType.options.maxValue <= 0 || $scope.questionType.options.minValue > $scope.questionType.options.maxValue || $scope.questionType.options.minCaption_EN === "" || $scope.questionType.options.minCaption_FR === "" || $scope.questionType.options.maxCaption_EN === "" || $scope.questionType.options.maxCaption_FR === "" )
						return false;
					else
						return true;
				} else if ($scope.questionType.typeId === "4" || $scope.questionType.typeId === "1") {
					var loopResult = true;
					$scope.questionType.subOptions.forEach(function(entry) {
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
		questionnaireCollectionService.getQuestionTypeDetails($scope.currentQuestionType.serNum, OAUserId).then(function (response) {
			// Assign value
			$scope.questionType = response.data;

			if($scope.questionType.typeId === "2") {
				$scope.questionType.options.minValue = parseInt($scope.questionType.options.minValue);
				$scope.questionType.options.maxValue = parseInt($scope.questionType.options.maxValue);
				$scope.questionType.options.increment = parseInt($scope.questionType.options.increment);
				$scope.updateSlider();
			}

			if($scope.questionType.subOptions !== null) {
				$scope.questionType.subOptions.forEach(function(entry) {
					entry.order = parseInt(entry.order);
				});
			}

			if (response.data.private === "1")
				$scope.questionType.private = true;
			else
				$scope.questionType.private = false;
			if (response.data.isOwner === "1")
				$scope.questionType.isOwner = true;
			else
				$scope.questionType.isOwner = false;

			$scope.questionType.OAUserId = OAUserId;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function (err) {
			alert('Error occurred getting question details.\r\nCode ' + err.status + " " + err.data);
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


		$scope.addOptions = function () {
			$scope.questionType.subOptions.push({
				description_EN: "",
				description_FR: "",
				order: $scope.questionType.subOptions.length+1,
				OAUserId: OAUserId
			});
		};

		// delete options
		$scope.deleteOptions = function (optionToDelete) {
			var index = $scope.questionType.subOptions.indexOf(optionToDelete);
			if (index > -1) {
				$scope.questionType.subOptions.splice(index, 1);
				$scope.changesMade = true;
			}
		};


		// Submit changes
		$scope.updateQuestionType = function () {
			// Submit form
			$.ajax({
				type: "POST",
				url: "php/questionnaire/update.question_type.php",
				data: $scope.questionType,
				success: function (response) {
					response = JSON.parse(response);

					// Show success or failure depending on response
					if (response.code === 200) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.questionType.name_EN.replace(/<(?:.|\n)*?>/gm, '') + " / " + $scope.questionType.name_FR.replace(/<(?:.|\n)*?>/gm, '') + "\"!";
						$uibModalInstance.close();
						$scope.showBanner();
					}
					else
						alert("An error occurred, code "+response.code+". Please review the error message below.\r\n" + response.message);
				}
			});
		};
	});