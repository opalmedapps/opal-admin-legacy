// Angular Module
// To collect various data using JSONP (JavaScript Object Notation with Padding), a safer (than JSON) cross-domain ajax call
// Most pages on the site require information from either our database or clinical databases so we create an "API service" (Application
// Programming Interface) for each page with functions to collect relevant data.
// Each function calls a PHP script (located in api directory) and encodes the data in JSON because the callback will not work otherwise.

angular.module('opalAdmin.collections', [])

// Application API server
	.factory('applicationCollectionService', function ($http) {

		var applicationAPI = {};

		// Function to get configs
		applicationAPI.getConfigs = function () {
			return $http.post(
				"application/get/config",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get the app version and build
		applicationAPI.getApplicationBuild = function () {
			return $http.post(
				"application/get/application-build",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		// Function to get source databases
		applicationAPI.getSourceDatabases = function () {
			return $http.post(
				"application/get/source-databases",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return applicationAPI;
	})

	// Alias API service
	.factory('aliasCollectionService', function ($http) {

		var aliasAPI = {};

		// Function to get the list of existing alias in our DB
		aliasAPI.getAliases = function () {
			return $http.post(
				"alias/get/aliases",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get an alias detail given an id number
		aliasAPI.getAliasDetails = function (serial) {
			return $http.post(
				"alias/get/alias-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get a list of unassigned expressions
		aliasAPI.getExpressions = function (sourcedbser, type) {
			return $http.post(
				"alias/get/expressions",
				$.param({
					sourcedbser: sourcedbser,
					type: type,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		// Function to get a list of source databases
		aliasAPI.getSourceDatabases = function () {
			return $http.post(
				"alias/get/source-databases",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get existing color tags
		aliasAPI.getExistingColorTags = function (type) {
			return $http.post(
				"alias/get/color-tags",
				$.param({
					type: type,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get alias chart logs given a serial
		aliasAPI.getAliasChartLogs = function (serial, type) {
			return $http.post(
				"alias/get/alias-chart-logs",
				$.param({
					serial: serial,
					type: type,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get alias log list details given an array of serial numbers
		aliasAPI.getAliasListLogs = function (serials, type) {
			return $http.post(
				"alias/get/alias-list-logs",
				$.param({
					serials: JSON.stringify(serials),
					type: type
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get alias log list details given an array of serial numbers
		aliasAPI.getEducationalMaterials = function () {
			return $http.post(
				"alias/get/educational-materials",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		aliasAPI.getHospitalMaps = function () {
			return $http.post(
				"alias/get/hospital-maps",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return aliasAPI;
	})

	// Post API service
	.factory('postCollectionService', function ($http) {

		var postAPI = {};

		// Function to get the list of posts
		postAPI.getPosts = function (OAUserId) {
			return $http.post(
				"post/get/posts",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get a post detail given a serial
		postAPI.getPostDetails = function (postId, OAUserId) {
			return $http.post(
				"post/get/post-details",
				$.param({
					OAUserId: OAUserId,
					postId: postId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get post chart logs given a serial
		postAPI.getPostChartLogs = function (serial, type, OAUserId) {
			return $http.post(
				"post/get/post-chart-logs",
				$.param({
					OAUserId: OAUserId,
					serial: serial,
					type: type,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get post log list details given an array of serial numbers
		postAPI.getPostListLogs = function (serials, type, OAUserId) {
			return $http.post(
				"post/get/post-list-logs",
				$.param({
					OAUserId: OAUserId,
					serials: JSON.stringify(serials),
					type: type,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return postAPI;
	})

	// Educational Material API service
	.factory('educationalMaterialCollectionService', function ($http) {

		var educationalMaterialAPI = {};

		// Function to get the list of existing education materials
		educationalMaterialAPI.getEducationalMaterials = function () {
			return $http.post(
				"educational-material/get/educational-materials",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get an educational material detail given a serial
		educationalMaterialAPI.getEducationalMaterialDetails = function (serial) {
			return $http.post(
				"educational-material/get/educational-material-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get distinct educational material types
		educationalMaterialAPI.getEducationalMaterialTypes = function () {
			return $http.post(
				"educational-material/get/educational-material-types",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get phases in treatment
		educationalMaterialAPI.getPhasesInTreatment = function () {
			return $http.post(
				"educational-material/get/phases-in-treatment",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get parent educational materials
		educationalMaterialAPI.getParentEducationalMaterials = function () {
			return $http.post(
				"educational-material/get/educational-material-parents",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get edcuational material chart logs given a serial
		educationalMaterialAPI.getEducationalMaterialChartLogs = function (serial) {
			return $http.post(
				"educational-material/get/educational-material-chart-logs",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get edcuational material log list details given an array of serial numbers
		educationalMaterialAPI.getEducationalMaterialListLogs = function (serials) {
			return $http.post(
				"educational-material/get/educational-material-list-logs",
				$.param({
					serials: JSON.stringify(serials),
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return educationalMaterialAPI;
	})

	// Hospital Map API service
	.factory('hospitalMapCollectionService', function ($http) {

		var hospitalMapAPI = {};

		// Function to get the list of hospital maps
		hospitalMapAPI.getHospitalMaps = function () {
			return $http.post(
				"hospital-map/get/hospital-maps",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get map details given a serial
		hospitalMapAPI.getHospitalMapDetails = function (serial) {
			return $http.post(
				"hospital-map/get/hospital-map-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to generate qrcode and return image path
		hospitalMapAPI.generateQRCode = function (qrid, oldqrid) {
			return $http.post(
				"hospital-map/generate-qr-code",
				$.param({
					qrid: qrid,
					oldqrid: oldqrid,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return hospitalMapAPI;
	})

	// Notification API service
	.factory('notificationCollectionService', function ($http) {

		var notificationAPI = {};

		// Function to get the list of notifications
		notificationAPI.getNotifications = function () {
			return $http.post(
				"notification/get/notifications",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get notification details given a serial
		notificationAPI.getNotificationDetails = function (serial) {
			return $http.post(
				"notification/get/notification-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get distinct notification types
		notificationAPI.getNotificationTypes = function () {
			return $http.post(
				"notification/get/notification-types",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get notification logs given a serial
		notificationAPI.getNotificationChartLogs = function (serial) {
			return $http.post(
				"notification/get/notification-chart-logs",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get notification log list details given an array of serial numbers
		notificationAPI.getNotificationListLogs = function (serials) {
			return $http.post(
				"notification/get/notification-list-logs",
				$.param({
					serials: JSON.stringify(serials),
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return notificationAPI;
	})

	// Patient API service
	.factory('patientCollectionService', function ($http) {

		var patientAPI = {};

		// Function to get the list of patients
		patientAPI.getPatients = function () {
			return $http.post(
				"patient/get/patients",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// API to find patient given an SSN
		patientAPI.findPatient = function (ssn, id) {
			return $http.post(
				"patient/find-patient",
				$.param({
					ssn: ssn,
					id: id,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// API to fetch security questions
		patientAPI.fetchSecurityQuestions = function (lang) {
			return $http.post(
				"patient/get/security-questions",
				$.param({
					lang: lang,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// API to check email existence
		patientAPI.emailAlreadyInUse = function (email) {
			return $http.post(
				"patient/email-in-use",
				$.param({
					email: email,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// API to get patient activity list
		patientAPI.getPatientActivities = function () {
			return $http.post(
				"patient/get/patient-activities",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// API to get a patient's details
		patientAPI.getPatientDetails = function (serial) {
			return $http.post(
				"patient/get/patient-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return patientAPI;
	})

	// Test Result API service
	.factory('testResultCollectionService', function ($http) {

		var testResultAPI = {};

		// Function to get distinct test groups
		testResultAPI.getTestResultGroups = function () {
			return $http.post(
				"test-result/get/test-result-groups",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get distinct tests
		testResultAPI.getTestNames = function () {
			return $http.post(
				"test-result/get/test-names",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		testResultAPI.getEducationalMaterials = function () {
			return $http.post(
				"test-result/get/get/educational-materials",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get existing test results
		testResultAPI.getExistingTestResults = function () {
			return $http.post(
				"test-result/get/test-results",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get test result details
		testResultAPI.getTestResultDetails = function (serial) {
			return $http.post(
				"test-result/get/test-result-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get test result chart logs given a serial
		testResultAPI.getTestResultChartLogs = function (serial) {
			return $http.post(
				"test-result/get/test-result-chart-logs",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get test result log list details given an array of serial numbers
		testResultAPI.getTestResultListLogs = function (serials) {
			return $http.post(
				"test-result/get/test-result-list-logs",
				$.param({
					serials: JSON.stringify(serials),
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return testResultAPI;
	})

	// Cron API service
	.factory('cronCollectionService', function ($http) {

		var cronAPI = {};

		// Function to get the cron details in our DB
		cronAPI.getCronDetails = function () {
			return $http.post(
				"cron/get/cron-details",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get the cron logs for highcharts
		cronAPI.getCronChartLogs = function () {
			return $http.post(
				"cron/get/cron-chart-logs",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get selected cron logs
		cronAPI.getSelectedCronListLogs = function (contents, OAUserId) {
			return $http.post(
				"cron/get/cron-list-logs",
				$.param({
					contents: JSON.stringify(contents),
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return cronAPI;
	})

	// user API service
	.factory('userCollectionService', function ($http) {

		var userAPI = {};

		// Function to get user details given a serial
		userAPI.getUserDetails = function (userId, OAUserId) {
			return $http.post(
				"user/get/user-details",
				$.param({
					userId: userId,
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get the list of existing users in our DB
		userAPI.getUsers = function (OAUserId) {
			return $http.post(
				"user/get/users",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to check username existence
		userAPI.usernameAlreadyInUse = function (username, OAUserId) {
			return $http.post(
				"user/username-in-use",
				$.param({
					username: username,
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get the list of existing roles
		userAPI.getRoles = function () {
			return $http.post(
				"user/get/roles",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get user logs given a serial
		userAPI.getUserActivityLogs = function (userser, OAUserId) {
			return $http.post(
				"user/get/user-activity-logs",
				$.param({
					userser: userser,
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return userAPI;

	})

	// Email API service
	.factory('emailCollectionService', function ($http) {

		var emailAPI = {};

		// Function to get the list of email templates
		emailAPI.getEmails = function () {
			return $http.post(
				"email/get/email-templates",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get email details given a serial
		emailAPI.getEmailDetails = function (serial) {
			return $http.post(
				"email/get/email-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get distinct email types
		emailAPI.getEmailTypes = function () {
			return $http.post(
				"email/get/email-types",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get email chart logs given a serial
		emailAPI.getEmailChartLogs = function (serial) {
			return $http.post(
				"email/get/email-chart-logs",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get email log list details given an array of serial numbers
		emailAPI.getEmailListLogs = function (serials) {
			return $http.post(
				"email/get/email-list-logs",
				$.param({
					serials: JSON.stringify(serials),
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return emailAPI;
	})
	// Master Source API service
	.factory('masterSourceCollectionService', function ($http) {

		var masterSourceAPI = {};

		masterSourceAPI.getMasterSourceDiagnoses = function () {
			return $http.post(
				"master-source/get/diagnoses",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		masterSourceAPI.getMasterSourceTestResults = function () {
			return $http.post(
				"master-source/get/test-results",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		masterSourceAPI.getExternalSourceDatabase = function () {
			return $http.post(
				"master-source/get/external-source-db",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		masterSourceAPI.isMasterSourceDiagnosisExists = function (source, externalId) {
			return $http.post(
				"master-source/get/diagnosis-exists",
				$.param({
					source: source,
					externalId: externalId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		masterSourceAPI.isMasterSourceTestResultExists = function (source, code) {
			return $http.post(
				"master-source/get/test-result-exists",
				$.param({
					source: source,
					code: code,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		masterSourceAPI.getDiagnosisDetails  = function (externalId, source) {
			return $http.post(
				"master-source/get/diagnosis-details",
				$.param({
					externalId: externalId,
					source: source,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		masterSourceAPI.getTestResultDetails  = function (code, source) {
			return $http.post(
				"master-source/get/test-result-details",
				$.param({
					code: code,
					source: source,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return masterSourceAPI;
	})

	// Questionnaire API service
	.factory('questionnaireCollectionService', function ($http) {
		var questionnaireAPI = {};

		questionnaireAPI.getQuestionnaires = function (OAUserId) {
			return $http.post(
				"questionnaire/get/questionnaires",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getLibraries = function (OAUserId) {
			return $http.post(
				"library/get/libraries",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getTemplatesQuestions = function (OAUserId) {
			return $http.post(
				"template-question/get/templates-questions",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getTemplateQuestionDetails = function (templateQuestionId, OAUserId) {
			return $http.post(
				"template-question/get/template-question-details",
				$.param({
					OAUserId: OAUserId,
					templateQuestionId: templateQuestionId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getTemplateQuestionCategory = function (OAUserId) {
			return $http.post(
				"template-question/get/template-question-list",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getQuestions = function (OAUserId) {
			return $http.post(
				"question/get/questions",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getQuestionDetails = function (questionId, OAUserId) {
			return $http.post(
				"question/get/question-details",
				$.param({
					questionId: questionId,
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getFinalizedQuestions = function (OAUserId) {
			return $http.post(
				"questionnaire/get/finalized-questions",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		questionnaireAPI.getQuestionnaireDetails = function (questionnaireId, OAUserId) {
			return $http.post(
				"questionnaire/get/questionnaire-details",
				$.param({
					questionnaireId: questionnaireId,
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return questionnaireAPI;
	})

	// Publication API service
	.factory('publicationCollectionService', function ($http) {
		var publicationAPI = {};

		publicationAPI.getPublications = function (OAUserId) {
			return $http.post(
				"publication/get/publications",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		publicationAPI.getPublicationModules = function (OAUserId) {
			return $http.post(
				"publication/get/publication-modules",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		publicationAPI.getPublicationsPerModule = function (OAUserId, moduleId) {
			return $http.post(
				"publication/get/publications-per-module",
				$.param({
					OAUserId: OAUserId,
					moduleId: moduleId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		publicationAPI.getPublicationDetails = function (publicationId, moduleId, OAUserId) {
			return $http.post(
				"publication/get/publication-details",
				$.param({
					OAUserId: OAUserId,
					publicationId: publicationId,
					moduleId: moduleId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		publicationAPI.getPublicationsChartLogs = function (publicationId, moduleId, OAUserId) {
			return $http.post(
				"publication/get/publication-chart-logs",
				$.param({
					publicationId: publicationId,
					moduleId: moduleId,
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get post log list details given an array of serial numbers
		publicationAPI.getPublicationListLogs = function (publicationId, moduleId, OAUserId, cronIds) {
			return $http.post(
				"publication/get/publication-list-logs",
				$.param({
					publicationId: publicationId,
					moduleId: moduleId,
					OAUserId: OAUserId,
					cronIds: JSON.stringify(cronIds),
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		publicationAPI.getFilters = function () {
			return $http.post(
				"publication/get/filters",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return publicationAPI;
	})

	// Custom Codes API service
	.factory('customCodeCollectionService', function ($http) {
		var customCodeAPI = {};

		customCodeAPI.getAvailableModules = function (OAUserId) {
			return $http.post(
				"custom-code/get/available-modules",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		customCodeAPI.getCustomCodes = function (OAUserId) {
			return $http.post(
				"custom-code/get/custom-codes",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		customCodeAPI.getCustomCodeDetails = function (customCodeId, moduleId, OAUserId) {
			return $http.post(
				"custom-code/get/custom-code-details",
				$.param({
					OAUserId: OAUserId,
					customCodeId: customCodeId,
					moduleId: moduleId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return customCodeAPI;
	})

	// Study API service
	.factory('studyCollectionService', function ($http) {
		var studyAPI = {};

		studyAPI.getStudies = function (OAUserId) {
			return $http.post(
				"study/get/studies",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		studyAPI.getStudiesDetails = function (studyId, OAUserId) {
			return $http.post(
				"study/get/study-details",
				$.param({
					OAUserId: OAUserId,
					studyId: studyId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return studyAPI;
	})

	// Alert API service
	.factory('alertCollectionService', function ($http) {
		var alertAPI = {};

		alertAPI.getAlerts = function () {
			return $http.post(
				"alert/get/alerts",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);

		};

		alertAPI.getAlertDetails = function (alertId) {
			return $http.post(
				"alert/get/alert-details",
				$.param({
					alertId: alertId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return alertAPI;
	})

	// Role API service
	.factory('roleCollectionService', function ($http) {
		var roleAPI = {};

		roleAPI.getRoles = function (OAUserId) {
			return $http.post(
				"role/get/roles",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		roleAPI.getAvailableRoleModules = function (OAUserId) {
			return $http.post(
				"role/get/available-modules",
				$.param({
					OAUserId: OAUserId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		roleAPI.getRoleDetails = function (roleId, OAUserId) {
			return $http.post(
				"role/get/role-details",
				$.param({
					OAUserId: OAUserId,
					roleId: roleId,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return roleAPI;
	})

	// Audit API service
	.factory('auditCollectionService', function ($http) {
		var auditAPI = {};

		auditAPI.getAudits = function () {
			return $http.post(
				"audit/get/audits",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		auditAPI.getAuditDetails = function (ID) {
			return $http.post(
				"audit/get/audit-details",
				$.param({
					ID: ID,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		return auditAPI;
	})

	// Diagnosis API service
	.factory('diagnosisCollectionService', function ($http) {

		var diagnosisAPI = {};

		// Function to get distinct diagnosis codes
		diagnosisAPI.getDiagnoses = function () {
			return $http.post(
				"diagnosis-translation/get/diagnoses",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get existing diagnosis translations
		diagnosisAPI.getDiagnosisTranslations = function () {
			return $http.post(
				"diagnosis-translation/get/diagnosis-translations",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};

		// Function to get existing diagnosis translations
		diagnosisAPI.getEducationalMaterials = function () {
			return $http.post(
				"diagnosis-translation/get/educational-materials",
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};


		// Function to get diagnosis translation details
		diagnosisAPI.getDiagnosisTranslationDetails = function (serial) {
			return $http.post(
				"diagnosis-translation/get/diagnosis-translation-details",
				$.param({
					serial: serial,
				}),
				{
					headers : {'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'},
				}
			);
		};
		return diagnosisAPI;
	})

	// install API service
	.factory('installCollectionService', function ($http) {

		var installAPI = {};

		// Function to verify installation requirements
		installAPI.verifyRequirements = function (urlpath) {
			return $http({
				method: 'JSONP',
				url: "install/verify-requirements"
			});
		};

		return installAPI;
	});
