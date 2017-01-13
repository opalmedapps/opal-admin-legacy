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
                <span class="fa fa-address-card"></span>
                <h1><strong>Patients</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>Home <span class="glyphicon glyphicon-menu-right teflon"></span> <strong>Patients</strong></span>
              </div>
            </div>
            <div class="panel-container" style="text-align:left">
              <div class="panel-info">
                <div class="panel-input">
                  <div class="row clearfix" style="margin-bottom:10px;">
                    <div class="col-md-9">
                      <a style="width:200px;" href="" ng-click="goToAddPatient()" class="btn btn-md btn-outline">
                        <span style="font-size: 17px;" class="glyphicon glyphicon-plus"></span>
                        <span style="font-size: 20px;"><strong>Register New</strong></span>
                      </a>
                    </div>
                    <div class="col-md-3">
                      <div class="input-group">
                        <input type="text" class="form-control" ng-model="filterValue" ng-change="filterPatient(filterValue)" placeholder="Search...">
                        <span class="input-group-addon">
                          <span class="glyphicon glyphicon-search"></span>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div id="data-table">
                    <div class="gridStyle" ui-grid="gridOptions" ui-grid-resize-columns style="height:720px"></div>
                    <div class="table-buttons" style="text-align: center;">
                      <form method="post" ng-submit="submitTransferFlags()">
                        <input class="btn btn-primary" ng-class="{'disabled': !changesMade}" type="submit" value="Save Changes">
                      </form>
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
   <div class="bannerMessage alert-success">{{bannerMessage}}</div>
  </div>

