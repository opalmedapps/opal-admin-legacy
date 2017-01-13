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
                      <p>Tool for grouping, translating, and publishing internal clinical codes.</p>
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
                      <p>Tool for referencing educational material hosted on the web.</p>
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
                      <p>Tool for referencing hospital maps and creating QR codes.</p>
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
                      <p>Tool for managing notifications.</p>
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
                      <p>Tool for publishing lab test results categorized by test group.</p>
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
                      <p>Control publishing event times and frequency.</p>
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
                      <p>List of registered patient. Publishing control per patient. Register new patients.</p>
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
  </div>

          
                      
                    
