angular.module('opalAdmin.controllers.email', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Controller for the email page
	*******************************************************************************/
	controller('email', function ($scope, $uibModal, $filter, $state, $sce, emailCollectionService, Session) {

		// Function to go to add email page
		$scope.goToAddEmail = function () {
			$state.go('email-add');
		};

		// Function to control search engine model
		$scope.filterEmail = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editEmail(row.entity)">' +
			'<strong><a href="">{{row.entity.subject_EN}} / {{row.entity.subject_FR}}</a></strong></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editEmail(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteEmail(row.entity)">Delete</a></strong></div>';

		// Search engine for table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['subject_EN'].forEach(function (field) {
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

		// Table options for email list
		$scope.gridOptions = {
			data: 'emailList',
			columnDefs: [
				{ field: 'subject_EN', displayName: 'Title (EN)', cellTemplate: cellTemplateName, width: '40%' },
				{ field: 'type', displayName: 'Type', width: '45%' },
				{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, sortable: false }
			],
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing emails
		$scope.emailList = [];

		// Initialize an object for delete an email
		$scope.emailToDelete = {};

		// Call our API to get the list of existing emails
		emailCollectionService.getEmails().then(function (response) {
			$scope.emailList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting email list:', response.status, response.data);
		});

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

		// Initialize a scope variable for a selected email
		$scope.currentEmail = {};

		// Function for when the email has been clicked for editing
		$scope.editEmail = function (email) {

			$scope.currentEmail = email;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/email/edit.email.html',
				controller: 'email.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the email list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing emails
				emailCollectionService.getEmails().then(function (response) {

					// Assign the retrieved response
					$scope.emailList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting email list:', response.status, response.data);
				});
			});
		};

		// Function for when the email has been clicked for deletion
		// Open a modal
		$scope.deleteEmail = function (currentEmail) {

			// Assign selected email as the item to delete
			$scope.emailToDelete = currentEmail;

			var modalInstance = $uibModal.open({
				templateUrl: 'templates/email/delete.email.html',
				controller: 'email.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the map list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing emails
				emailCollectionService.getEmails().then(function (response) {
					// Assign the retrieved response
					$scope.emailList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting email list:', response.status, response.data);
				});
			});
		};

	});
