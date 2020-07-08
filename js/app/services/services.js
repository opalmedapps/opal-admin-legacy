// Angular Service
// 

angular.module('opalAdmin.services', [])

	.service('Session', function ($cookies) {
		this.create = function (user) {
			$cookies.putObject('user', user);
		};
		this.retrieve = function (data) {
			return $cookies.get(data);
		};
		this.retrieveObject = function (data) {
			return $cookies.getObject(data);
		};
		this.update = function (user) {
			this.destroy();
			this.create(user);
		};
		this.destroy = function () {
			$cookies.remove('user');
		};
	})

	.service('ErrorHandler', function($filter, $rootScope, HTTP_CODE, AUTH_EVENTS) {
		this.onError = function(response, clientErrMsg) {
			if(response.status === HTTP_CODE.notFoundError)
				alert(clientErrMsg + " " + $filter('translate')('ERROR_HANDLER.404.MESSAGE'));
			else if(response.status === HTTP_CODE.internalServerError) {
				var tempText;
				if (response.responseText)
					tempText = JSON.parse(response.responseText);
				else
					tempText = $filter('translate')('ERROR_HANDLER.500.UNKNOWN');
				alert(clientErrMsg + " " + $filter('translate')('ERROR_HANDLER.500.MESSAGE') + "\r\n" + tempText);
			}
			else if(response.status === HTTP_CODE.forbiddenAccessError)
				$rootScope.$broadcast(AUTH_EVENTS.notAuthorized);
			// else if(response.status === HTTP_CODE.notAuthenticatedError)
			// 	$rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
		}
	})

	.service('loginModal', function ($uibModal) {
		return function () {
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/login/login-form.html',
				controller: 'loginModal',
				backdrop: 'static',
			});

			return modalInstance.result.then(function() {});
		};
	})

	.service('LogoutService', function (Session, $state, $http) {
		this.logLogout = function () {
			var user = Session.retrieveObject('user');
			$http.post(
				"user/logout",
				$.param({
					OAUserId: user.id,
					sessionId: user.sessionid,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};
		this.logout = function () {
			this.logLogout();
			Session.destroy();
			$state.go('login');
		};
	})

	.service('Encrypt', function () {
		this.encode = function (s, k) {
			var enc = "";
			var str = "";
			// make sure that input is string
			str = s.toString();
			for (var i = 0; i < s.length; i++) {
				// create block
				var a = s.charCodeAt(i);
				// bitwise XOR
				var b = a ^ k;
				enc = enc + String.fromCharCode(b);
			}
			// base 64 encode
			return btoa(enc);
		};
	})

	.service('FrequencyFilterService', function ($filter) {
		this.presetFrequencies = [
			{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.EVERY_DAY'),
				id: 'every_day',
				meta_key: 'repeat_day',
				meta_value: 1
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.EVERY_OTHER_DAY'),
				id: 'every_other_day',
				meta_key: 'repeat_day',
				meta_value: 2
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.EVERY_WEEK'),
				id: 'every_week',
				meta_key: 'repeat_week',
				meta_value: 1
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.EVERY_2_WEEK'),
				id: 'every_2_weeks',
				meta_key: 'repeat_week',
				meta_value: 2
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.EVERY_MONTH'),
				id: 'every_month',
				meta_key: 'repeat_month',
				meta_value: 1
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.CUSTOM'),
				id: 'custom',
				meta_key: null,
				meta_value: null
			}];

		this.customFrequency = {
			meta_value: 1,
			unit:null
		};

		this.frequencyUnits = [
			{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.DAY'),
				id: 'day',
				meta_key: 'repeat_day',
				every_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.TOUS_LES'),
				name_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.DAYS'),
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.WEEK'),
				id: 'week',
				meta_key: 'repeat_week',
				every_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.TOUTES_LES'),
				name_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.WEEKS'),
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.MONTH'),
				id: 'month',
				meta_key: 'repeat_month',
				every_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.TOUS_LES'),
				name_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.MONTHS'),
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.YEAR'),
				id: 'year',
				meta_key: 'repeat_year',
				every_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.TOUS_LES'),
				name_fr: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.YEARS'),
			}];

		this.daysInWeek = [
			{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.SUNDAY'),
				id: 1
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.MONDAY'),
				id: 2
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.TUESDAY'),
				id: 3
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.WEDNESDAY'),
				id: 4
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.THURSDAY'),
				id: 5
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.FRIDAY'),
				id: 6
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.SATURDAY'),
				id: 7
			}];

		this.weekNumbersInMonth = [
			{
				name: '---',
				id: null
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.1ST'),
				id: 1
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.2ND'),
				id: 2
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.3RD'),
				id: 3
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.4TH'),
				id: 4
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.5TH'),
				id: 5
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.LAST'),
				id: 6
			}];

		this.monthsInYear = [
			{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.JANUARY'),
				id: 1
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.FEBRUARY'),
				id: 2
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.MARCH'),
				id: 3
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.APRIL'),
				id: 4
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.MAY'),
				id: 5
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.JUNE'),
				id: 6
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.JULY'),
				id: 7
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.AUGUST'),
				id: 8
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.SEPTEMBER'),
				id: 9
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.OCTOBER'),
				id: 10
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.NOVEMBER'),
				id: 11
			},{
				name: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.DECEMBER'),
				id: 12
			}];

		this.additionalMeta = {
			repeat_day_iw: [],
			repeat_week_im: [],
			repeat_date_im: [],
			repeat_month_iy: []
		};
	});