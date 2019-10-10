angular.module('opalAdmin.controllers.question.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.edit', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {
		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		// initialize default variables & lists
		$scope.question = {};
		$scope.libraries = [];
		$scope.changesMade = false;
		$scope.validSlider = true;
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

		// search function
		$scope.searchLibFilter = function (Filter) {
			var keyword = new RegExp($scope.libEntered, 'i');
			return !$scope.libEntered || keyword.test($scope.language.toUpperCase() === "FR"?Filter.name_FR:Filter.name_EN);
		};

		$scope.orderPreview = function () {
			$scope.question.subOptions.sort(function(a,b){
				return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
			});
		};

		$scope.updateSlider = function () {
			var radiostep = new Array();
			var increment = parseFloat($scope.question.options.increment);
			var minValue = parseFloat($scope.question.options.minValue);
			var maxValue = parseFloat($scope.question.options.maxValue);

			if (minValue <= 0.0 || maxValue <= 0.0 || increment <= 0 || minValue >= maxValue)
				$scope.validSlider = false;
			else {
				maxValue = (Math.floor((maxValue - minValue) / increment) * increment) + minValue;
				$scope.validSlider = true;
				for(var i = minValue; i <= maxValue; i += increment) {
					radiostep.push({"description":" " + i,"description_EN":" " + i,"description_FR":" " + i});
				}
				radiostep[0]["description_EN"] += " " + $scope.question.options.minCaption_EN;
				radiostep[0]["description_FR"] += " " + $scope.question.options.minCaption_FR;
				radiostep[radiostep.length - 1]["description_EN"] += " " + $scope.question.options.maxCaption_EN;
				radiostep[radiostep.length - 1]["description_FR"] += " " + $scope.question.options.maxCaption_FR;
			}
			$scope.question.subOptions = radiostep;
		}

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
			if ($scope.question.question_EN && $scope.question.question_FR && $scope.question.display_EN && $scope.question.display_FR && $scope.changesMade) {
				if($scope.question.typeId === "2") {
					if ($scope.question.options.increment <= 0 || $scope.question.options.minValue <= 0 || $scope.question.options.maxValue <= 0 || $scope.question.options.minValue > $scope.question.options.maxValue || $scope.question.options.minCaption_EN === "" || $scope.question.options.minCaption_FR === "" || $scope.question.options.maxCaption_EN === "" || $scope.question.options.maxCaption_FR === "" )
						return false;
					else
						return true;
				} else if ($scope.question.typeId === "4" || $scope.question.typeId === "1") {
					var loopResult = true;
					$scope.question.subOptions.forEach(function(entry) {
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
		questionnaireCollectionService.getQuestionDetails($scope.currentQuestion.serNum, OAUserId).then(function (response) {
			questionnaireCollectionService.getLibraries(OAUserId).then(function (resp) {
				$scope.libraryFilterList = resp.data;
				$scope.libraryFilterList.forEach(function(entry) {
					if($scope.language.toUpperCase() === "FR")
						entry.name_display = entry.name_FR;
					else
						entry.name_display = entry.name_EN;
				});
			}).catch(function (response) {
				alert('Error occurred getting libraries. Code '+ response.status +"\r\n" + response.data);
			});

			// Assign value
			$scope.question = response.data;

			if($scope.language.toUpperCase() === "FR")
				$scope.question.type_display = $scope.question.type_FR;
			else
				$scope.question.type_display = $scope.question.type_EN;

			if($scope.question.typeId === "2") {
				$scope.question.options.minValue = parseInt($scope.question.options.minValue);
				$scope.question.options.maxValue = parseInt($scope.question.options.maxValue);
				$scope.question.options.increment = parseInt($scope.question.options.increment);
				$scope.updateSlider();
			}

			if($scope.question.subOptions !== null) {
				$scope.question.subOptions.forEach(function(entry) {
					entry.order = parseInt(entry.order);
				});
			}

			$scope.question.private = parseInt($scope.question.private);
			$scope.question.final = parseInt($scope.question.final);
			$scope.question.readOnly = parseInt($scope.question.readOnly);
			$scope.question.isOwner = parseInt($scope.question.isOwner);

			$scope.question.OAUserId = OAUserId;

			$scope.selectedLibrary = response.data.libraries;

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

		$scope.updateLibrary = function (selectedLibrary) {
			$scope.changesMade = true;
			var idx = $scope.selectedLibrary.indexOf(selectedLibrary.serNum);
			if (idx > -1)
				$scope.selectedLibrary.splice(idx, 1);
			else
				$scope.selectedLibrary.push(selectedLibrary.serNum);

		};

		$scope.addOptions = function () {
			$scope.question.subOptions.push({
				description_EN: "",
				description_FR: "",
				order: $scope.question.subOptions.length+1,
				OAUserId: OAUserId
			});
		};

		// delete options
		$scope.deleteOptions = function (optionToDelete) {
			var index = $scope.question.subOptions.indexOf(optionToDelete);
			if (index > -1) {
				$scope.question.subOptions.splice(index, 1);
				$scope.changesMade = true;
			}

			var i = 1;
			$scope.question.subOptions.forEach(function(entry) {
				entry.order = i;
				i++;
			});
		};

		$scope.newLibrary = {
			name_EN: "",
			name_FR: "",
			private: 0,
			OAUserId: OAUserId
		};

		$scope.addNewLib = function () {
			// Prompt to confirm user's action
			var confirmation = confirm($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_EDIT.CONFIRM_LIBRARY') + "\r\n\r\n" + $scope.newLibrary.name_EN + " / "+$scope.newLibrary.name_FR);
			if (confirmation) {
				// write in to db
				$.ajax({
					type: "POST",
					url: "library/insert/library",
					data: $scope.newLibrary,
					success: function (result) {
						result = JSON.parse(result);
						if(result.code === 200) {
							alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_EDIT.SUCCESS_LIBRARY'));

							questionnaireCollectionService.getLibraries(OAUserId).then(function (response) {
								$scope.libraries = [];
								$scope.libraryFilterList = response.data;
								$scope.libraryFilterList.forEach(function(entry) {
									if($scope.language.toUpperCase() === "FR")
										entry.name_display = entry.name_FR;
									else
										entry.name_display = entry.name_EN;
								});
							}).catch(function (response) {
								alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_EDIT.ERROR_GET_LIBRARY') + "\r\n\r\n" + response.status +" - "+ response.data);
							});
						}
						else {
							alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_EDIT.ERROR_SET_LIBRARY') + "\r\n\r\n" + result.code +" - "+ result.message);
						}
					},
					error: function (result) {
						alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_EDIT.ERROR_SET_LIBRARY') + "\r\n\r\n" + result.code +" - "+ result.message);
					}
				});
			}
		};

		// Submit changes
		$scope.updateQuestion = function () {
			var toSubmit = $scope.question;
			if (toSubmit["typeId"] === "2")
				delete toSubmit["subOptions"];
			// Submit form
			$.ajax({
				type: "POST",
				url: "question/update/question",
				data: toSubmit,
				success: function (response) {
					response = JSON.parse(response);

					// Show success or failure depending on response
					if (response.code === 200) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_EDIT.SUCCESS_QUESTION');
						$uibModalInstance.close();
						$scope.showBanner();
					}
					else
						alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_EDIT.ERROR_UPDATE_QUESTION') + "\r\n\r\n" + response.code +" - "+ response.message);
				}
			});
		};
	});