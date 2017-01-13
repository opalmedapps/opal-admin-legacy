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
angular.module('ATO_InterfaceApp', [
  'ATO_InterfaceApp.collections',
  'ATO_InterfaceApp.controllers',
  'ATO_InterfaceApp.services',
  'ngRoute',
  'ui.router'
])

// .constant('AUTH_EVENTS', {
//   loginSuccess: 'auth-login-success',
//   loginFailed: 'auth-login-failed',
//   logoutSuccess: 'auth-logout-success',
//   sessionTimeout: 'auth-session-timeout',
//   notAuthenticated: 'auth-not-authenticated',
//   notAuthorized: 'auth-not-authorized'
// })
// .constant('USER_ROLES', {
//   all: '*',
//   admin: 'admin',
//   editor: 'editor',
//   guest: 'guest'
// })

// Authentication and authorization service
// .factory('AuthService', function ($http, Session) {

//     var authService = {};

//     authService.login = function (credentials) {
//         return $http
//             .post('/login', credentials)
//             .then(function (response) {
//                 Session.create(response.data.id, response.data.user.id,
//                     response.data.user.role);
//                 return res.data.user;
//             });
//     }

//     authService.isAuthenticated = function () {
//         return !!Session.userId;
//     }

//     authService.isAuthorized = function (authorizedRoles) {
//         if (!angular.isArray(authorizedRoles)) {
//             authorizedRoles = [authorizedRoles];
//         }
//         return (authService.isAuthenticated() && 
//             authorizedRoles.indexOf(Session.userRole) !== -1);
//     }

//     return authService;
// })

// .config(['$urlRouterProvider', '$stateProvider', function ($urlRouterProvider, $stateProvider, USER_ROLES) {
// 	$urlRouterProvider.otherwise("/");
// 	$stateProvider
// 		.state('login', {url:'/login', templateUrl: 'templates/login.php', controller: 'loginController'})
// 		.state('home', {url:'/', templateUrl: 'templates/home.php', controller: 'homeController'})
// 		.state('alias', {url:'/alias', templateUrl: "templates/alias.php", controller: "aliasController"})
// 		.state('alias.add', {url:'/alias/add', templateUrl: "templates/add-alias.php", controller: "newAliasController"})
// 		.state('post', {url:'/post', templateUrl: "templates/post.php", controller: "postController"})
// 		.state('post.add', {url:'/post/add', templateUrl: "templates/add-post.php", controller: "newPostController"})
// 		.state('educational-material', {url:'/educational-material', templateUrl: "templates/educational-material.php", controller: "eduMatController"})
// 		.state('educational-material.add', {url: '/educational-material/add', templateUrl: "templates/add-educational-material.php", controller: "newEduMatController"})
// 		.state('hospital-map', {url: '/hospital-map', templateUrl: "templates/hospital-map.php", controller: "hospitalMapController"})
// 		.state('hospital-map.add', {url: '/hospital-map/add', templateUrl: "templates/add-hospital-map.php", controller: "newHospitalMapController"})
// 		.state('notification', {url:'/notification', templateUrl: "templates/notification.php", controller: "notificationController"})
// 		.state('notification.add', {url:'/notification/add', templateUrl: "templates/add-notification.php", controller: "newNotificationController"})
// 		.state('patients', {url:'/patients', templateUrl: "templates/patient.php", controller: "patientController"})
// 		.state('patients.register', {url:'/patients/register', templateUrl: "templates/patient-registration.php", controller: "patientRegistrationController"})
// 		.state('test-result', {url:'/test-result', templateUrl: "templates/test-result.php", controller: "testResultController"})
// 		.state('test-result.add', {url:'/test-result/add', templateUrl: "templates/add-test-result.php", controller: "newTestResultController"})
// 		.state('cron', {url:'/cron', templateUrl: "templates/cron.php", controller: "cronController"})
// 		.state('protected-route', {url:'/protected', resolve: {auth: function resolveAuthentication(AuthResolver) {return AuthResolver.resolve();}}});
// }])

// .config(function ($httpProvider) {
// 	$httpProvider.interceptors.push([
// 		'$injector', function ($injector) {
// 			return $injector.get('AuthInterceptor');
// 		}
// 	]);
// })

// To broadcast the notAuthenticated / notAuthorized 
// event based on the HTTP response status code
// .factory('AuthInterceptor', function ($rootScope, $q, AUTH_EVENTS) {
// 	return {
// 		responseError: function (response) { 
// 			$rootScope.$broadcast({
// 				401: AUTH_EVENTS.notAuthenticated,
// 				403: AUTH_EVENTS.notAuthorized,
// 				419: AUTH_EVENTS.sessionTimeout,
// 				440: AUTH_EVENTS.sessionTimeout
// 			}[response.status], response);
// 			return $q.reject(response);
// 		}
// 	};
// })
// .run(function ($rootScope, AUTH_EVENTS, AuthService) {
// 	$rootScope.$on('$stateChangeStart', function (event, next) {
// 		var authorizedRoles = next.data.authorizedRoles;
// 		if (!AuthService.isAuthorized(authorizedRoles)) {
// 			event.preventDefault();
// 			if (AuthService.isAuthenticated()) {
// 				// user is not allowed
// 				$rootScope.$broadcast(AUTH_EVENTS.notAuthorized);
// 			} else {
// 				// user is not logged in 
// 				$rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
// 			}
// 		}
// 	});
// });

.config(['$routeProvider', function($routeProvider) { // Set routes
  $routeProvider.
	when("/", {templateUrl: "templates/home.php", controller: "homeController"}).
	when("/alias", {templateUrl: "templates/alias.php", controller: "aliasController"}).
	when("/login", {templateUrl: "templates/login.php", controller: "loginController"}).
	when("/alias/add", {templateUrl: "templates/add-alias.php", controller: "newAliasController"}).
	when("/post", {templateUrl: "templates/post.php", controller: "postController"}).
	when("/post/add", {templateUrl: "templates/add-post.php", controller: "newPostController"}).
	when("/educational-material", {templateUrl: "templates/educational-material.php", controller: "eduMatController"}).
	when("/educational-material/add", {templateUrl: "templates/add-educational-material.php", controller: "newEduMatController"}).
	when("/hospital-map", {templateUrl: "templates/hospital-map.php", controller: "hospitalMapController"}).
	when("/hospital-map/add", {templateUrl: "templates/add-hospital-map.php", controller: "newHospitalMapController"}).
	when("/notification", {templateUrl: "templates/notification.php", controller: "notificationController"}).
	when("/notification/add", {templateUrl: "templates/add-notification.php", controller: "newNotificationController"}).
	when("/patients", {templateUrl: "templates/patient.php", controller: "patientController"}).
	when("/patients/register", {templateUrl: "templates/patient-registration.php", controller: "patientRegistrationController"}).
	when("/test-result", {templateUrl: "templates/test-result.php", controller: "testResultController"}).
	when("/test-result/add", {templateUrl: "templates/add-test-result.php", controller: "newTestResultController"}).
	when("/cron", {templateUrl: "templates/cron.php", controller: "cronController"}).
	otherwise({redirectTo: '/'});
}]);

