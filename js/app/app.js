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

	.constant('MODULE', {
		alias: 1,
		post: 2,
		edu_mat: 3,
		hospital_map: 4,
		notification: 5,
		test_results: 6,
		questionnaire: 7,
		publication: 8,
		diagnosis_translation: 9,
		cron_log: 10,
		patient: 11,
		user: 12,
		study: 13,
		email: 14,
		custom_code: 15,
		role: 16,
		alert: 17,
		audit: 18,
		sms: 22,
		patient_administration: 23,
	})

	.constant('HTTP_CODE', {
		success: 200,
		badRequestError: 400,
		notAuthenticatedError: 401,
		forbiddenAccessError: 403,
		notFoundError: 404,
		sessionTimeoutError: 419,
		loginTimeoutError: 440,
		httpToHttpsError: 497,
		internalServerError: 500,
		badGatewayError: 502,
	})

	.constant('USER_ROLES', {
		admin: '1',
		registrant: '4',
	})


	// Authentication and authorization service
	.factory('AuthService', function ($http, Session, $q, USER_ROLES) {

		var authService = {};

		authService.login = function (username, password) {

			let oaPromise = $http.post( // Log in to the old Opal Admin API
				"user/validate-login",
				$.param({
					username: username,
					password: password,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			); 
			
			return $http.post( // Log in to the new back end API
				"http://127.0.0.1:8000/api/auth/login/",
				{
					"username": username,
					"password": password,
				},
				{
					"withCredentials": true
				}
			).then(
				function (response) { return oaPromise; }, // Success
				function (response) { 			  // Error
					console.error('Unable to connect to the api-backend:', response.status);
					return oaPromise;
				}
			);
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

	.config(['$urlRouterProvider', '$stateProvider', function ($urlRouterProvider, $stateProvider) {
		$urlRouterProvider.otherwise("/");
		$stateProvider
			.state('login', { url: '/', templateUrl: 'templates/login/login.html', controller: 'login', data: { requireLogin: false } })
			.state('home', { url: '/home', templateUrl: 'templates/home/home.html', controller: "home", data: {  requireLogin: true } })
			.state('alias', { url: '/alias', templateUrl: "templates/alias/alias.html", controller: "alias", data: { requireLogin: true } })
			.state('alias-add', { url: '/alias/add', templateUrl: "templates/alias/add.alias.html", controller: "alias.add", data: { requireLogin: true } })
			.state('post', { url: '/post', templateUrl: "templates/post/post.html", controller: "post", data: { requireLogin: true } })
			.state('post-add', { url: '/post/add', templateUrl: "templates/post/add.post.html", controller: "post.add", data: { requireLogin: true } })
			.state('educational-material', { url: '/educational-material', templateUrl: "templates/educational-material/educational-material.html", controller: "educationalMaterial", data: { requireLogin: true } })
			.state('educational-material-add', { url: '/educational-material/add', templateUrl: "templates/educational-material/add.educational-material.html", controller: "educationalMaterial.add", data: { requireLogin: true } })
			.state('hospital-map', { url: '/hospital-map', templateUrl: "templates/hospital-map/hospital-map.html", controller: "hospitalMap", data: { requireLogin: true } })
			.state('hospital-map-add', { url: '/hospital-map/add', templateUrl: "templates/hospital-map/add.hospital-map.html", controller: "hospitalMap.add", data: { requireLogin: true } })
			.state('notification', { url: '/notification', templateUrl: "templates/notification/notification.html", controller: "notification", data: { requireLogin: true } })
			.state('notification-add', { url: '/notification/add', templateUrl: "templates/notification/add.notification.html", controller: "notification.add", data: { requireLogin: true } })
			.state('patients/menu', { url: '/patients/menu', templateUrl: "templates/patient/menu-main.html", controller: "patient", data: { requireLogin: true, accessible: true } })
			.state('patients', { url: '/patients', templateUrl: "templates/patient/patient.html", controller: "patient", data: { requireLogin: true } })
			.state('patients/activity', { url: '/patients/activity', templateUrl: "templates/patient/patient-activity.html", controller: "patientActivity", data: { requireLogin: true } })
			.state('patients/report', { url: '/patients/report', templateUrl: "templates/patient/patient-report.html", controller: "patientReportHandler", data: { requireLogin: true } })
			.state('patients/report/individual', { url: '/patients/report/individual', templateUrl: "templates/patient/individual-reports.html", controller: "patientReports", data: { requireLogin: true } })
			.state('patients/report/group', { url: '/patients/report/group', templateUrl: "templates/patient/group-reports.html", controller: "groupReports", data: { requireLogin: true } })
			.state('test-result', { url: '/test-result', templateUrl: "templates/test-result/test-result.html", controller: "testResult", data: { requireLogin: true } })
			.state('test-result-add', { url: '/test-result/add', templateUrl: "templates/test-result/add.test-result.html", controller: "testResult.add", data: { requireLogin: true } })
			.state('cron', { url: '/cron', templateUrl: "templates/cron/cron.html", controller: "cron", data: { requireLogin: true } })
			.state('ad-account', { url: '/account-ad', templateUrl: "templates/user/account.ad.html", controller: "account", data: { requireLogin: true } })
			.state('account', { url: '/account', templateUrl: "templates/user/account.html", controller: "account", data: { requireLogin: true } })
			.state('users', { url: '/users', templateUrl: "templates/user/user.html", controller: "user", data: { requireLogin: true } })
			.state('user-ad-register', { url: '/users/add-ad', templateUrl: "templates/user/add.user.ad.html", controller: "user.add.ad", data: { requireLogin: true } })
			.state('user-register', { url: '/users/add', templateUrl: "templates/user/add.user.html", controller: "user.add", data: { requireLogin: true } })
			.state('email', { url: '/email', templateUrl: "templates/email/email.html", controller: "email", data: { requireLogin: true } })
			.state('email-add', { url: '/email/add', templateUrl: "templates/email/add.email.html", controller: "email.add", data: { requireLogin: true } })
			.state('questionnaire/menu', { url: '/questionnaire/menu', templateUrl: "templates/questionnaire/menu-main.html", controller: "questionnaire", data: { requireLogin: true, accessible: true } })
			.state('questionnaire', { url: '/questionnaire', templateUrl: "templates/questionnaire/questionnaire.html", controller: "questionnaire", data: { requireLogin: true, accessible: true } })
			.state('questionnaire-add', { url: '/questionnaire/add', templateUrl: "templates/questionnaire/add.questionnaire.html", controller: "questionnaire.add", data: { requireLogin: true, accessible: true } })
			.state('publication', { url: '/publication', templateUrl: "templates/publication/publication.html", controller: "publication", data: { requireLogin: true, accessible: true } })
			.state('publication-add', { url: '/publication/add', templateUrl: "templates/publication/add.publication.html", controller: "publication.add", data: { requireLogin: true, accessible: true } })
			.state('questionnaire/question', { url: '/questionnaire/question', templateUrl: "templates/questionnaire/question.html", controller: "question", data: { requireLogin: true, accessible: true } })
			.state('questionnaire/question-add', { url: '/questionnaire/question/add', templateUrl: "templates/questionnaire/add.question.html", controller: "question.add", data: { requireLogin: true, accessible: true } })
			.state('questionnaire/template-question', { url: '/questionnaire/template-question', templateUrl: "templates/questionnaire/template.question.html", controller: "template.question", data: { requireLogin: true, accessible: true } })
			.state('questionnaire/template-question/add', { url: '/questionnaire/template-question/add', templateUrl: "templates/questionnaire/add.template.question.html", controller: "template.question.add", data: { requireLogin: true, accessible: true } })
			.state('questionnaire-completed', { url: '/questionnaire/completed', templateUrl: "templates/questionnaire/completed.questionnaire.html", controller: "questionnaire", data: { requireLogin: true, accessible: true } })
			.state('diagnosis-translation', { url: '/diagnosis-translation', templateUrl: "templates/diagnosis/diagnosis-translation.html", controller: "diagnosisTranslation", data: { requireLogin: true } })
			.state('diagnosis-translation-add', { url: '/diagnosis-translation/add', templateUrl: "templates/diagnosis/add.diagnosis-translation.html", controller: "diagnosisTranslation.add", data: { requireLogin: true } })
			.state('custom-code', { url: '/custom-code', templateUrl: "templates/custom-code/custom.codes.html", controller: "customCode", data: { requireLogin: true } })
			.state('custom-code-add', { url: '/custom-code/add', templateUrl: "templates/custom-code/add.custom.code.html", controller: "customCode.add", data: { requireLogin: true } })
			.state('study', { url: '/study', templateUrl: "templates/study/studies.html", controller: "study", data: { requireLogin: true } })
			.state('study-add', { url: '/study/add', templateUrl: "templates/study/add.study.html", controller: "study.add", data: { requireLogin: true } })
			.state('role', { url: '/role', templateUrl: "templates/role/roles.html", controller: "role", data: { requireLogin: true } })
			.state('role-add', { url: '/role/add', templateUrl: "templates/role/add.role.html", controller: "role.add", data: { requireLogin: true } })
			.state('alert', { url: '/alert', templateUrl: "templates/alert/alerts.html", controller: "alert", data: { requireLogin: true } })
			.state('alert-add', { url: '/alert/add', templateUrl: "templates/alert/add.alert.html", controller: "alert.add", data: { requireLogin: true } })
			.state('audit', { url: '/audit', templateUrl: "templates/audit/audits.html", controller: "audit", data: { requireLogin: true } })
			.state('user-activity', { url: '/user-activity', templateUrl: "templates/user/user-activity.html", controller: "userActivity", data: { requireLogin: true } })
			.state('protected-route', { url: '/protected', resolve: { auth: function resolveAuthentication(AuthResolver) { return AuthResolver.resolve(); } } })
			.state('sms',{ url: '/sms', templateUrl: "templates/sms/sms.html", controller: "sms", data:{ requireLogin: false } })
			.state('sms/message',{ url: '/sms/message', templateUrl: "templates/sms/add.sms.html", controller: "add.sms", data:{ requireLogin: false } })
<<<<<<< HEAD
			.state('patient-administration',{ url: '/patient-administration', templateUrl: "templates/patient-administration/patient.administration.html", controller: "patient.administration", data:{ requireLogin: true } })
			.state('parking', { url: 'http://127.0.0.1:8000/', external: true, data: { requireLogin: true }});

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
		$translateProvider.preferredLanguage(navigator.language.toLowerCase()==="fr"?"fr":"en");
		$translateProvider.useSanitizeValueStrategy('escaped');
	})

	// To broadcast the notAuthenticated / notAuthorized
	// event based on the HTTP response status code
	.factory('AuthInterceptor', function ($rootScope, $q, AUTH_EVENTS) {
		return {
			responseError: function (response) {
				$rootScope.$broadcast({
					401: AUTH_EVENTS.notAuthenticated,
					// 403: AUTH_EVENTS.notAuthorized,
					419: AUTH_EVENTS.sessionTimeout,
					440: AUTH_EVENTS.sessionTimeout
				}[response.status], response);
				return $q.reject(response);
			}
		};
	})
	.run(function ($rootScope, AUTH_EVENTS, AuthService, $state, $window) {

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
			if (next.external) {
				event.preventDefault();
				$window.open(next.url, '_self');
			}
		});
	})
	// Initiate Idle counter
	.run(['Idle', function (Idle) {
		Idle.watch();
	}]);
