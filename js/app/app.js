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
  guest: 'guest'
})

// Authentication and authorization service
.factory('AuthService', function ($http, Session, $q, USER_ROLES) {

    var authService = {};

    authService.login = function (credentials) {
        return $http
            .post('php/user/checklogin.php', credentials)
            .then(function (response) {
            	if (response.data.user) {
	                //Session.create(response.data.id, response.data.user.id, response.data.user.role);
	                Session.create(null, response.data.user.id, response.data.user.role);
	                return response.data.user;
	            }
	            else {return $q.reject(response)}

            });
    }

    authService.isAuthenticated = function () {
        return !!Session.retrieve('userId');
    }

    authService.isAuthorized = function (authorizedRoles) {
    	
        if (!angular.isArray(authorizedRoles)) {
            authorizedRoles = [authorizedRoles];
        }

        return (authService.isAuthenticated() && 
            ( authorizedRoles.indexOf(Session.retrieve('userRole')) !== -1 ||
            	authorizedRoles.indexOf(USER_ROLES.all) !== -1 ) );
    }

    return authService;
})

.config(['$urlRouterProvider', '$stateProvider', 'USER_ROLES', function ($urlRouterProvider, $stateProvider, USER_ROLES) {
	$urlRouterProvider.otherwise("/");
	$stateProvider
		.state('login', {url:'/login', templateUrl: 'templates/login.php', controller: 'loginController', data: {requireLogin: false}})
		.state('home', {url:'/', templateUrl: 'templates/home.php', controller: 'homeController', data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('alias', {url:'/alias', templateUrl: "templates/alias.php", controller: "aliasController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('alias.add', {url:'/alias/add', templateUrl: "templates/add-alias.php", controller: "newAliasController"})
		.state('post', {url:'/post', templateUrl: "templates/post.php", controller: "postController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('post.add', {url:'/post/add', templateUrl: "templates/add-post.php", controller: "newPostController"})
		.state('educational-material', {url:'/educational-material', templateUrl: "templates/educational-material.php", controller: "eduMatController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('educational-material.add', {url: '/educational-material/add', templateUrl: "templates/add-educational-material.php", controller: "newEduMatController"})
		.state('hospital-map', {url: '/hospital-map', templateUrl: "templates/hospital-map.php", controller: "hospitalMapController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('hospital-map.add', {url: '/hospital-map/add', templateUrl: "templates/add-hospital-map.php", controller: "newHospitalMapController"})
		.state('notification', {url:'/notification', templateUrl: "templates/notification.php", controller: "notificationController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('notification.add', {url:'/notification/add', templateUrl: "templates/add-notification.php", controller: "newNotificationController"})
		.state('patients', {url:'/patients', templateUrl: "templates/patient.php", controller: "patientController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('patients.register', {url:'/patients/register', templateUrl: "templates/patient-registration.php", controller: "patientRegistrationController"})
		.state('test-result', {url:'/test-result', templateUrl: "templates/test-result.php", controller: "testResultController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('test-result.add', {url:'/test-result/add', templateUrl: "templates/add-test-result.php", controller: "newTestResultController"})
		.state('cron', {url:'/cron', templateUrl: "templates/cron.php", controller: "cronController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('patient-activity', {url:'/patient-activity', templateUrl: "templates/patient-activity.php", controller: "patientActivityController", data: {authorizedRoles: [USER_ROLES.admin], requireLogin: true}})
		.state('protected-route', {url:'/protected', resolve: {auth: function resolveAuthentication(AuthResolver) {return AuthResolver.resolve();}}});
}])

.config(function ($httpProvider) {
	$httpProvider.interceptors.push([
		'$injector', function ($injector) {
			return $injector.get('AuthInterceptor');
		}
	]);
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
// Watches the value of ‘currentUser’ on $rootScope, 
// and will only resolve after currentUser has been set
.factory('AuthResolver', function ($q, $rootScope, $state) {
    return {
        resolve: function() {
            var deferred = $q.defer();
            var unwatch = $rootScope.$watch('currentUser', function (currentUser) {
                if (angular.isDefined(currentUser)) {
                    if (currentUser) {
                        deferred.resolve(currentUser);
                    } else {
                        deferred.reject();
                        $state.go('login');
                    }
                    unwatch();
                }
            });
            return deferred.promise;
        }
    };
})
.run(function ($rootScope, AUTH_EVENTS, AuthService, $state) {

	$rootScope.$on('$stateChangeStart', function (event, next, toParams) {
		var requireLogin = next.data.requireLogin;
		var authorizedRoles = next.data.authorizedRoles;
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
	});
})
// Initiate Idle counter
.run(['Idle', function(Idle) {
  Idle.watch();
}]);

// .config(['$routeProvider', function($routeProvider) { // Set routes
//   $routeProvider.
// 	when("/", {templateUrl: "templates/home.php", controller: "homeController"}).
// 	when("/alias", {templateUrl: "templates/alias.php", controller: "aliasController"}).
// 	when("/login", {templateUrl: "templates/login.php", controller: "loginController"}).
// 	when("/alias/add", {templateUrl: "templates/add-alias.php", controller: "newAliasController"}).
// 	when("/post", {templateUrl: "templates/post.php", controller: "postController"}).
// 	when("/post/add", {templateUrl: "templates/add-post.php", controller: "newPostController"}).
// 	when("/educational-material", {templateUrl: "templates/educational-material.php", controller: "eduMatController"}).
// 	when("/educational-material/add", {templateUrl: "templates/add-educational-material.php", controller: "newEduMatController"}).
// 	when("/hospital-map", {templateUrl: "templates/hospital-map.php", controller: "hospitalMapController"}).
// 	when("/hospital-map/add", {templateUrl: "templates/add-hospital-map.php", controller: "newHospitalMapController"}).
// 	when("/notification", {templateUrl: "templates/notification.php", controller: "notificationController"}).
// 	when("/notification/add", {templateUrl: "templates/add-notification.php", controller: "newNotificationController"}).
// 	when("/patients", {templateUrl: "templates/patient.php", controller: "patientController"}).
// 	when("/patients/register", {templateUrl: "templates/patient-registration.php", controller: "patientRegistrationController"}).
// 	when("/test-result", {templateUrl: "templates/test-result.php", controller: "testResultController"}).
// 	when("/test-result/add", {templateUrl: "templates/add-test-result.php", controller: "newTestResultController"}).
// 	when("/cron", {templateUrl: "templates/cron.php", controller: "cronController"}).
// 	when("/patient-activity", {templateUrl: "templates/patient-activity.php", controller: "patientActivityController"}).
// 	otherwise({redirectTo: '/'});
// }]);

