          <div ng-controller="sidePanelMenuController">
            <div class="row side-logo" ng-click="goToHome()">
              <div class="col-md-12">
                <img style="margin-top: -20px;" class="animated rotateIn" src="images/opal_logo_transparent_purple.png" height="130" width="130">
                <h1>opal <strong>ADMIN</strong> </h1>
              </div>
            </div>      
            <div class="row" ng-if="isAuthorized([userRoles.admin])">
              <div class="side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Publishing Tools</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row side-panel-menu" ng-if="isAuthorized([userRoles.admin])">            
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">   
                <div class="panel-container animated" ng-class="{pulse: hoverC, active: currentPage == 'alias'}" ng-mouseenter="hoverC=true" ng-mouseleave="hoverC=false" style="cursor:pointer;" ng-click="goToAlias()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'alias'}">
                    <div class="panel-content">
                      <span style="font-size: 23px;" class="glyphicon glyphicon-cloud" aria-hidden="true"></span>
                      <div class="side-panel-title">Aliases</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverD, active: currentPage == 'post'}" ng-mouseenter="hoverD=true" ng-mouseleave="hoverD=false" style="cursor:pointer;" ng-click="goToPost()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'post'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="glyphicon glyphicon-comment" aria-hidden="true"></span>
                      <div class="side-panel-title">Posts</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverE, active: currentPage == 'educational-material'}" ng-mouseenter="hoverE=true" ng-mouseleave="hoverE=false" style="cursor:pointer;" ng-click="goToEducationalMaterial()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'educational-material'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="glyphicon glyphicon-book" aria-hidden="true"></span>
                      <div class="side-panel-title">Edu Material</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverF, active: currentPage == 'hospital-map'}" ng-mouseenter="hoverF=true" ng-mouseleave="hoverF=false" style="cursor:pointer;" ng-click="goToHospitalMap()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'hospital-map'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>
                      <div class="side-panel-title">Hospital Maps</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverG, active: currentPage == 'notification'}" ng-mouseenter="hoverG=true" ng-mouseleave="hoverG=false" style="cursor:pointer;" ng-click="goToNotification()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'notification'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="glyphicon glyphicon-bell" aria-hidden="true"></span>
                      <div class="side-panel-title">Notifications</div>
                    </div>
                  </div>
                </div>
              </div>      
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverN, active: currentPage == 'test-result'}" ng-mouseenter="hoverN=true" ng-mouseleave="hoverN=false" style="cursor:pointer;" ng-click="goToTestResult()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'test-result'}">
                    <div class="panel-content">
                      <i style="font-size: 21px;" class="fa fa-heartbeat" aria-hidden="true"></i>
                      <div class="side-panel-title">Test Results</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row" ng-if="isAuthorized([userRoles.admin])">
              <div class="side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Adminstration</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row side-panel-menu" ng-if="isAuthorized([userRoles.admin])">
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverH, active: currentPage == 'cron'}" ng-mouseenter="hoverH=true" ng-mouseleave="hoverH=false" style="cursor:pointer;" ng-click="goToCron()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'cron'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="glyphicon glyphicon-time" aria-hidden="true"></span>
                      <div class="side-panel-title">Cron</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverI, active: currentPage == 'patients'}" ng-mouseenter="hoverI=true" ng-mouseleave="hoverI=false" style="cursor:pointer;" ng-click="goToPatient()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'patients'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="fa fa-address-card" aria-hidden="true"></span>
                      <div class="side-panel-title">Patients</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverO, active: currentPage == 'patient-activity'}" ng-mouseenter="hoverO=true" ng-mouseleave="hoverO=false" style="cursor:pointer;" ng-click="goToPatientActivity()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'patient-activity'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="fa fa-hourglass-half" aria-hidden="true"></span>
                      <div class="side-panel-title">Patient Activity</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row" ng-if="isAuthorized([userRoles.admin])">
              <div class="side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Profile</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row side-panel-menu" ng-if="isAuthorized([userRoles.admin])">
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverK, active: currentPage == 'account'}" ng-mouseenter="hoverK=true" ng-mouseleave="hoverK=false" style="cursor:pointer;" ng-click="goToAccount()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'account'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                      <div class="side-panel-title">Account</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.admin])">
                <div class="panel-container animated" ng-class="{pulse: hoverL, active: currentPage == 'users'}" ng-mouseenter="hoverL=true" ng-mouseleave="hoverL=false" style="cursor:pointer;" ng-click="goToUsers()">
                  <div class="side-panel-info" ng-class="{active: currentPage == 'users'}">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="fa fa-users" aria-hidden="true"></span>
                      <div class="side-panel-title">Users</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6" ng-if="isAuthorized([userRoles.all])">
                <div class="panel-container animated" ng-class="{pulse: hoverM}" ng-mouseenter="hoverM=true" ng-mouseleave="hoverM=false" style="cursor:pointer;" ng-click="goToLogout()">
                  <div class="side-panel-info">
                    <div class="panel-content">
                      <span style="font-size: 21px;" class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
                      <div class="side-panel-title">Logout</div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div> 

