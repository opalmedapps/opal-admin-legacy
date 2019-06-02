angular.module('opalAdmin.controllers.question.type.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.type.edit', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {
		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;


		// initialize default variables & lists
		$scope.question = {};
		$scope.libraries = [];
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

		// search function
		$scope.searchLibFilter = function (Filter) {
			var keyword = new RegExp($scope.libEntered, 'i');
			return !$scope.libEntered || keyword.test(Filter.name_EN);
		};

		$scope.orderPreview = function () {
			$scope.question.subOptions.sort(function(a,b){
				return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
			});
		};

		/*
		$options["minValue"] <= 0.0 || $options["maxValue"] <= 0.0 || $options["increment"] <= 0.0 || $options["minValue"] >= $options["maxValue"])
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid data.");

        $options["maxValue"] = floatval(floor(($options["maxValue"] - $options["minValue"]) / $options["increment"]) * $options["increment"]) + $options["minValue"];

		* */


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
			if ($scope.question.text_EN && $scope.question.text_FR && $scope.changesMade) {
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
			}).catch(function (response) {
				alert('Error occurred getting libraries. Code '+ response.status +"\r\n" + response.data);
			});

			// Assign value
			$scope.question = response.data;

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

			if (response.data.private === "1")
				$scope.question.private = true;
			else
				$scope.question.private = false;
			if (response.data.final === "1")
				$scope.question.final = true;
			else
				$scope.question.final = false;
			if (response.data.readOnly === "1")
				$scope.question.readOnly = true;
			else
				$scope.question.readOnly = false;
			if (response.data.isOwner === "1")
				$scope.question.isOwner = true;
			else
				$scope.question.isOwner = false;

			$scope.question.OAUserId = OAUserId;

			$scope.selectedLibrary = response.data.libraries;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function (err) {
			alert('Error occurred getting question details.\r\nCode ' + err.status + " " + err.data);
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});

		// Call our API service to get the list of existing answer types

		questionnaireCollectionService.getQuestionTypes(OAUserId).then(function (response) {
			$scope.atFilterList = response.data;
		}).catch(function (response){
			alert('Error occurred getting response types:', response.status, response.data);
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
		};

		questionnaireCollectionService.getLibraries(OAUserId).then(function (response) {
			$scope.groupFilterList = response.data;
		}).catch(function(response) {
			alert('Error occurred getting question libraries:', response.status, response.data);
		});

		$scope.newLibrary = {
			name_EN: "",
			name_FR: "",
			private: 0,
			OAUserId: OAUserId
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

							questionnaireCollectionService.getLibraries(OAUserId).then(function (response) {
								$scope.libraries = [];
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
			// Submit form
			$.ajax({
				type: "POST",
				url: "php/questionnaire/update.question.php",
				data: $scope.question,
				success: function (response) {
					response = JSON.parse(response);

					// Show success or failure depending on response
					if (response.code === 200) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.question.text_EN.replace(/<(?:.|\n)*?>/gm, '') + " / " + $scope.question.text_FR.replace(/<(?:.|\n)*?>/gm, '') + "\"!";
						$uibModalInstance.close();
						$scope.showBanner();
					}
					else
						alert("An error occurred, code "+response.code+". Please review the error message below.\r\n" + response.message);
				}
			});
		};
	});