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
                        <span ng-hide="checkTestsAdded(testList)" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="checkTestsAdded(testList)" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Test(s)</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="checkTestsAdded(testList)" class="description">
                          <p style="margin-top: 5px;">
                            <ul style="max-height: 100px; overflow-y: auto;">
                              <li ng-repeat="test in testList | filter: {added: 1} : 1">
                                {{test.name}} 
                              </li>
                            </ul>
                          </p>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="newTestResult.name_EN && newTestResult.name_FR && newTestResult.description_EN && newTestResult.description_FR" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newTestResult.name_EN && newTestResult.name_FR && newTestResult.description_EN && newTestResult.description_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Title & Description</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newTestResult.name_EN || newTestResult.description_EN" class="description">
                          <p ng-show="newTestResult.name_EN" style="margin-bottom:5px;"><strong>EN: </strong>{{newTestResult.name_EN}}</p>
                          <p ng-show="newTestResult.description_EN"><em>{{newTestResult.description_EN}}</em></p>
                        </div>
                        <div ng-show="newTestResult.name_FR || newTestResult.description_FR" class="description">
                          <p ng-show="newTestResult.name_FR" style="margin-bottom:5px;"><strong>FR: </strong>{{newTestResult.name_FR}}</p>
                          <p ng-show="newTestResult.description_FR"><em>{{newTestResult.description_FR}}</em></p>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="newTestResult.group_EN && newTestResult.group_FR" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newTestResult.group_EN && newTestResult.group_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Test Group</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newTestResult.group_EN" class="description">
                          <p><strong>EN: </strong> {{newTestResult.group_EN}}</p>
                        </div>
                        <div ng-show="newTestResult.group_FR" class="description">
                          <p><strong>FR: </strong> {{newTestResult.group_FR}}</p>
                        </div>
                      </td>  
                    </tr> 
                  </table> 
                </div>
                <div ng-hide="toggleAlertText()" class="table-buttons" style="text-align: center">
                  <form ng-submit="submitTestResult()" method="post">
                    <button class="btn btn-primary" ng-class="{'disabled': !checkForm()}" type="submit">Submit</button>
                  </form>
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
          <div class="col-md-10 animated fadeInRight" style="margin-left:17%">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-plus"></span>
                <h1><strong>Add Test Result</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>
                  <span>Home</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span>Test Results</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span><strong>Add Test Result</strong></span>
                </span>
              </div>
            </div>

            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Tests</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please add one or more tests to categorize and TestResult.
                </p>
              </div>
            </div>  
            <div class="row"> 
              <div class="col-md-8">
                <div class="panel" ng-class="checkTestsAdded(testList) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Test List</strong>
                    <span ng-hide="checkTestsAdded(testList)" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="checkTestsAdded(testList)" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="list-space">
                      <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                        <input class="form-control" type="text" ng-model="testFilter" ng-change="changeTestFilter(testFilter)" placeholder="Search Tests"/>
                      </div>
                      <ul class="list-items">
                        <li ng-repeat="test in testList | filter: searchTestsFilter">
                          <label>
                            <input type="checkbox" ng-click="toggleTestSelection(test)" ng-checked="test.added" /> {{test.name}}
                          </label>
                        </li>
                      </ul>
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
                    <h2>Title, Test Group & Description</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please assign an english and french title, test group, and description.
                </p>
              </div>
            </div>  
            <div class="row"> 
              <div class="col-md-6">
                <div class="panel" ng-class="(newTestResult.name_EN && newTestResult.description_EN && newTestResult.group_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newTestResult.name_EN && newTestResult.description_EN && newTestResult.group_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newTestResult.name_EN && newTestResult.description_EN && newTestResult.group_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Title</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newTestResult.name_EN" ng-change="titleUpdate()" placeholder="English Title" required="required">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Test Group</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newTestResult.group_EN" ng-change="groupUpdate()" typeahead-on-select="groupUpdate()" uib-typeahead="group for group in TestResultGroups_EN | filter:$viewValue" typeahead-min-length="0" placeholder="English" required="required"> 
                        </div>
                      </div>
                    </div>    
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Description</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <textarea class="form-control" rows="5" ng-model="newTestResult.description_EN" ng-change="descriptionUpdate()" placeholder="English Description" required="required"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-6">
                <div class="panel" ng-class="(newTestResult.name_FR && newTestResult.description_FR && newTestResult.group_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newTestResult.name_FR && newTestResult.description_FR && newTestResult.group_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newTestResult.name_FR && newTestResult.description_FR && newTestResult.group_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Titre</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newTestResult.name_FR" ng-change="titleUpdate()" placeholder="Titre Français" required="required">
                        </div>
                      </div>
                    </div> 
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Groupe d'Essai</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newTestResult.group_FR" ng-change="groupUpdate()" typeahead-on-select="groupUpdate()" uib-typeahead="group for group in TestResultGroups_FR | filter:$viewValue" typeahead-min-length="0" placeholder="Francais" required="required"> 
                        </div>
                      </div>
                    </div>    
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Description</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <textarea class="form-control" rows="5" ng-model="newTestResult.description_FR" ng-change="descriptionUpdate()" placeholder="Description Français" required="required"></textarea>
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

