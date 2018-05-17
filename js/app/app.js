/* Angular module */
/*
 * Constant:  To define all of the available event codes in a central place. If you ever
 * want to give all editors the same rights as administrators, you can simply change the
 * value of editor to ‘admin’.
 *
 * Config: To provide location directives when certain links are contained in the url.
 * All pages have a template and a controller module that handles all functions and variables for a given page.
 * If there is an unknown url path, we redirect to the home page.
 *
 */

'use strict';
angular.module('opalAdmin', [
	'opalAdmin.collections',
	'opalAdmin.controllers',
	'opalAdmin.services',
	'ngRoute',
	'ui.router',
	'ngCookies',
	'ngIdle'
])

	.constant('AUTH_EVENTS', {
		loginSuccess: 'auth-login-success',
		loginFailed: 'auth-login-failed',
		logoutSuccess: 'auth-logout-success',
		sessionTimeout: 'auth-session-timeout',
		notAuthenticated: 'auth-not-authenticated',
		notAuthorized: 'auth-not-authorized'
	})
	.constant('USER_ROLES', {
		all: '*',
		admin: 'admin',
		editor: 'editor',
		guest: 'guest',
		registrant: 'registrant',
		clinician: 'clinician',
		manager: 'manager',
		educationCreator: 'education-creator'
	})

	// Authentication and authorization service
	.factory('AuthService', function ($http, Session, $q, USER_ROLES) {

		var authService = {};

		authService.login = function (credentials) {
			return $http
				.post('php/user/validate_login.php', credentials)
				.then(function (response) {
					if (response.data.user) {
						Session.create(response.data.user);
						return response.data.user;
					}
					else { return $q.reject(response); }

				});
		};

		authService.confirm = function (credentials) {
			return $http
				.post('php/user/validate_login.php', credentials)
				.then(function (response) {
					if (response.data.success) {
						return response.success;
					}
					else { return $q.reject(response); }
				});
		};

		authService.isAuthenticated = function () {
			return !!Session.retrieveObject('user');
		};

		authService.isAuthorized = function (authorizedRoles) {

			if (!angular.isArray(authorizedRoles)) {
				authorizedRoles = [authorizedRoles];
			}

			return (authService.isAuthenticated() &&
				(authorizedRoles.indexOf(Session.retrieveObject('user').role) !== -1 ||
					authorizedRoles.indexOf(USER_ROLES.all) !== -1));
		};

		return authService;
	})

	.config(['$urlRouterProvider', '$stateProvider', 'USER_ROLES', function ($urlRouterProvider, $stateProvider, USER_ROLES) {
		$urlRouterProvider.otherwise("/");
		$stateProvider
			.state('login', { url: '/', templateUrl: 'templates/login/login.html', controller: 'login', data: { requireLogin: false } })
			.state('home', { url: '/home', templateUrl: 'templates/home/home.html', data: { authorizedRoles: [USER_ROLES.all], requireLogin: true } })
			.state('alias', { url: '/alias', templateUrl: "templates/alias/alias.html", controller: "alias", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('alias-add', { url: '/alias/add', templateUrl: "templates/alias/add.alias.html", controller: "alias.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('post', { url: '/post', templateUrl: "templates/post/post.html", controller: "post", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('post-add', { url: '/post/add', templateUrl: "templates/post/add.post.html", controller: "post.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('educational-material', { url: '/educational-material', templateUrl: "templates/educational-material/educational-material.html", controller: "educationalMaterial", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('educational-material-add', { url: '/educational-material/add', templateUrl: "templates/educational-material/add.educational-material.html", controller: "educationalMaterial.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('hospital-map', { url: '/hospital-map', templateUrl: "templates/hospital-map/hospital-map.html", controller: "hospitalMap", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('hospital-map-add', { url: '/hospital-map/add', templateUrl: "templates/hospital-map/add.hospital-map.html", controller: "hospitalMap.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('notification', { url: '/notification', templateUrl: "templates/notification/notification.html", controller: "notification", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('notification-add', { url: '/notification/add', templateUrl: "templates/notification/add.notification.html", controller: "notification.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('patients', { url: '/patients', templateUrl: "templates/patient/patient.html", controller: "patient", data: { authorizedRoles: [USER_ROLES.admin, USER_ROLES.registrant], requireLogin: true } })
			.state('patients-register', { url: '/patients/register', templateUrl: "templates/patient/patient-registration.html", controller: "patientRegistration", data: { authorizedRoles: [USER_ROLES.admin, USER_ROLES.registrant], requireLogin: true } })
			.state('test-result', { url: '/test-result', templateUrl: "templates/test-result/test-result.html", controller: "testResult", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('test-result-add', { url: '/test-result/add', templateUrl: "templates/test-result/add.test-result.html", controller: "testResult.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('cron', { url: '/cron', templateUrl: "templates/cron/cron.html", controller: "cron", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('patient-activity', { url: '/patient-activity', templateUrl: "templates/patient/patient-activity.html", controller: "patientActivity", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('account', { url: '/account', templateUrl: "templates/user/account.html", controller: "account", data: { authorizedRoles: [USER_ROLES.all], requireLogin: true } })
			.state('users', { url: '/users', templateUrl: "templates/user/users.html", controller: "user", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('user-register', { url: '/users/add', templateUrl: "templates/user/add.user.html", controller: "user.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('email', { url: '/email', templateUrl: "templates/email/email.html", controller: "email", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('email-add', { url: '/email/add', templateUrl: "templates/email/add.email.html", controller: "email.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('questionnaire-menu', { url: '/questionnaire/menu', templateUrl: "templates/questionnaire/questionnaire-main-menu.html", controller: "questionnaire", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true, accessible: true } })
			.state('questionnaire', { url: '/questionnaire', templateUrl: "templates/questionnaire/questionnaire.html", controller: "questionnaire", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true, accessible: true } })
			.state('questionnaire-add', { url: '/questionnaire/add', templateUrl: "templates/questionnaire/add.questionnaire.html", controller: "questionnaire.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true, accessible: true } })
			.state('questionnaire-question', { url: '/questionnaire/question', templateUrl: "templates/questionnaire/question.html", controller: "question", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true, accessible: true } })
      		.state('questionnaire-question-add', { url: '/questionnaire/question/add', templateUrl: "templates/questionnaire/add.question.html", controller: "question.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true, accessible: true } })
			.state('questionnaire-completed', { url: '/questionnaire/completed', templateUrl: "templates/questionnaire/completed.questionnaire.html", controller: "questionnaire", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true, accessible: true } })
			.state('legacy-questionnaire', { url: '/legacy-questionnaire', templateUrl: "templates/legacy-questionnaire/legacy-questionnaire.html", controller: "legacyQuestionnaire", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('legacy-questionnaire-add', { url: '/legacy-questionnaire/add', templateUrl: "templates/legacy-questionnaire/add.legacy-questionnaire.html", controller: "legacyQuestionnaire.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('diagnosis-translation', { url: '/diagnosis-translation', templateUrl: "templates/diagnosis/diagnosis-translation.html", controller: "diagnosisTranslation", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('diagnosis-translation-add', { url: '/diagnosis-translation/add', templateUrl: "templates/diagnosis/add.diagnosis-translation.html", controller: "diagnosisTranslation.add", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('user-activity', { url: '/user-activity', templateUrl: "templates/user/user-activity.html", controller: "userActivity", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('protected-route', { url: '/protected', resolve: { auth: function resolveAuthentication(AuthResolver) { return AuthResolver.resolve(); } } });
	}])

	.config(function ($httpProvider) {
		$httpProvider.interceptors.push([
			'$injector', function ($injector) {
				return $injector.get('AuthInterceptor');
			}
		]);
	})

	.config(function ($translateProvider) {
		// load static translation files
		$translateProvider.useStaticFilesLoader({
			prefix: 'translate/locale-',
			suffix: '.json'
		});
		// load 'en' table on startup
		$translateProvider.preferredLanguage('en');
		// Enable escaping of HTML
		$translateProvider.useSanitizeValueStrategy('escaped');
	})

	// To broadcast the notAuthenticated / notAuthorized
	// event based on the HTTP response status code
	.factory('AuthInterceptor', function ($rootScope, $q, AUTH_EVENTS) {
		return {
			responseError: function (response) {
				$rootScope.$broadcast({
					401: AUTH_EVENTS.notAuthenticated,
					403: AUTH_EVENTS.notAuthorized,
					419: AUTH_EVENTS.sessionTimeout,
					440: AUTH_EVENTS.sessionTimeout
				}[response.status], response);
				return $q.reject(response);
			}
		};
	})
	.run(function ($rootScope, AUTH_EVENTS, AuthService, $state) {

		$rootScope.$on('$stateChangeStart', function (event, next, toParams) {
			var requireLogin = next.data.requireLogin;
			var authorizedRoles = next.data.authorizedRoles;
			var installAccess = next.data.installAccess;
			var accessible = next.data.accessible;

			if (!AuthService.isAuthorized(authorizedRoles) && requireLogin) {
				event.preventDefault();

				if (AuthService.isAuthenticated()) {
					// user is not allowed
					$rootScope.$broadcast(AUTH_EVENTS.notAuthorized);
				} else {
					// user is not logged in
					$rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
				}
			}
			if (accessible !== undefined) {
				if (!accessible) {
					event.preventDefault();
					// user is not allowed
					$rootScope.$broadcast(AUTH_EVENTS.notAuthorized);
				}
			}
		});
	})
	// Initiate Idle counter
	.run(['Idle', function (Idle) {
		Idle.watch();
	}]);
