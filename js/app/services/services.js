// Angular Service
// 

angular.module('opalAdmin.services', [])

	.service('Session', function ($cookies) {
		this.create = function (session_id, user) {
			$cookies.put('session_id', session_id);
			$cookies.putObject('user', user);
		};
		this.retrieve = function (data) {
			return $cookies.get(data);
		};
		this.retrieveObject = function (data) {
			return $cookies.getObject(data);
		};
		this.destroy = function () {
			$cookies.remove('session_id');
			$cookies.remove('user');
		};
	})

	.service('loginModal', function ($uibModal) {
		return function () {
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/login-form.html',
				controller: 'loginModalController',
				backdrop: 'static',
			});

			return modalInstance.result.then(function() {});
		};

	})

	.service('LogoutService', function (Session, $state) {
		this.logout = function () {
			Session.destroy();
			$state.go('login');
		};
	})

	.service('FrequencyFilterService', function () {
		this.presetFrequencies = [
		{
			name: 'Once',
			id: 'once',
			meta_key: 'repeat_day',
			meta_value: 0
		},{
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