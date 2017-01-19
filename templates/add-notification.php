<?php session_start();

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
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-plus"></span>
                <h1><strong>Add Notification</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>
                  <span>Home</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span>Notifications</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span><strong>Add Notification</strong></span>
                </span>
              </div>
            </div>   

            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Type</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please select the notification type.
                </p>
              </div>
            </div>    
            <div class="row">
              <div ng-repeat="type in notificationTypes" class="col-md-2"> 
                <div class="panel-container animated" style="cursor:pointer;" ng-class="{pulse: hover, active: newNotification.type == type.id}" ng-click="typeUpdate(type.id)" ng-mouseenter="hover=true" ng-mouseleave="hover=false">
                  <div class="panel-info" ng-class="{active: newNotification.type == type.id}">
                    <div class="panel-content" style="text-align:center">
                      <span style="font-size:30px;" class="glyphicon glyphicon-bell"></span>
                      <div class="option-panel-title">{{type.name}}</div>
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
                    <h2>Title & Message</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please assign an english and french title and message.
                </p>
              </div>
            </div>  
            <div class="row"> 
              <div class="col-md-6">
                <div class="panel" ng-class="(newNotification.name_EN && newNotification.description_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newNotification.name_EN && newNotification.description_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newNotification.name_EN && newNotification.description_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Title</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newNotification.name_EN" ng-change="titleUpdate()" placeholder="English Title" required="required">
                        </div>
                      </div>
                    </div>    
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Message</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <textarea class="form-control" rows="5" ng-model="newNotification.description_EN" ng-change="descriptionUpdate()" placeholder="English Message" required="required"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div> 
              <div class="col-md-6">
                <div class="panel" ng-class="(newNotification.name_FR && newNotification.description_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newNotification.name_FR && newNotification.description_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newNotification.name_FR && newNotification.description_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Titre</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newNotification.name_FR" ng-change="titleUpdate()" placeholder="Titre Français" required="required">
                        </div>
                      </div>
                    </div>    
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Message</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <textarea class="form-control" rows="5" ng-model="newNotification.description_FR" ng-change="descriptionUpdate()" placeholder="Message Français" required="required"></textarea>
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


