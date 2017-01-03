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
          <div class="col-md-2">
            <div ng-include="'templates/side-panel-menu.php'"></div>
          </div>
          <div class="col-md-10 animated fadeIn">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-time"></span>
                <h1><strong>Cron</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>Home <span class="glyphicon glyphicon-menu-right teflon"></span> <strong>Cron</strong></span>
              </div>
            </div>    
            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Cron Date & Time</h2>
                  </span>  
                </div>  
              </div>
            </div>
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 30px;">
                  Use this field to manually set the next cron date and time. To turn off cron, set a date and time in the past.
                </p>
              </div>
            </div> 
            <div class="row">
              <div class="col-md-8">
                <div class="row">
                  <div class="col-md-5" style="padding-top: 10px;">
                    <p class="input-group">
                      <input type="text" class="form-control" uib-datepicker-popup="{{format}}" ng-model="cronDetailsMod.nextCronDate" is-open="opened" min="minDate" ng-change="setChangesMade()" datepicker-options="dateOptions" ng-required="true" close-text="Close" />
                      <span class="input-group-btn">
                        <button class="btn btn-default" ng-click="open($event)"><i class="glyphicon glyphicon-calendar"></i></button>
                      </span>
                    </p>
                  </div>
                  <div class="col-md-2" style="margin-top: -24px;">
                    <div>
                      <uib-timepicker ng-model="cronDetailsMod.nextCronTime" ng-change="setChangesMade()" minute-step="5" show-meridian="false"></uib-timepicker>
                    </div>
                  </div>
                  <div class="col-md-2">
                  	<div class="panel-buttons">
                  		<input class="btn btn-primary" type="button" value="Save Changes" ng-class="{'disabled': !checkForm()}" ng-click="submitCronChange()">
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
                    <h2>Repeat Interval</h2>
                  </span>  
                </div>  
              </div>
            </div>
            <div class="row ">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  Use this field to set the cron repeat interval. Current repeat interval: <strong>{{cronDetails.repeatInterval}} {{cronDetails.repeatUnits}}</strong>
                </p>
              </div>
            </div> 
            <div class="row ">
              <div class="col-md-8">
                <div class="row">
                  <div class="col-md-5">
                    <select ng-model="cronDetailsMod.repeatUnits" ng-change="setChangesMade()" ng-options="unit for unit in repeatUnits" class="form-control" ></select>
                  </div>
                  <div class="col-md-2">
                    <div>
                      <input class="form-control" ng-model="cronDetailsMod.repeatInterval" ng-change="setChangesMade()" type="number" max="59" min="1" required="required">
                    </div>
                  </div>
                  <div class="col-md-2">
                  	<div>
                  		<input class="btn btn-primary" type="button" value="Save Changes" ng-class="{'disabled': !checkForm()}" ng-click="submitCronChange()">
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