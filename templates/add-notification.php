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
            <div class="panel-container">
              <div class="panel-info">
                <div class="panel-content" style="padding-top: 0">
                  <table class="summary-box" style="width: 100%">
                    <col width="7%">
                    <col width="93%"> 
                    <tr>
                      <td>
                        <span ng-hide="newNotification.type" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newNotification.type" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Type</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newNotification.type" class="description">
                          <p>{{newNotification.type}}</p>
                        </div>
                      </td>
                    </tr>  

                    <tr>
                      <td>
                        <span ng-hide="newNotification.name_EN && newNotification.name_FR && newNotification.description_EN && newNotification.description_FR" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newNotification.name_EN && newNotification.name_FR && newNotification.description_EN && newNotification.description_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Title & Message</strong>
                          </span>
                        </div> 
                      </td>

                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newNotification.name_EN || newNotification.description_EN" class="description">
                          <p ng-show="newNotification.name_EN" style="margin-bottom:5px;"><strong>EN: </strong>{{newNotification.name_EN}}</p>
                          <p ng-show="newNotification.description_EN"><em>{{newNotification.description_EN}}</em></p>
                        </div>
                        <div ng-show="newNotification.name_FR || newNotification.description_FR" class="description">
                          <p ng-show="newNotification.name_FR" style="margin-bottom:5px;"><strong>FR: </strong>{{newNotification.name_FR}}</p>
                          <p ng-show="newNotification.description_FR"><em>{{newNotification.description_FR}}</em></p>
                        </div>
                      </td>
                    </tr>  
                  </table>  

                  <div ng-hide="toggleAlertText()" class="table-buttons" style="text-align: center">
                    <form ng-submit="submitNotification()" method="post">
                      <button class="btn btn-primary" ng-class="{'disabled': !checkForm()}" type="submit">Submit</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel-container animated" ng-class="{pulse: hoverA}" ng-mouseenter="hoverA=true" ng-mouseleave="hoverA=false" style="cursor:pointer;" ng-click="goBack()">
              <div class="side-panel-info">
                <div class="panel-content" style="text-align:center;">
                  <span style="font-size: 23px; padding-right:10px;" class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
                  <span style="font-size: 30px; font-weight:700">Go Back</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-10 animated fadeInRight" style="margin-left:17%;">
            <div class="panel-container" style="text-align: left">
              <uib-accordion close-others="true"> 
                <uib-accordion-group ng-class="newNotification.type ? 'panel-success': 'panel-danger'" is-open="true">
                  <uib-accordion-heading>
                    <h2 class="panel-title"><strong>Assign a type</strong>
                      <span ng-hide="newNotification.type" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="newNotification.type" style="float:right"><em>Complete</em></span>
                    </h2>
                  </uib-accordion-heading>
                  <div class="panel-input">  
                    <ul class="no-list">
                      <li ng-repeat="type in notificationTypes">
                        <label>
                          <input type="radio" ng-model="newNotification.type" ng-change="typeUpdate()" ng-value="type.id" /> {{type.name}}
                        </label>
                      </li>
                    </ul>
                  </div>
                </uib-accordion-group>     
                <uib-accordion-group ng-class="(newNotification.name_EN && newNotification.name_FR) ? 'panel-success': 'panel-danger'" is-open="statusB">
                  <uib-accordion-heading>
                    <h2 class="panel-title"><strong>Assign EN/FR titles</strong>
                      <span ng-hide="newNotification.name_EN && newNotification.name_FR" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="newNotification.name_EN && newNotification.name_FR" style="float:right"><em>Complete</em></span>
                    </h2>
                  </uib-accordion-heading>
                  <div class="panel-input">  
                    <div class="row">
                      <div class="col-md-6">
                        <div class="input-group">
                          <span class="input-group-addon">EN</span>
                          <input class="form-control" type="text" ng-model="newNotification.name_EN" ng-change="titleUpdate()" placeholder="English Title" required="required">
                        </div>
                      </div>    
                      <div class="col-md-6">
                        <div class="input-group">
                          <span class="input-group-addon">FR</span>
                          <input class="form-control" type="text" ng-model="newNotification.name_FR" ng-change="titleUpdate()" placeholder="Titre Français" required="required">
                        </div>
                      </div> 
                    </div>
                  </div>  
                </uib-accordion-group>
                <uib-accordion-group ng-class="(newNotification.description_EN && newNotification.description_FR) ? 'panel-success': 'panel-danger'">
                  <uib-accordion-heading>
                    <h2 class="panel-title"><strong>Assign EN/FR messages</strong>
                      <span ng-hide="newNotification.description_EN && newNotification.description_FR" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="newNotification.description_EN && newNotification.description_FR" style="float:right"><em>Complete</em></span>
                    </h2>
                  </uib-accordion-heading>
                  <div class="panel-input">  
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <textarea class="form-control" rows="10" ng-model="newNotification.description_EN" ng-change="descriptionUpdate()" placeholder="English Message" required="required"></textarea>
                        </div>
                      </div>    
                      <div class="col-md-6">
                        <div class="form-group">
                          <textarea class="form-control" rows="10" ng-model="newNotification.description_FR" ng-change="descriptionUpdate()" placeholder="Message Français" required="required"></textarea>
                        </div>
                      </div> 
                    </div>
                  </div>  
                </uib-accordion-group>    
              </uib-accordion> 
            </div>       
          </div>
        </div>
      </div>
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
	$(".global-nav li.nav-new-notification").addClass("active");
    </script>


