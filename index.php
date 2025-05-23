<?php require_once('php/config.php'); ?>

<!--
SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>

SPDX-License-Identifier: AGPL-3.0-or-later
-->

<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6 lt8"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7 lt8"> <![endif]-->
<!--[if IE 8]><html class="ie8" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9" lang="en"><![endif]-->
<!--[if gt IE 9]><!--><html lang="en"><!--<![endif]-->
<head>
	<base href="<?php echo(FRONTEND_REL_URL); ?>">

	<title>opal ADMIN</title>
	<meta charset="utf-8">
	<meta content="text/html; X-Content-Type-Options=nosniff" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Libraries -->
	<script src="node_modules/jquery/dist/jquery.min.js"></script>
	<script src="node_modules/angular/angular.js"></script>
	<script src="node_modules/angular-route/angular-route.js"></script>
	<script src="node_modules/angular-animate/angular-animate.js"></script>
	<script src="node_modules/angular-sanitize/angular-sanitize.js"></script>
	<script src="node_modules/angular-ui-router/release/angular-ui-router.js"></script>
	<script src="node_modules/angular-cookies/angular-cookies.js"></script>
	<script src="node_modules/angular-translate/dist/angular-translate.js"></script>
	<script src="node_modules/angular-translate-loader-url/angular-translate-loader-url.js"></script>
	<script src="node_modules/angular-translate-loader-static-files/angular-translate-loader-static-files.js"></script>
	<script src="node_modules/angularjs-dropdown-multiselect/dist/angularjs-dropdown-multiselect.min.js"></script>
	<script src="node_modules/ng-idle/angular-idle.js"></script>
	<script src="node_modules/moment/moment.js"></script>
	<script src="node_modules/moment-timezone/builds/moment-timezone.min.js" defer></script>
	<script src="node_modules/moment-timezone/builds/moment-timezone-with-data.min.js" defer></script>
	<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="node_modules/multiple-date-picker/dist/multipleDatePicker.js"></script>
	<script src="node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js"></script>
	<script src="node_modules/rangy/lib/rangy-core.js"></script>
	<script src="node_modules/rangy/lib/rangy-classapplier.js"></script>
	<script src="node_modules/rangy/lib/rangy-highlighter.js"></script>
	<script src="node_modules/rangy/lib/rangy-selectionsaverestore.js"></script>
	<script src="node_modules/rangy/lib/rangy-serializer.js"></script>
	<script src="node_modules/rangy/lib/rangy-textrange.js"></script>
	<script src="node_modules/textangularjs/dist/textAngular.js"></script>
	<script src="node_modules/textangularjs/dist/textAngular-sanitize.js"></script>
	<script src="node_modules/textangularjs/dist/textAngularSetup.js"></script>
	<script src="node_modules/crypto-js/crypto-js.js"></script>
	<script src="node_modules/angular-ui-grid/ui-grid.js"></script>
	<script src="node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js" defer></script>
	<script src="js/lib/bootstrap/ui.bootstrap.materialPicker.js"></script>
	<script src="node_modules/marked/marked.min.js"></script>
	<script src="node_modules/requirejs/require.js"></script>

	<!-- Start Up -->
 	<script type="text/javascript" src="js/app/app.js"></script>

	<!-- Controller -->
 	<script type="text/javascript" src="js/app/controllers/controllers.js"></script>
 	<script type="text/javascript" src="js/app/controllers/application.js"></script>
 	<script type="text/javascript" src="js/app/controllers/navigation.js"></script>

	<script type="text/javascript" src="js/app/controllers/home/home.js"></script>

 	<script type="text/javascript" src="js/app/controllers/alias/alias.js"></script>
 	<script type="text/javascript" src="js/app/controllers/alias/add.alias.js"></script>
 	<script type="text/javascript" src="js/app/controllers/alias/edit.alias.js"></script>
<!-- 	<script type="text/javascript" src="js/app/controllers/alias/delete.alias.js"></script>-->
 	<script type="text/javascript" src="js/app/controllers/alias/log.alias.js"></script>

 	<script type="text/javascript" src="js/app/controllers/post/post.js"></script>
 	<script type="text/javascript" src="js/app/controllers/post/add.post.js"></script>
 	<script type="text/javascript" src="js/app/controllers/post/edit.post.js"></script>
 	<script type="text/javascript" src="js/app/controllers/post/delete.post.js"></script>
 	<script type="text/javascript" src="js/app/controllers/post/log.post.js"></script>

 	<script type="text/javascript" src="js/app/controllers/educational-material/educationalMaterial.js"></script>
 	<script type="text/javascript" src="js/app/controllers/educational-material/add.educationalMaterial.js"></script>
 	<script type="text/javascript" src="js/app/controllers/educational-material/edit.educationalMaterial.js"></script>
 	<script type="text/javascript" src="js/app/controllers/educational-material/delete.educationalMaterial.js"></script>
 	<script type="text/javascript" src="js/app/controllers/educational-material/log.educationalMaterial.js"></script>

 	<script type="text/javascript" src="js/app/controllers/hospital-map/hospitalMap.js"></script>
 	<script type="text/javascript" src="js/app/controllers/hospital-map/add.hospitalMap.js"></script>
 	<script type="text/javascript" src="js/app/controllers/hospital-map/edit.hospitalMap.js"></script>
 	<script type="text/javascript" src="js/app/controllers/hospital-map/delete.hospitalMap.js"></script>

 	<script type="text/javascript" src="js/app/controllers/notification/notification.js"></script>
 	<script type="text/javascript" src="js/app/controllers/notification/edit.notification.js"></script>
 	<script type="text/javascript" src="js/app/controllers/notification/delete.notification.js"></script>
 	<script type="text/javascript" src="js/app/controllers/notification/log.notification.js"></script>

 	<script type="text/javascript" src="js/app/controllers/patient/patient.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient/patientActivity.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient/patientReports.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient/groupReports.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient/patientReportHandler.js"></script>

 	<script type="text/javascript" src="js/app/controllers/test-result/testResult.js"></script>
 	<script type="text/javascript" src="js/app/controllers/test-result/add.testResult.js"></script>
 	<script type="text/javascript" src="js/app/controllers/test-result/edit.testResult.js"></script>
 	<script type="text/javascript" src="js/app/controllers/test-result/delete.testResult.js"></script>
 	<script type="text/javascript" src="js/app/controllers/test-result/log.testResult.js"></script>

 	<script type="text/javascript" src="js/app/controllers/cron/cron.js"></script>
 	<script type="text/javascript" src="js/app/controllers/cron/log.cron.js"></script>

 	<script type="text/javascript" src="js/app/controllers/login/login.js"></script>
 	<script type="text/javascript" src="js/app/controllers/login/loginModal.js"></script>

 	<script type="text/javascript" src="js/app/controllers/user/account.ad.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/account.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/user.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/add.user.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/add.user.ad.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/edit.user.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/edit.user.ad.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/delete.user.js"></script>
 	<script type="text/javascript" src="js/app/controllers/user/log.user.js"></script>

 	<script type="text/javascript" src="js/app/controllers/email/email.js"></script>
 	<script type="text/javascript" src="js/app/controllers/email/add.email.js"></script>
 	<script type="text/javascript" src="js/app/controllers/email/edit.email.js"></script>
 	<script type="text/javascript" src="js/app/controllers/email/delete.email.js"></script>
 	<script type="text/javascript" src="js/app/controllers/email/log.email.js"></script>

 	<script type="text/javascript" src="js/app/controllers/questionnaire/questionnaire.js"></script>
 	<script type="text/javascript" src="js/app/controllers/questionnaire/add.questionnaire.js"></script>
 	<script type="text/javascript" src="js/app/controllers/questionnaire/edit.questionnaire.js"></script>
 	<script type="text/javascript" src="js/app/controllers/questionnaire/delete.questionnaire.js"></script>

 	<script type="text/javascript" src="js/app/controllers/publication/publication.js"></script>
 	<script type="text/javascript" src="js/app/controllers/publication/add.publication.js"></script>
 	<script type="text/javascript" src="js/app/controllers/publication/edit.publication.js"></script>
    <script type="text/javascript" src="js/app/controllers/publication/log.publication.js"></script>

	<script type="text/javascript" src="js/app/controllers/question/question.js"></script>
	<script type="text/javascript" src="js/app/controllers/question/add.question.js"></script>
	<script type="text/javascript" src="js/app/controllers/question/edit.question.js"></script>
	<script type="text/javascript" src="js/app/controllers/question/delete.question.js"></script>

	<script type="text/javascript" src="js/app/controllers/template-question/template.question.js"></script>
	<script type="text/javascript" src="js/app/controllers/template-question/add.template.question.js"></script>
	<script type="text/javascript" src="js/app/controllers/template-question/edit.template.question.js"></script>
	<script type="text/javascript" src="js/app/controllers/template-question/delete.template.question.js"></script>

	<script type="text/javascript" src="js/app/controllers/diagnosis/diagnosisTranslation.js"></script>
	<script type="text/javascript" src="js/app/controllers/diagnosis/add.diagnosisTranslation.js"></script>
	<script type="text/javascript" src="js/app/controllers/diagnosis/edit.diagnosisTranslation.js"></script>
	<script type="text/javascript" src="js/app/controllers/diagnosis/delete.diagnosisTranslation.js"></script>

	<script type="text/javascript" src="js/app/controllers/custom-code/custom.codes.js"></script>
	<script type="text/javascript" src="js/app/controllers/custom-code/add.custom.code.js"></script>
	<script type="text/javascript" src="js/app/controllers/custom-code/edit.custom.code.js"></script>
	<script type="text/javascript" src="js/app/controllers/custom-code/delete.custom.code.js"></script>

	<script type="text/javascript" src="js/app/controllers/study/studies.js"></script>
	<script type="text/javascript" src="js/app/controllers/study/add.study.js"></script>
	<script type="text/javascript" src="js/app/controllers/study/edit.study.js"></script>

	<script type="text/javascript" src="js/app/controllers/role/roles.js"></script>
	<script type="text/javascript" src="js/app/controllers/role/add.role.js"></script>
	<script type="text/javascript" src="js/app/controllers/role/edit.role.js"></script>
	<script type="text/javascript" src="js/app/controllers/role/delete.role.js"></script>

	<script type="text/javascript" src="js/app/controllers/alert/alerts.js"></script>
	<script type="text/javascript" src="js/app/controllers/alert/add.alert.js"></script>
	<script type="text/javascript" src="js/app/controllers/alert/edit.alert.js"></script>
	<script type="text/javascript" src="js/app/controllers/alert/delete.alert.js"></script>

	<script type="text/javascript" src="js/app/controllers/audit/audits.js"></script>
	<script type="text/javascript" src="js/app/controllers/audit/view.audit.js"></script>

	<script type="text/javascript" src="js/app/controllers/error-handler/access.denied.js"></script>

	<script type="text/javascript" src="js/app/controllers/sms/sms.js"></script>
	<script type="text/javascript" src="js/app/controllers/sms/add.sms.js"></script>
	<script type="text/javascript" src="js/app/controllers/sms/edit.sms.js"></script>

	<script type="text/javascript" src="js/app/controllers/patient-administration/patient.administration.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient-administration/update.email.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient-administration/update.password.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient-administration/update.accessLevel.js"></script>
	<script type="text/javascript" src="js/app/controllers/patient-administration/update.securityAnswer.js"></script>

	<script type="text/javascript" src="js/app/controllers/about/about.js"></script>

	<!-- Collection -->
 	<script type="text/javascript" src="js/app/collections/collections.js"></script>

 	<!-- Service -->
 	<script type="text/javascript" src="js/app/services/services.js"></script>

	<!-- bower:css -->
	<link rel="stylesheet" href="node_modules/textangularjs/dist/textAngular.css" />
	<link rel="stylesheet" type="text/css" href="node_modules/multiple-date-picker/dist/multipleDatePicker.css">
	<!-- endbower -->

	<!-- bower:font -->
	<!-- endbower -->

	<!-- Stylesheets -->
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/jquery-ui.css">
	<link media="all" type="text/css" rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.css">
	<link media="all" type="text/css" rel="stylesheet" href="node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css">
	<link media="all" type="text/css" rel="stylesheet" href="node_modules/angular-ui-grid/ui-grid.css">
	<link media="all" type="text/css" rel="stylesheet" href="node_modules/animate.css/animate.min.css">
	<link media="all" type="text/css" rel="stylesheet" href="node_modules/font-awesome/css/font-awesome.min.css">
	<link media="all" type="text/css" rel="stylesheet" href="node_modules/textangularjs/dist/textAngular.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/ui.bootstrap.materialPicker.css">

	<link media="all" type="text/css" rel="stylesheet" href="node_modules/@fontsource/nunito/index.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/style.css">

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/png" href="favicon.png"/>
</head>
<body ng-app="opalAdmin" ng-controller="application">
	<div ng-include="'templates/navbar-menu.html'" ></div>
	<div id="page">
		<div ui-view style="position: relative; height: inherit;"></div>
	</div>

	<footer class="app-version" ng-class="{'login-footer': isIndexPage()}">
		<div class="text-right">
			<a ng-if="!isAboutPage()" ui-sref="about">{{'ABOUT'|translate}}</a>
			<a ng-if="isAboutPage()" ng-attr-ui-sref="{{ isAuthenticated() ? 'home' : 'login' }}">
				{{'BREADCRUMBS.HOME'|translate}}
			</a> ·
			<a href="https://github.com/opalmedapps">{{'SOURCE_CODE'|translate}}</a> ·
			<span ng-if="build">({{'ENVIRONMENT'|translate}}: {{build.environment_name}})</span>
		</div>
	</footer>

</body>
</html>
