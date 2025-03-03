// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.question.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.delete', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, uiGridConstants, Session, ErrorHandler) {

		// Submit delete
		$scope.deleteQuestion = function () {
			$.ajax({
				type: "POST",
				url: "question/delete/question",
				data: {"ID": $scope.questionToDelete.serNum, "OAUserId": Session.retrieveObject('user').id},
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.DELETED');
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.ERROR'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});