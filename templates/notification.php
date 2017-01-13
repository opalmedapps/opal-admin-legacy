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
                <span class="glyphicon glyphicon-bell"></span>
                <h1><strong>Notifications</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>Home <span class="glyphicon glyphicon-menu-right teflon"></span> <strong>Notifications</strong></span>
              </div>
            </div>    
            <div class="panel-container" style="text-align:left">
              <div class="panel-info">
                <div class="panel-input">
                  <div class="row clearfix" style="margin-bottom: 10px;">
                    <div class="col-md-9">
                      <a style="width:100px;" href="" ng-click="goToAddNotification()" class="btn btn-md btn-outline">
                        <span style="font-size: 17px;" class="glyphicon glyphicon-plus"></span>
                        <span style="font-size: 20px;"><strong>Add</strong></span>
                      </a>
                    </div>
                    <div class="col-md-3">
                      <div class="input-group">
                        <input type="text" class="form-control" ng-model="filterValue" ng-change="filterNotification(filterValue)" placeholder="Search...">
                        <span class="input-group-addon">
                          <span class="glyphicon glyphicon-search"></span>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div id="data-table">
                    <div class="gridStyle" ui-grid="gridOptions" ui-grid-resize-columns style="height:720px"></div>
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
   <script type="text/ng-template" id="deleteNotificationModalContent.htm">
      <div class="modal-header">
        <h2 class="modal-title">
          <span class="glyphicon glyphicon-trash" style="font-size: 30px;"></span> 
          Delete Notification: {{notificationToDelete.name_EN}} / {{notificationToDelete.name_FR}}
        </h2>
      </div>
      <div class="modal-body">
        <div class="bs-callout bs-callout-danger">
          <h4>Notification Delete</h4>
          <p class="deleteText">Are you sure you want to delete the Notification "{{notificationToDelete.name_EN}}" ?</p>
          <form method="post" ng-submit="deleteNotification()">			
            <input class="btn btn-primary" type="submit" value="Delete">
            <input ng-click="cancel()" class="btn btn-danger" type="button" value="Cancel">
          </form>
        </div>
      </div>
    </script>
    <script type="text/ng-template" id="editNotificationModalContent.htm">
      <div class="modal-header">
        <h2 class="modal-title">
          <span class="glyphicon glyphicon-pencil" style="font-size: 30px;"></span> 
          Edit Notification: {{notification.name_EN}} / {{notification.name_FR}}
          <span style="float:right;"> 
            <form method="post" ng-submit="updateNotification()">
              <input class="btn btn-primary" ng-class="{'disabled': !checkForm()}" type="submit" value="Save Changes">
              <input ng-click="cancel()" class="btn btn-danger" type="button" value="Cancel">
            </form>
          </span>      
        </h2>
      </div>
      <div class="modal-body">
        <uib-accordion>
          <uib-accordion-group is-open="statusA.open">
            <uib-accordion-heading>
              <div>
                Titles <i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': statusA.open, 'glyphicon-chevron-right': !statusA.open}"></i>
              </div>
            </uib-accordion-heading>
            <div class="bs-callout bs-callout-info">
              <h4>Current Notification Titles (EN / FR): {{notification.name_EN}} / {{notification.name_FR}}</h4>
              To change the current title(s), enter the new title(s) in the text box(es) below.
            </div>
            <div class="row">
              <div class="col-md-5">
                <div class="input-group">
                  <span class="input-group-addon">EN</span>
                  <input class="form-control" type="text" ng-model="notification.name_EN" ng-change="setChangesMade()" placeholder="English Title" required="required">
                </div>
              </div>
              <div class="col-md-1"></div>
              <div class="col-md-5">
                <div class="input-group">
                  <span class="input-group-addon">FR</span>
                  <input class="form-control" type="text" ng-model="notification.name_FR" ng-change="setChangesMade()" placeholder="Titre Français" required="required">
                </div>
              </div>
            </div>
          </uib-accordion-group>
          <uib-accordion-group is-open="statusB.open">
            <uib-accordion-heading>
              <div>
                Messages <i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': statusB.open, 'glyphicon-chevron-right': !statusB.open}"></i>
              </div>
            </uib-accordion-heading>
            <div class="bs-callout bs-callout-info" style="word-wrap:break-word">
              <h4>Current Notification Messages (EN / FR): {{notification.description_EN}} / {{notification.description_FR}}</h4>
              To change the current message(s), enter the new message(s) in the text area(s) below.
            </div>
            <div class="row">
              <div class="col-md-5">
                <div class="form-group">
                  <textarea class="form-control" rows="10" ng-model="notification.description_EN" ng-change="setChangesMade()" placeholder="English Message" required="required"></textarea>
                </div>
              </div>
              <div class="col-md-1"></div>
              <div class="col-md-5">
                <div class="form-group">
                  <textarea class="form-control" rows="10" ng-model="notification.description_FR" ng-change="setChangesMade()" placeholder="Message Français" required="required"></textarea>
                </div>
              </div>
            </div>
          </uib-accordion-group>
        </uib-accordion>
      </div>
    </script>
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
	$(".global-nav li.nav-notification").addClass("active");
    </script>

