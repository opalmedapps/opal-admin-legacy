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
				.post('php/user/checklogin.php', credentials)
				.then(function (response) {
					if (response.data.user) {
						Session.create('123abc', response.data.user);
						return response.data.user;
					}
					else { return $q.reject(response); }

				});
		};

		authService.confirm = function (credentials) {
			return $http
				.post('php/user/checklogin.php', credentials)
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
			.state('login', { url: '/', templateUrl: 'templates/login.html', controller: 'loginController', data: { requireLogin: false } })
			.state('home', { url: '/home', templateUrl: 'templates/home.html', controller: 'homeController', data: { authorizedRoles: [USER_ROLES.all], requireLogin: true } })
			.state('alias', { url: '/alias', templateUrl: "templates/alias.html", controller: "aliasController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('alias-add', { url: '/alias/add', templateUrl: "templates/add-alias.html", controller: "newAliasController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('post', { url: '/post', templateUrl: "templates/post.html", controller: "postController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('post-add', { url: '/post/add', templateUrl: "templates/add-post.html", controller: "newPostController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('educational-material', { url: '/educational-material', templateUrl: "templates/educational-material.html", controller: "eduMatController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('educational-material-add', { url: '/educational-material/add', templateUrl: "templates/add-educational-material.html", controller: "newEduMatController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('hospital-map', { url: '/hospital-map', templateUrl: "templates/hospital-map.html", controller: "hospitalMapController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('hospital-map-add', { url: '/hospital-map/add', templateUrl: "templates/add-hospital-map.html", controller: "newHospitalMapController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('notification', { url: '/notification', templateUrl: "templates/notification.html", controller: "notificationController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('notification-add', { url: '/notification/add', templateUrl: "templates/add-notification.html", controller: "newNotificationController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('patients', { url: '/patients', templateUrl: "templates/patient.html", controller: "patientController", data: { authorizedRoles: [USER_ROLES.admin, USER_ROLES.registrant], requireLogin: true } })
			.state('patients-register', { url: '/patients/register', templateUrl: "templates/patient-registration.html", controller: "patientRegistrationController", data: { authorizedRoles: [USER_ROLES.admin, USER_ROLES.registrant], requireLogin: true } })
			.state('test-result', { url: '/test-result', templateUrl: "templates/test-result.html", controller: "testResultController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('test-result-add', { url: '/test-result/add', templateUrl: "templates/add-test-result.html", controller: "newTestResultController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('cron', { url: '/cron', templateUrl: "templates/cron.html", controller: "cronController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('patient-activity', { url: '/patient-activity', templateUrl: "templates/patient-activity.html", controller: "patientActivityController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('account', { url: '/account', templateUrl: "templates/account.html", controller: "accountController", data: { authorizedRoles: [USER_ROLES.all], requireLogin: true } })
			.state('users', { url: '/users', templateUrl: "templates/user.html", controller: "userController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('user-register', { url: '/users/add', templateUrl: "templates/add-user.html", controller: "newUserController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('email', { url: '/email', templateUrl: "templates/email.html", controller: "emailController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('email-add', { url: '/email/add', templateUrl: "templates/add-email.html", controller: "newEmailController", data: { authorizedRoles: [USER_ROLES.admin], requireLogin: true } })
			.state('install', { url: '/install', templateUrl: "templates/install.html", controller: "installationController", data: { requireLogin: false, installAccess: INSTALL_ACCESS } })
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
			if (installAccess !== undefined) {
				if (!installAccess) {
					event.preventDefault();
				}

			}
		});
	})
	// Initiate Idle counter
	.run(['Idle', function (Idle) {
		Idle.watch();
	}]);
