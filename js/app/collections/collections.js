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
			return $http({
				method: 'GET',
				url: "config.json"
			});
		}

		// Function to get the app version and build
		applicationAPI.getApplicationBuild = function () {
			return $http({
				method: 'JSONP',
				url: "api/application/get.application_build.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get source databases
		applicationAPI.getSourceDatabases = function () {
			return $http({
				method: 'JSONP',
				url: "api/application/get.source_databases.php?callback=JSON_CALLBACK"
			});
		};

		return applicationAPI;
	})

	// Alias API service
	.factory('aliasCollectionService', function ($http) {

		var aliasAPI = {};

		// Function to get the list of existing alias in our DB
		aliasAPI.getAliases = function () {
			return $http({
				method: 'JSONP',
				url: "api/alias/get.aliases.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get an alias detail given an id number
		aliasAPI.getAliasDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/alias/get.alias_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get a list of unassigned expressions
		aliasAPI.getExpressions = function (sourcedbser, type) {
			return $http({
				method: 'JSONP',
				url: "api/alias/get.expressions.php?callback=JSON_CALLBACK&sourcedbser=" + sourcedbser + "&type=" + type
			});
		};

		// Function to get a list of source databases
		aliasAPI.getSourceDatabases = function () {
			return $http({
				method: 'JSONP',
				url: "api/alias/get.source_databases.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get existing color tags
		aliasAPI.getExistingColorTags = function (type) {
			return $http({
				method: 'JSONP',
				url: "api/alias/get.color_tags.php?callback=JSON_CALLBACK&type=" + type
			});
		};

		// Function to get alias chart logs given a serial
		aliasAPI.getAliasChartLogs = function (serial, type) {
			return $http({
				method: 'JSONP',
				url: "api/alias/get.alias_chart_logs.php?callback=JSON_CALLBACK&serial=" + serial + '&type=' + type
			});
		};

		// Function to get alias log list details given an array of serial numbers
		aliasAPI.getAliasListLogs = function (serials, type) {
			return $http({
				method: 'JSONP',
				url: "api/alias/get.alias_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					serials: JSON.stringify(serials),
					type: type
				}
			});
		}

		return aliasAPI;
	})

	// Post API service
	.factory('postCollectionService', function ($http) {

		var postAPI = {};

		// Function to get the list of posts
		postAPI.getPosts = function () {
			return $http({
				method: 'JSONP',
				url: "api/post/get.posts.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get a post detail given a serial
		postAPI.getPostDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/post/get.post_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get post chart logs given a serial
		postAPI.getPostChartLogs = function (serial, type) {
			return $http({
				method: 'JSONP',
				url: "api/post/get.post_chart_logs.php?callback=JSON_CALLBACK&serial=" + serial + '&type=' + type
			});
		};

		// Function to get post log list details given an array of serial numbers
		postAPI.getPostListLogs = function (serials, type) {
			return $http({
				method: 'JSONP',
				url: "api/post/get.post_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					serials: JSON.stringify(serials),
					type: type
				}
			});
		}

		return postAPI;
	})

	// Educational Material API service
	.factory('educationalMaterialCollectionService', function ($http) {

		var educationalMaterialAPI = {};

		// Function to get the list of existing education materials
		educationalMaterialAPI.getEducationalMaterials = function () {
			return $http({
				method: 'JSONP',
				url: "api/educational-material/get.educational_materials.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get an educational material detail given a serial
		educationalMaterialAPI.getEducationalMaterialDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/educational-material/get.educational_material_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get distinct educational material types
		educationalMaterialAPI.getEducationalMaterialTypes = function () {
			return $http({
				method: 'JSONP',
				url: "api/educational-material/get.educational_material_types.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get phases in treatment
		educationalMaterialAPI.getPhasesInTreatment = function () {
			return $http({
				method: 'JSONP',
				url: "api/educational-material/get.phases_in_treatment.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get parent educational materials
		educationalMaterialAPI.getParentEducationalMaterials = function () {
			return $http({
				method: 'JSONP',
				url: "api/educational-material/get.educational_material_parents.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get edcuational material chart logs given a serial
		educationalMaterialAPI.getEducationalMaterialChartLogs = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/educational-material/get.educational_material_chart_logs.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get edcuational material log list details given an array of serial numbers
		educationalMaterialAPI.getEducationalMaterialListLogs = function (serials) {
			return $http({
				method: 'JSONP',
				url: "api/educational-material/get.educational_material_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					serials: JSON.stringify(serials),
					type: type
				}
			});
		}

		return educationalMaterialAPI;
	})

	// Hospital Map API service
	.factory('hospitalMapCollectionService', function ($http) {

		var hospitalMapAPI = {};

		// Function to get the list of hospital maps
		hospitalMapAPI.getHospitalMaps = function () {
			return $http({
				method: 'JSONP',
				url: "api/hospital-map/get.hospital_maps.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get map details given a serial
		hospitalMapAPI.getHospitalMapDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/hospital-map/get.hospital_map_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to generate qrcode and return image path
		hospitalMapAPI.generateQRCode = function (qrid, oldqrid) {
			return $http({
				method: 'JSONP',
				url: "api/hospital-map/generate_QR_code.php?callback=JSON_CALLBACK&qrid=" + qrid + "&oldqrid=" + oldqrid
			});
		};

		return hospitalMapAPI;
	})

	// Notification API service
	.factory('notificationCollectionService', function ($http) {

		var notificationAPI = {};

		// Function to get the list of notifications
		notificationAPI.getNotifications = function () {
			return $http({
				method: 'JSONP',
				url: "api/notification/get.notifications.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get notification details given a serial
		notificationAPI.getNotificationDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/notification/get.notification_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get distinct notification types
		notificationAPI.getNotificationTypes = function () {
			return $http({
				method: 'JSONP',
				url: "api/notification/get.notification_types.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get notification logs given a serial
		notificationAPI.getNotificationChartLogs = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/notification/get.notification_chart_logs.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get notification log list details given an array of serial numbers
		notificationAPI.getNotificationListLogs = function (serials) {
			return $http({
				method: 'JSONP',
				url: "api/notification/get.notification_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					serials: JSON.stringify(serials)
				}
			});
		};

		return notificationAPI;
	})

	// Patient API service
	.factory('patientCollectionService', function ($http) {

		var patientAPI = {};

		// Function to get the list of patients
		patientAPI.getPatients = function () {
			return $http({
				method: 'JSONP',
				url: "api/patient/get.patients.php?callback=JSON_CALLBACK"
			});
		};

		// API to find patient given an SSN
		patientAPI.findPatient = function (ssn, id) {
			return $http({
				method: 'JSONP',
				url: "api/patient/find_patient.php?callback=JSON_CALLBACK&ssn=" + ssn + "&id=" + id
			});
		};

		// API to fetch security questions
		patientAPI.fetchSecurityQuestions = function (lang) {
			return $http({
				method: 'JSONP',
				url: "api/patient/get.security_questions.php?callback=JSON_CALLBACK&lang=" + lang
			});
		};

		// API to check email existence
		patientAPI.emailAlreadyInUse = function (email) {
			return $http({
				method: 'JSONP',
				url: "api/patient/email_in_use.php?callback=JSON_CALLBACK&email=" + email
			});
		};

		// API to get patient activity list
		patientAPI.getPatientActivities = function () {
			return $http({
				method: 'JSONP',
				url: "api/patient/get.patient_activities.php?callback=JSON_CALLBACK"
			});
		};

		// API to get a patient's details
		patientAPI.getPatientDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/patient/get.patient_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		return patientAPI;
	})

	// Filter API service
	.factory('filterCollectionService', function ($http) {

		var filterAPI = {};

		// Function to get all filters
		filterAPI.getFilters = function () {
			return $http({
				method: 'JSONP',
				url: "api/filter/get.filters.php?callback=JSON_CALLBACK"
			});
		};

		return filterAPI;
	})

	// Test Result API service
	.factory('testResultCollectionService', function ($http) {

		var testResultAPI = {};

		// Function to get distinct test groups
		testResultAPI.getTestResultGroups = function () {
			return $http({
				method: 'JSONP',
				url: "api/test-result/get.test_result_groups.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get distinct tests
		testResultAPI.getTestNames = function () {
			return $http({
				method: 'JSONP',
				url: "api/test-result/get.test_names.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get existing test results
		testResultAPI.getExistingTestResults = function () {
			return $http({
				method: 'JSONP',
				url: "api/test-result/get.test_results.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get test result details
		testResultAPI.getTestResultDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/test-result/get.test_result_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get test result chart logs given a serial
		testResultAPI.getTestResultChartLogs = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/test-result/get.test_result_chart_logs.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get test result log list details given an array of serial numbers
		testResultAPI.getTestResultListLogs = function (serials) {
			return $http({
				method: 'JSONP',
				url: "api/test-result/get.test_result_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					serials: JSON.stringify(serials),
				}
			});
		}

		return testResultAPI;
	})


	// Cron API service
	.factory('cronCollectionService', function ($http) {

		var cronAPI = {};

		// Function to get the cron details in our DB
		cronAPI.getCronDetails = function () {
			return $http({
				method: 'JSONP',
				url: "api/cron/get.cron_details.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get the cron logs for highcharts
		cronAPI.getCronChartLogs = function () {
			return $http({
				method: 'JSONP',
				url: "api/cron/get.cron_chart_logs.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get selected cron logs
		cronAPI.getSelectedCronListLogs = function (contents) {
			return $http({
				method: 'JSONP',
				url: "api/cron/get.cron_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					contents: JSON.stringify(contents),
				}
			});
		};

		return cronAPI;
	})

	// user API service
	.factory('userCollectionService', function ($http) {

		var userAPI = {};

		// Function to get user details given a serial
		userAPI.getUserDetails = function (userser) {
			return $http({
				method: 'JSONP',
				url: "api/user/get.user_details.php?callback=JSON_CALLBACK&userser=" + userser
			});
		};

		// Function to get the list of existing users in our DB
		userAPI.getUsers = function () {
			return $http({
				method: 'JSONP',
				url: "api/user/get.users.php?callback=JSON_CALLBACK"
			});
		};

		// Function to check username existence
		userAPI.usernameAlreadyInUse = function (username) {
			return $http({
				method: 'JSONP',
				url: "api/user/username_in_use.php?callback=JSON_CALLBACK&username=" + username
			});
		};

		// Function to get the list of existing roles
		userAPI.getRoles = function () {
			return $http({
				method: 'JSONP',
				url: "api/user/get.roles.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get user logs given a serial
		userAPI.getUserActivityLogs = function (userser) {
			return $http({
				method: 'JSONP',
				url: "api/user/get.user_activity_logs.php?callback=JSON_CALLBACK&userser=" + userser
			});
		};

		return userAPI;

	})

	// Email API service
	.factory('emailCollectionService', function ($http) {

		var emailAPI = {};

		// Function to get the list of email templates
		emailAPI.getEmails = function () {
			return $http({
				method: 'JSONP',
				url: "api/email/get.email_templates.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get email details given a serial
		emailAPI.getEmailDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/email/get.email_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get distinct email types
		emailAPI.getEmailTypes = function () {
			return $http({
				method: 'JSONP',
				url: "api/email/get.email_types.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get email chart logs given a serial
		emailAPI.getEmailChartLogs = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/email/get.email_chart_logs.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get email log list details given an array of serial numbers
		emailAPI.getEmailListLogs = function (serials) {
			return $http({
				method: 'JSONP',
				url: "api/email/get.email_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					serials: JSON.stringify(serials),
				}
			});
		}

		return emailAPI;
	})

	.factory('questionnaireCollectionService', function ($http) {
		var questionnaireAPI = {};

		questionnaireAPI.getQuestionnaires = function (userid) {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.questionnaires.php?callback=JSON_CALLBACK&userid=" + userid
			});
		};

		questionnaireAPI.getLibraries = function (userid) {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.libraries.php?callback=JSON_CALLBACK&userid=" + userid
			});
		};

		questionnaireAPI.getTags = function () {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.tags.php?callback=JSON_CALLBACK"
			});
		};

		questionnaireAPI.getAnswerTypes = function (userid) {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.answer_types.php?callback=JSON_CALLBACK&userid=" + userid
			});
		};

		questionnaireAPI.getQuestionGroupCategories = function () {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.question_group_categories.php?callback=JSON_CALLBACK"
			});
		};

		questionnaireAPI.getAnswerTypeCategories = function () {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.answer_type_categories.php?callback=JSON_CALLBACK"
			});
		};

		questionnaireAPI.getQuestionGroups = function (userid) {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.question_groups.php?callback=JSON_CALLBACK&userid=" + userid
			});
		};

		questionnaireAPI.getQuestions = function () {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.questions.php?callback=JSON_CALLBACK"
			});
		};

		questionnaireAPI.getQuestionDetails = function (questionSerNum) {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.question_details.php?callback=JSON_CALLBACK&questionSerNum=" + questionSerNum
			});
		};

		questionnaireAPI.getQuestionGroupWithLibraries = function (userid) {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.question_group_with_libraries.php?callback=JSON_CALLBACK&userid=" + userid
			});
		};

		questionnaireAPI.getQuestionnaireDetails = function (questionnaireSerNum) {
			return $http({
				method: 'JSONP',
				url: "api/questionnaire/get.questionnaire_details.php?callback=JSON_CALLBACK&serNum=" + questionnaireSerNum
			});
		};


		return questionnaireAPI;
	})

	// Legacy Questionnaire API service
	.factory('legacyQuestionnaireCollectionService', function ($http) {

		var legacyQuestionnaireAPI = {};

		// Function to get the list of legacy questionnaires
		legacyQuestionnaireAPI.getLegacyQuestionnaires = function () {
			return $http({
				method: 'JSONP',
				url: "api/legacy-questionnaire/get.legacy_questionnaires.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get legacy questionnaire details given a serial
		legacyQuestionnaireAPI.getLegacyQuestionnaireDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/legacy-questionnaire/get.legacy_questionnaire_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get legacy questionnaire expressions
		legacyQuestionnaireAPI.getLegacyQuestionnaireExpressions = function () {
			return $http({
				method: 'JSONP',
				url: "api/legacy-questionnaire/get.legacy_questionnaire_expressions.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get legacy questionnaire chart logs given a serial
		legacyQuestionnaireAPI.getLegacyQuestionnaireChartLogs = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/legacy-questionnaire/get.legacy_questionnaire_chart_logs.php?callback=JSON_CALLBACK&serial=" + serial
			});
		};

		// Function to get legacy questionnaire log list details given an array of serial numbers
		legacyQuestionnaireAPI.getLegacyQuestionnaireListLogs = function (serials) {
			return $http({
				method: 'JSONP',
				url: "api/legacy-questionnaire/get.legacy_questionnaire_list_logs.php",
				params: {
					callback: 'JSON_CALLBACK',
					serials: JSON.stringify(serials),
				}
			});
		}

		return legacyQuestionnaireAPI;
	})

	// Diagnosis API service
	.factory('diagnosisCollectionService', function ($http) {

		var diagnosisAPI = {};

		// Function to get distinct diagnosis codes
		diagnosisAPI.getDiagnoses = function () {
			return $http({
				method: 'JSONP',
				url: "api/diagnosis-translation/get.diagnoses.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get existing diagnosis translations
		diagnosisAPI.getDiagnosisTranslations = function () {
			return $http({
				method: 'JSONP',
				url: "api/diagnosis-translation/get.diagnosis_translations.php?callback=JSON_CALLBACK"
			});
		};

		// Function to get diagnosis translation details
		diagnosisAPI.getDiagnosisTranslationDetails = function (serial) {
			return $http({
				method: 'JSONP',
				url: "api/diagnosis-translation/get.diagnosis_translation_details.php?callback=JSON_CALLBACK&serial=" + serial
			});
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
				url: urlpath + "api/install/verify_requirements.php?callback=JSON_CALLBACK"
			});
		};

		return installAPI;
	});
