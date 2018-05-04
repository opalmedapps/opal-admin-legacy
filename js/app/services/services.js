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
		}
		this.destroy = function () {
			$cookies.remove('user');
		};
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
			var user = Session.retrieveObject('user')
			$http.post('php/user/logout.php', user );
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
		}
	})

	.service('FrequencyFilterService', function () {
		this.presetFrequencies = [
		{
			name: 'Every Day',
			id: 'every_day',
			meta_key: 'repeat_day',
			meta_value: 1
		},{
			name: 'Every Other Day',
			id: 'every_other_day',
			meta_key: 'repeat_day',
			meta_value: 2
		},{
			name: 'Every Week',
			id: 'every_week',
			meta_key: 'repeat_week',
			meta_value: 1
		},{
			name: 'Every 2 Weeks',
			id: 'every_2_weeks',
			meta_key: 'repeat_week',
			meta_value: 2
		},{
			name: 'Every Month',
			id: 'every_month',
			meta_key: 'repeat_month',
			meta_value: 1
		},{
			name: 'Custom',
			id: 'custom',
			meta_key: null,
			meta_value: null
		}];

		this.customFrequency = {
			meta_value: 1,
			unit:null
		}

		this.frequencyUnits = [
		{
			name: 'Day',
			id: 'day',
			meta_key: 'repeat_day'
		},{
			name: 'Week',
			id: 'week',
			meta_key: 'repeat_week'
		},{
			name: 'Month',
			id: 'month',
			meta_key: 'repeat_month'
		},{
			name: 'Year',
			id: 'year',
			meta_key: 'repeat_year'
		}];

		this.daysInWeek = [
		{
			name: 'Sunday',
			id: 1
		},{
			name: 'Monday',
			id: 2
		},{
			name: 'Tuesday',
			id: 3
		},{
			name: 'Wednesday',
			id: 4
		},{
			name: 'Thursday',
			id: 5
		},{
			name: 'Friday',
			id: 6
		},{
			name: 'Saturday',
			id: 7
		}];

		this.weekNumbersInMonth = [
		{
			name: '---',
			id: null
		},{
			name: '1st',
			id: 1
		},{
			name: '2nd',
			id: 2
		},{
			name: '3rd',
			id: 3
		},{
			name: '4th',
			id: 4
		},{
			name: '5th',
			id: 5
		},{
			name: 'Last',
			id: 6
		}];

		this.monthsInYear = [
		{
			name: 'January',
			id: 1
		},{
			name: 'February',
			id: 2
		},{
			name: 'March',
			id: 3
		},{
			name: 'April',
			id: 4
		},{
			name: 'May',
			id: 5
		},{
			name: 'June',
			id: 6
		},{
			name: 'July',
			id: 7
		},{
			name: 'August',
			id: 8
		},{
			name: 'September',
			id: 9
		},{ 
			name: 'October',
			id: 10
		},{
			name: 'November',
			id: 11
		},{
			name: 'December',
			id: 12
		}];

		this.additionalMeta = {
			repeat_day_iw: [],
			repeat_week_im: [],
			repeat_date_im: [],
			repeat_month_iy: []
		};
	});