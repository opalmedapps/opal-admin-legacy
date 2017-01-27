<?php session_start(); ?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6 lt8"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7 lt8"> <![endif]-->
<!--[if IE 8]><html class="ie8" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9" lang="en"><![endif]-->
<!--[if gt IE 9]><!--><html lang="en"><!--<![endif]-->
<head>
	<title>opal ADMIN</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		
	<!-- Libraries -->
	<script src="js/lib/jquery/jquery-2.2.1.min.js"></script>
	<script src="js/lib/jquery/jquery-ui.min.js"></script>
	<script src="js/lib/angular/angular.min.js"></script>
	<script src="js/lib/angular/angular-route.min.js"></script>
	<script src="js/lib/angular/angular-animate.min.js"></script>
	<script src="js/lib/angular/angular-sanitize.min.js"></script>
	<script src="js/lib/angular/angular-ui-router.min.js"></script>
	<script src="js/lib/angular/angular-cookies.min.js"></script>
	<script src="js/lib/angular/angular-idle.min.js"></script>
	<script src="js/lib/other/moment.min.js"></script>
	<script src="js/lib/livicon/prettify.min.js"></script>
	<script src="js/lib/bootstrap/bootstrap.min.js"></script>
	<script src="js/lib/bootstrap/ui-bootstrap-tpls-1.2.1.custom.min.js"></script>
	<script src="js/lib/bootstrap/bootstrap-datetimepicker.min.js"></script>
	<script src="js/lib/bootstrap/ui.bootstrap.materialPicker.js"></script>
	<script src="js/lib/livicon/raphael-min.js"></script>
	<script src="js/lib/livicon/livicons-1.4-custom.min.js"></script>
	<script src="js/lib/ui-grid/ui-grid.min.js"></script>
	<script src="js/lib/textAngular/textAngular-rangy.min.js"></script>
	<script src="js/lib/textAngular/textAngular-sanitize.min.js"></script>
	<script src="js/lib/textAngular/textAngular.min.js"></script>
	<script src="js/lib/itemSlide/itemslide.min.js"></script>
	<script src="js/lib/firebase/firebase.js"></script>
	<script src="js/lib/cryptojs/aes.js"></script>
	<script src="js/lib/cryptojs/mode-cfb-min.js"></script>
	<script src="js/lib/cryptojs/sha256.js"></script>

	<!-- Start Up -->
 	<script type="text/javascript" src="js/app/app.js"></script>

	<!-- Controller -->
 	<script type="text/javascript" src="js/app/controllers/controllers.js"></script>
 	<script type="text/javascript" src="js/app/controllers/headerController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/homeController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/aliasController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/newAliasController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/postController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/newPostController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/newEduMatController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/eduMatController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/hospitalMapController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/newHospitalMapController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/notificationController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/newNotificationController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/patientController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/patientRegistrationController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/testResultController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/newTestResultController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/sidePanelMenuController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/cronController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/loginController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/loginModalController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/patientActivityController.js"></script>
 	<script type="text/javascript" src="js/app/controllers/accountController.js"></script>
 	<!-- <script type="text/javascript" src="js/app/controllers/applicationController.js"></script> -->


	<!-- Collection -->
 	<script type="text/javascript" src="js/app/collections/collections.js"></script>

 	<!-- Service -->
 	<script type="text/javascript" src="js/app/services/services.js"></script>

	<!-- Config -->
 	<script type="text/javascript" src="js/config.js"></script>
	
	<!-- Stylesheets -->
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/jquery-ui.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/bootstrap.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/bootstrap-datetimepicker.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/ui-grid.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/animate.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/livicon.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/prettify.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/docs.min.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/font-awesome.min.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/textAngular.css">
	<link media="all" type="text/css" rel="stylesheet" href="css/lib/ui.bootstrap.materialPicker.css">

	<link media="all" type="text/css" rel="stylesheet" href="css/style.css">

</head>
<!--<body ng-app="opalAdmin" ng-controller="applicationController">-->
<body ng-app="opalAdmin" ng-controller="accountController">
	<div id="page">
		<div ui-view></div>
		<!-- <div ng-view></div> -->
	</div>
 
</body>
</html>
