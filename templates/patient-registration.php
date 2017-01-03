<?php session_start();

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "ATO")) . "ATO/php/config.php";
	// Include config file 
	include_once($configFile);

	if (!isset($_SESSION[SESSION_KEY_LOGIN])) {
		echo "<script>
			window.location.href = 'php/user/logout.php';
		      </script> ";
	}

?> 
<div id="main">
    <div id="top">
      <div class="clearfix">
        <div class="row">
          <div class="col-md-2" style="position:fixed;">
            <div ng-controller="sidePanelMenuController">
              <div class="row side-logo" ng-click="goToHome()">
                <div class="col-md-12">
                  <img style="margin-top: -20px;" class="animated rotateIn" src="images/opal_logo_transparent_purple.png" height="130" width="130">
                  <h1 class="animated fadeIn">opal <strong>ADMIN</strong> </h1>
                </div>
              </div>   
            </div>  
            <div class="row">
              <div class="side-menu-title">
                <div class="horz-line" style="height: 10px; text-align: center">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <h2>Summary</h2>
                  </span>  
                </div>  
              </div>
            </div>
            <div class="row">
              <div class="progress progress-striped active">
                <div class="progress-bar" ng-class="{'progress-bar-success': stepProgress == 100}" role="progressbar" aria-valuenow="{{stepProgress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{stepProgress}}%">
                </div>
              </div>
            </div>


            <div class="panel-container animated" ng-class="{pulse: hoverA}" ng-mouseenter="hoverA=true" ng-mouseleave="hoverA=false" style="cursor:pointer;" ng-click="goBack()">
              <div class="side-panel-info">
                <div class="panel-content" style="text-align:center">
                  <span style="font-size: 23px; padding-right:10px;" class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
                  <span style="font-size: 30px; font-weight:700">Go Back</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-10 animated fadeIn" style="margin-left:17%">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-list-alt"></span>
                <h1><strong>Registration</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>
                  <span>Home</span> 
                  <span class="glyphicon glyphicon-menu-right teflon"></span> 
                  <span>Patients</span>
                  <span class="glyphicon glyphicon-menu-right teflon"></span>
                  <span><strong>Registration</strong></span>
                </span>
              </div>
            </div>    
            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Registration SSN</h2>
                  </span>  
                </div>  
              </div>
            </div>
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  Please enter a <strong>12-character</strong> Medicare card number to start the registration process.
                </p>
              </div>
            </div> 
            <div class="row">
              <div class="col-md-8">
                <form ng-submit="checkSSN()">
                  <div class="row">
                    <div class="col-md-5">
                      <div class="form-group has-feedback" ng-class="{'has-success': checkedSSN.status=='valid', 'has-error': checkedSSN.status=='invalid', 'has-warning': checkedSSN.status=='warning'}">
                        <input type="text" class="form-control" required="required" ng-change="checkedSSN.status=null" ng-model="checkedSSN.SSN" placeholder="Enter Medicare Card Number" maxlength="20">
                        <span class="glyphicon form-control-feedback" ng-class="{'glyphicon-ok':checkedSSN.status=='valid', 'glyphicon-remove':checkedSSN.status=='invalid', 'glyphicon-warning-sign':checkedSSN.status=='warning'}" aria-hidden="true"></span>
                      </div>
                    </div>
                    <div class="col-md-4">
                    	<div>
                    		<button class="btn btn-primary" type="submit">
                    			<span class="glyphicon glyphicon-search"></span> Search
                    		</button>
                    	</div>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Email & Password</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required fields:</strong></span>
                  Please add a valid email and password.
                </p>
              </div>
            </div>  
            <div class="row"> 
              <div class="col-md-5">
                <div class="panel" ng-class="(validEmail && newPatient.email == newPatient.confirmEmail) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Email</strong>
                    <span ng-hide="validEmail && newPatient.email == newPatient.confirmEmail" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="validEmail && newPatient.email == newPatient.confirmEmail" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body form-horizontal">
                    <div class="form-group has-feedback" ng-class="validEmail ? 'has-success': 'has-error'">
                      <label class="control-label col-md-3">
                        Email
                      </label>
                      <div class="col-md-9">
                        <input type="text" class="form-control" required="required" ng-change="validateEmail(newPatient.email)" ng-model="newPatient.email" placeholder="example@someone.com">
                        <span ng-show="newPatient.email" class="glyphicon form-control-feedback" ng-class="validEmail ? 'glyphicon-ok': 'glyphicon-remove'" aria-hidden="true"></span>
                      </div>
                    </div>    
                    <div class="form-group has-feedback" ng-class="(newPatient.email == newPatient.confirmEmail && newPatient.confirmEmail) ? 'has-success': 'has-error'">
                      <label class="control-label col-md-3">
                        Confirm Email
                      </label>
                      <div class="col-md-9">
                        <input type="text" class="form-control" required="required" ng-model="newPatient.confirmEmail" placeholder="example@someone.com">
                        <span ng-show="newPatient.confirmEmail" class="glyphicon form-control-feedback" ng-class="(newPatient.email == newPatient.confirmEmail) ? 'glyphicon-ok': 'glyphicon-remove'" aria-hidden="true"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-5">
                <div class="panel" ng-class="(newPatient.password && newPatient.confirmPassword && newPatient.password == newPatient.confirmPassword) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Password</strong>
                    <span ng-hide="newPatient.password && newPatient.confirmPassword && newPatient.password == newPatient.confirmPassword" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newPatient.password && newPatient.confirmPassword && newPatient.password == newPatient.confirmPassword" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body form-horizontal">
                    <div class="form-group has-feedback" ng-class="newPatient.password ? 'has-success': 'has-error'">
                      <label class="control-label col-md-3">
                        Password
                      </label>
                      <div class="col-md-9">
                        <input type="password" class="form-control" required="required" ng-model="newPatient.password">
                        <span ng-show="newPatient.password" class="glyphicon form-control-feedback" ng-class="newPatient.password ? 'glyphicon-ok': 'glyphicon-remove'" aria-hidden="true"></span>
                      </div>
                    </div>    
                    <div class="form-group has-feedback" ng-class="(newPatient.password == newPatient.confirmPassword && newPatient.confirmPassword) ? 'has-success': 'has-error'">
                      <label class="control-label col-md-3">
                        Confirm Password
                      </label>
                      <div class="col-md-9">
                        <input type="password" class="form-control" required="required" ng-model="newPatient.confirmPassword">
                        <span ng-show="newPatient.confirmPassword" class="glyphicon form-control-feedback" ng-class="(newPatient.password == newPatient.confirmPassword) ? 'glyphicon-ok': 'glyphicon-remove'" aria-hidden="true"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
            </div>

            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Language</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please choose the primary language of choice for Opal.
                </p>
              </div>
            </div>  
            <div class="row ">
              <div class="col-md-4">
                <div class="row">
                  <div class="col-md-5">
                    <select ng-model="newPatient.language" ng-options="language.name for language in languages track by language.id" class="form-control" ></select>
                  </div>
                </div>
              </div>  
            </div>


          </div>
        </div>
      </div>
    </div>
    <div class="bannerMessage alert-success">{{bannerMessage}}</div>
  </div>
                 