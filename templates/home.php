<?php session_start();

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "ATO")) . "ATO/php/config.php";
	// Include config file 
	include_once($configFile);

	$username 	    = $_SESSION[SESSION_KEY_NAME];
	$loginAttempt 	= $_SESSION[SESSION_KEY_LOGIN];
	$registerAttempt= $_SESSION[SESSION_KEY_REGISTER];
	$userid		    = $_SESSION[SESSION_KEY_USERID];
 ?>

  <div id="main">
    <!-- PHP if user is logged in -->
    <? if($loginAttempt == 1) : ?>
    <div id="top" class="home-main" ng-controller="sidePanelMenuController">
      <div class="clearfix">
        <div class="row">
          <div class="col-lg-6">
            <div class="home-header-jumbo">
              <img class="animated rotateIn" src="images/opal_logo_transparent_purple.png" height="120" width="120">
              <h1 class="animated fadeIn">opal <strong>ADMIN</strong> </h1>
            </div>
          </div>
        </div>    
        <div class="row" style="margin-bottom: 20px;">
          <div class="col-md-2 animated slideInLeft">
            <div class="home-side-menu">
              <span class="glyphicon glyphicon-menu-right"></span>
              <h2>Publishing Tools</h2>
            </div>
          </div>
          <div class="col-md-10 animated fadeIn">
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverA}" ng-mouseenter="hoverA=true" ng-mouseleave="hoverA=false" style="cursor:pointer;" ng-click="goToAlias()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-cloud" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Aliases</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Publish internal clinical codes and translating tool.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverB}" ng-mouseenter="hoverB=true" ng-mouseleave="hoverB=false" style="cursor:pointer;" ng-click="goToPost()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-comment" aria-hidden="true"></span>
                    </div>   
                    <div class="panel-title">  
                      <h1>Posts</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Tool for publishing general announcements and treatment team messages.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverC}" ng-mouseenter="hoverC=true" ng-mouseleave="hoverC=false" style="cursor:pointer;" ng-click="goToEducationalMaterial()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-book" aria-hidden="true"></span>     
                    </div>
                    <div class="panel-title">  
                      <h1>Edu Material</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Reference educational material hosted on the web.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div> 
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverD}" ng-mouseenter="hoverD=true" ng-mouseleave="hoverD=false" style="cursor:pointer;" ng-click="goToHospitalMap()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Hospital Maps</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Reference hospital maps, create QR codes.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverE}" ng-mouseenter="hoverE=true" ng-mouseleave="hoverE=false" style="cursor:pointer;" ng-click="goToNotification()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-bell" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Notifications</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Tool to control notification types.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverG}" ng-mouseenter="hoverG=true" ng-mouseleave="hoverG=false" style="cursor:pointer;" ng-click="goToTestResult()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="fa fa-heartbeat" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Test Results</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Publish lab test results according to test group.</p>
                    </div>
                 </div>
                </div>
              </div>
            </div>
          </div> 
        </div>     
        <div class="row" style="margin-bottom: 20px;">
          <div class="col-md-2 animated slideInLeft">
            <div class="home-side-menu">
              <span class="glyphicon glyphicon-menu-right"></span>
              <h2>Administration</h2>
            </div>
          </div>
          <div class="col-md-10 animated fadeIn">
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverH}" ng-mouseenter="hoverH=true" ng-mouseleave="hoverH=false" style="cursor:pointer;" ng-click="goToCron()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-time" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Cron</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Control publish event time and frequency.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverI}" ng-mouseenter="hoverI=true" ng-mouseleave="hoverI=false" style="cursor:pointer;" ng-click="goToPatient()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="fa fa-address-card" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Patients</h1>
                    </div>  
                    <div class="panel-description">
                      <p>List of registered patient. Publishing options. Register new patients.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>     
        <div class="row" style="margin-bottom: 20px;">
          <div class="col-md-2 animated slideInLeft">
            <div class="home-side-menu">
              <span class="glyphicon glyphicon-menu-right"></span>
              <h2>Profile</h2>
            </div>
          </div>
          <div class="col-md-10 animated fadeIn">
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverK}" ng-mouseenter="hoverK=true" ng-mouseleave="hoverK=false" style="cursor:pointer;" ng-click="goToAccount()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Account</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Change profile settings.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverL}" ng-mouseenter="hoverL=true" ng-mouseleave="hoverL=false" style="cursor:pointer;" ng-click="goToUsers()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="fa fa-users" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Users</h1>
                    </div>  
                    <div class="panel-description">
                      <p>Monitor user activity. Change passwords.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="panel-container animated" ng-class="{pulse: hoverM}" ng-mouseenter="hoverM=true" ng-mouseleave="hoverM=false" style="cursor:pointer;" ng-click="goToLogout()">
                <div class="panel-info" style="height: 180px;">
                  <div class="panel-content">
                    <div class="icon-home clearfix">
                      <span style="font-size: 50px;" class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
                    </div>
                    <div class="panel-title">  
                      <h1>Logout</h1>
                    </div>  
                    <div class="panel-description">
                      <p></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>  
        </div>    
         
      </div>
    </div>
    <!-- PHP Else user is not logged in to use the site -->
    <? else: ?> 
    <div class="container login-register">
      <div class="row">
        <div class="col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3"> 

          <div class="login-logo">
            <img class="animated rotateIn" src="images/opal_logo_transparent_purple.png" height="140" width="140">
            <h1><b>opal</b> ADMIN</h1>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3"> 
          <div class="form-box animated" ng-class="{'pulse': !formLoaded}">
            <p class="login-title">
              <span>Log in to start your session</span>
            </p>  
            <div id="block-system-main" class="login-register-block-main">
              <div class="login-content clearfix">
                <form ng-submit="submitLogin()" method="post">
                  <div class="row clearfix" style="margin-top:20px; margin-bottom:20px;">
                    <div class="col-md-12">
                      <label>Username</label>
                      <div class="input-group">
                        <span class="input-group-addon">U</span>
                        <input type="text" class="form-control" required="required" ng-model="login.username">    
                      </div>
                    </div>
                  </div>
                  <div class="row" style="margin-bottom:20px;">    
                    <div class="col-md-12">
                      <label>Password</label>
                      <div class="input-group">
                        <span class="input-group-addon">P</span>
                        <input type="password" class="form-control" required="required" ng-model="login.password">    
                      </div>
                    </div>
                  </div>  
                  <div class="table-buttons">
                    <div class="btn-group btn-group-justified" role="group">
                      <div class="btn-group" role="group">
                        <input class="btn btn-primary" ng-class="{'disabled': !loginFormComplete()}" type="submit" value="Log in">
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>                         
    <? endif; ?> 
    <div class="bannerMessage" ng-class="banner.alertClass">{{banner.message}}</div>
  </div>
<script type="text/ng-template" id="processingModal.htm">
  <div class="modal-header">
    <h1> Processing...</h1>
  </div>
  <div class="modal-body">
    <div class="progress progress-striped active">
      <div class="progress-bar" style="width: 100%"></div>        
    </div>
  </div>
</script>

<script type="text/javascript">
    $(".global-nav li").removeClass("active");
    $(".global-nav li.nav-home").addClass("active");
</script>

          
                      
                    
