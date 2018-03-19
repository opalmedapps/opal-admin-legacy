angular.module('opalAdmin.controllers.testResult.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('testResult.edit', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, testResultCollectionService, educationalMaterialCollectionService, Session) {

		// Default Boolean
		$scope.changesMade = false; // changes been made?

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		$scope.testResult = {}; // Initialize test result object

		// Initialize list to hold test names
		$scope.testList = [];
		$scope.eduMatList = [];

		// Initialize lists to hold distinct test groups
		$scope.TestResultGroups_EN = [];
		$scope.TestResultGroups_FR = [];

		// Call our API to get the list of test result groups
		testResultCollectionService.getTestResultGroups().then(function (response) {
			$scope.TestResultGroups_EN = response.data.EN;
			$scope.TestResultGroups_FR = response.data.FR;
		}).catch(function(response) {
			console.error('Error occurred test result groups:', response.status, response.data);
		});

		// Initialize search field 
		$scope.testFilter = "";
		$scope.eduMatFilter = null;

		// Call our API service to get the list of educational material
		educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
			$scope.eduMatList = response.data; // Assign value
		}).catch(function(response) {
			console.error('Error occurred getting educational material list:', response.status, response.data);
		});

		// Function to assign search field when textbox changes
		$scope.changeTestFilter = function (field) {
			$scope.testFilter = field;
		};


		// Function for search through the test names
		$scope.searchTestsFilter = function (Filter) {
			var keyword = new RegExp($scope.testFilter, 'i');
			return !$scope.testFilter || keyword.test(Filter.name);
		};

		// Function to assign eduMatFilter when textbox is changing 
		$scope.changeEduMatFilter = function (eduMatFilter) {
			$scope.eduMatFilter = eduMatFilter;
		};

		// Function for searching through expression names
		$scope.searchEduMatsFilter = function (edumat) {
			var keyword = new RegExp($scope.eduMatFilter, 'i');
			return !$scope.eduMatFilter || keyword.test(edumat.name_EN);
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
		// Show processing dialog
		$scope.showProcessingModal();

		// Call our API service to get the current test result details
		testResultCollectionService.getTestResultDetails($scope.currentTestResult.serial).then(function (response) {

			$scope.testResult = response.data;

			// Call our API service to get the list of test names
			testResultCollectionService.getTestNames().then(function (response) {

				$scope.testList = checkAdded(response.data);


				processingModal.close(); // hide modal
				processingModal = null; // remove reference

			}).catch(function(response) {
				console.error('Error occurred getting test names:', response.status, response.data);
			});

		}).catch(function(response) {
			console.error('Error occurred getting test result details:', response.status, response.data);
		});

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			$scope.changesMade = true;
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

		// Function to assign '1' to existing test names
		function checkAdded(testList) {
			angular.forEach($scope.testResult.tests, function (selectedTest) {
				var selectedName = selectedTest.name;
				angular.forEach(testList, function (test) {
					var name = test.name;
					if (name == selectedName) {
						test.added = 1;
					}
				});
			});

			return testList;
		}

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.testResult.name_EN && $scope.testResult.name_FR && $scope.testResult.description_EN
				&& $scope.testResult.description_FR && $scope.testResult.group_EN && $scope.testResult.group_FR
				&& $scope.checkTestsAdded($scope.testList) && $scope.additionalLinksComplete && $scope.changesMade) {
				return true;
			}
			else return false;
		};

		$scope.eduMatUpdate = function (eduMat) {

			$scope.testResult.eduMat = eduMat;
			// Toggle boolean
			$scope.setChangesMade();
		};

		// Function to add an additional link to the test result
		$scope.addAdditionalLink = function () {
			$scope.testResult.additional_links.push({
				name_EN: "",
				name_FR: "",
				url_EN: "",
				url_FR: "",
				serial: null
			});
			$scope.setChangesMade();
		};

		// Function to remove an additional link from the test result
		$scope.removeAdditionalLink = function (index) {
			$scope.testResult.additional_links.splice(index, 1);

			if (!$scope.testResult.additional_links) {
				$scope.testResult.additional_links = [];
			}
			$scope.setChangesMade();
		};

		// Function to add / remove a test
		$scope.toggleTestSelection = function (test) {

			$scope.setChangesMade();

			// If originally added, remove it
			if (test.added) {

				test.added = 0; // added parameter

			}
			else { // Originally not added, add it

				test.added = 1;


			}
		};

		// Function to return boolean for # of added tests
		$scope.checkTestsAdded = function (testList) {

			var addedParam = false;
			angular.forEach(testList, function (test) {
				if (test.added)
					addedParam = true;
			});
			if (addedParam)
				return true;
			else
				return false;
		};

		$scope.setChangesMade = function () {
			$scope.changesMade = true;
			$scope.additionalLinksComplete = true;
			if($scope.testResult.additional_links) {
				angular.forEach($scope.testResult.additional_links, function (link) {
					if (!link.name_EN || !link.name_FR || !link.url_EN 
						|| !link.url_FR) {
						$scope.additionalLinksComplete = false;
					}
				});
			}
		};

		// Submit changes
		$scope.updateTestResult = function () {

			if ($scope.checkForm()) {

				$scope.testResult.tests = [];
				// Fill in the tests from testList
				angular.forEach($scope.testList, function (test) {
					if (test.added)
						$scope.testResult.tests.push(test.name);
				});

				// Log who updated test result 
				var currentUser = Session.retrieveObject('user');
				$scope.testResult.user = currentUser;

				// Submit form
				$.ajax({
					type: "POST",
					url: "php/test-result/update.test_result.php",
					data: $scope.testResult,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.testResult.name_EN + "/ " + $scope.testResult.name_FR + "\"!";
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

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});