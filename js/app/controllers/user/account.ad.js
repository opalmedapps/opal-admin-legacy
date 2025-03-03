// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.account.ad', ['ui.bootstrap']).


	/******************************************************************************
	 * Controller for the account page
	 *******************************************************************************/
	controller('account.ad', function ($scope, $rootScope, $translate, $route, $filter, $templateCache, Session) {
		$scope.navMenu = Session.retrieveObject('menu');

		// Set current user
		$scope.currentUser = Session.retrieveObject('user');

		// Initialize a list of languages available
		$scope.languages = [{
			name: "English",
			id: 'EN'
		}, {
			name: "Français",
			id: 'FR'
		}];

		$scope.bannerMessage = "";
		// Function to show page banner
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 5000);
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

		// Initialize account object
		$scope.defaultAccount = {
			user: $scope.currentUser,
			oldPassword: null,
			password: null,
			confirmPassword: null
		};
		$scope.account = jQuery.extend(true, {}, $scope.defaultAccount);

		// Function to reset the account object
		$scope.flushAccount = function () {
			$scope.account = jQuery.extend(true, {}, $scope.defaultAccount);
			$scope.validOldPassword.status = null;
			$scope.validPassword.status = null;
			$scope.validConfirmPassword.status = null;
		};

		// Function when language changes
		$scope.updateLanguage = function (user) {
			var toSend = {
				OAUserId: Session.retrieveObject('user').id,
				language: user.language
			};

			// submit form
			$.ajax({
				type: "POST",
				url: "user/update/language",
				data: toSend,
				success: function (menu) {
					$templateCache.removeAll();
					Session.updateUser(user); // change language in cookies
					$translate.use($scope.currentUser.language.toLowerCase());
					location.reload();
				},
				error: function () {
					alert($filter('translate')('PROFILE.LANGUAGE_ERROR'));
				}
			});
		};
	});

