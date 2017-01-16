 <?php session_start();

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "opalAdmin")) . "opalAdmin/php/config.php";
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
            <div class="row panel-container">
              <div class="panel-info">
                <div class="panel-content" style="padding-top: 0">
                  <div class="table-summary">
                    <table class="summary-box" style="width: 100%">
                      <col width="7%">
                      <col width="93%"> 
                      <tr>
                        <td>
                          <span ng-hide="newEduMat.name_EN && newEduMat.name_FR" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="newEduMat.name_EN && newEduMat.name_FR" style="color:#5cb85c" class="glyphicon glyphicon-minus"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Title</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td> 
                          <div ng-show="newEduMat.name_EN" class="description">
                            <p><strong>EN: </strong>{{newEduMat.name_EN}}</p>
                          </div>
                          <div ng-show="newEduMat.name_FR" class="description">
                            <p><strong>FR: </strong>{{newEduMat.name_FR}}</p>
                          </div>
                        </td>
                      </tr>

                      <tr ng-hide="newEduMat.tocs.length">
                        <td>
                          <span ng-hide="newEduMat.url_EN && newEduMat.url_FR" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="newEduMat.url_EN && newEduMat.url_FR" style="color:#5cb85c" class="glyphicon glyphicon-minus"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>URL</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr ng-hide="newEduMat.tocs.length">
                        <td></td>
                        <td>
                          <div ng-show="newEduMat.url_EN" class="description">
                            <p><strong>EN: </strong>{{newEduMat.url_EN}}</p>
                          </div>
                          <div ng-show="newEduMat.url_FR" class="description">
                            <p><strong>FR: </strong>{{newEduMat.url_FR}}</p>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <span ng-hide="newEduMat.type_EN && newEduMat.type_FR" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="newEduMat.type_EN && newEduMat.type_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
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
                          <div ng-show="newEduMat.type_EN" class="description">
                            <p><strong>EN: </strong> {{newEduMat.type_EN}}</p>
                          </div>
                          <div ng-show="newEduMat.type_FR" class="description">
                            <p><strong>FR: </strong> {{newEduMat.type_FR}}</p>
                          </div>
                        </td>  
                      </tr>  
                          
                      <tr>
                        <td>
                          <span ng-hide="newEduMat.phase_in_tx" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="newEduMat.phase_in_tx" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Phase In Treatment</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <div ng-show="newEduMat.phase_in_tx" class="description">
                            <p>{{newEduMat.phase_in_tx.name_EN}}</p>
                          </div>
                        </td>  
                      </tr>     
                        
                      <tr ng-hide="newEduMat.url_EN || newEduMat.url_FR">
                        <td>
                          <span ng-hide="tocsComplete" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="tocsComplete" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td> 
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Table Of Contents</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr ng-hide="newEduMat.url_EN || newEduMat.url_FR">
                        <td></td>
                        <td>
                          <div ng-show="newEduMat.tocs.length" class="description">
                            <p>{{newEduMat.tocs.length}} Item(s)</p>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <span ng-hide="newEduMat.share_url_EN && newEduMat.share_url_FR" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="newEduMat.share_url_EN && newEduMat.share_url_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Supporting PDF</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <div ng-show="newEduMat.share_url_EN" class="description">
                            <p><strong>EN: </strong>{{newEduMat.share_url_EN}}</p>
                          </div>
                          <div ng-show="newEduMat.share_url_FR" class="description">
                            <p><strong>FR: </strong>{{newEduMat.share_url_FR}}</p>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <span ng-hide="demoFilter.sex || demoFilter.age.min != 0 || demoFilter.age.max != 100" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="demoFilter.sex || demoFilter.age.min != 0 || demoFilter.age.max != 100" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Demographic Filter(s)</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <div ng-show="demoFilter.sex" class="description">
                            <p><strong>Sex: </strong>{{demoFilter.sex}}</p>
                          </div>
                          <div ng-show="demoFilter.age.min != 0 || demoFilter.age.max != 100" class="description">
                            <p><strong>Age Group: </strong>{{demoFilter.age.min}} to {{demoFilter.age.max}} </p>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <span ng-hide="checkFilters(termList)" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="checkFilters(termList)" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Expression Filter(s)</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <div ng-show="checkFilters(termList)" class="description">
                            <p>
                              <ul style="max-height: 100px; overflow-y: auto;">
                                <li ng-repeat="term in termList | filter: {added: 1} : 1">
                                  {{term.name}}
                                </li>
                              </ul>
                            </p>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <span ng-hide="checkFilters(dxFilterList)" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="checkFilters(dxFilterList)" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Diagnosis Filter(s)</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <div ng-show="checkFilters(dxFilterList)" class="description">
                            <p>
                              <ul style="max-height: 100px; overflow-y: auto;">
                                <li ng-repeat="Filter in dxFilterList | filter: {added: 1} : 1">
                                  {{Filter.name}}
                                </li>
                              </ul>
                            </p>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <span ng-hide="checkFilters(doctorFilterList)" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="checkFilters(doctorFilterList)" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Doctor Filter(s)</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <div ng-show="checkFilters(doctorFilterList)" class="description">
                            <p>
                              <ul style="max-height: 100px; overflow-y: auto;">
                                <li ng-repeat="Filter in doctorFilterList | filter: {added: 1} : 1">
                                  {{Filter.name}}
                                </li>
                              </ul>
                            </p>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <span ng-hide="checkFilters(resourceFilterList)" class="glyphicon glyphicon-minus"></span>
                          <span ng-show="checkFilters(resourceFilterList)" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                        </td>
                        <td>
                          <div class="horz-line" style="line-height: 0px;">
                            <span class="title" style="background-color: #fff;">
                              <strong>Resource Filter(s)</strong>
                            </span>
                          </div> 
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <div ng-show="checkFilters(resourceFilterList)" class="description">
                            <p>
                              <ul style="max-height: 100px; overflow-y: auto;">
                                <li ng-repeat="Filter in resourceFilterList | filter: {added: 1} : 1">
                                  {{Filter.name}}
                                </li>
                              </ul>
                            </p>
                          </div>
                        </td>
                      </tr>
                    </table>  
                  </div>
                       
                  <div ng-hide="toggleAlertText()" class="table-buttons" style="text-align: center">
                    <form ng-submit="submitEduMat()" method="post">
                      <button class="btn btn-primary" ng-class="{'disabled': !checkForm()}" type="submit">Submit</button>
                    </form>
                  </div>
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
          <div class="col-md-10 animated fadeInRight" style="margin-left:17%;">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-plus"></span>
                <h1><strong>Add Educational Material</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>
                  <span>Home</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span>Edu Material</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span><strong>Add Educational Material</strong></span>
                </span>
              </div>
            </div>    

            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Title</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please assign an english and french title.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-5">
                <div class="panel" ng-class="(newEduMat.name_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newEduMat.name_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newEduMat.name_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Title</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newEduMat.name_EN" ng-change="titleUpdate()" placeholder="English Title" required="required">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-5">
                <div class="panel" ng-class="(newEduMat.name_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newEduMat.name_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newEduMat.name_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Titre</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newEduMat.name_FR" ng-change="titleUpdate()" placeholder="Titre Français" required="required">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>        
            </div>

            <div class="row" ng-hide="newEduMat.tocs.length">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>URL</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row" ng-hide="newEduMat.tocs.length">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please specify the location of the educational material for both English and French versions. If more than one education material, then go to <strong>Table Of Contents</strong> section.
                </p>
              </div>
            </div>  
            <div class="row" ng-hide="newEduMat.tocs.length">
              <div class="col-md-5">
                <div class="panel" ng-class="(newEduMat.url_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newEduMat.url_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newEduMat.url_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">URL</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newEduMat.url_EN" ng-change="urlUpdate()" placeholder="English Title" required="required">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-5">
                <div class="panel" ng-class="(newEduMat.url_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newEduMat.url_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newEduMat.url_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">URL</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newEduMat.url_FR" ng-change="urlUpdate()" placeholder="Titre Français" required="required">
                        </div>
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
                    <h2>Type</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please specify the type of educational material. Choose from the list of existing types (on text field focus) or specify a new type.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-5">
                <div class="panel" ng-class="(newEduMat.type_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newEduMat.type_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newEduMat.type_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Type</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newEduMat.type_EN" ng-change="typeUpdate()" typeahead-on-select="typeUpdate()" uib-typeahead="type for type in EduMatTypes_EN | filter:$viewValue" typeahead-min-length="0" placeholder="English Type" required="required"> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-5">
                <div class="panel" ng-class="(newEduMat.type_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newEduMat.type_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newEduMat.type_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Type</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newEduMat.type_FR" ng-change="typeUpdate()" typeahead-on-select="typeUpdate()" uib-typeahead="type for type in EduMatTypes_FR | filter:$viewValue" typeahead-min-length="0" placeholder="Type Francais" required="required"> 
                        </div>
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
                    <h2>Phase In Treatment</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please assign the phase in treatment this educational material should be publish at.
                </p>
              </div>
            </div>    
            <div class="row">
              <div ng-repeat="phase in phaseInTxs" class="col-md-2">
                <div class="panel-container animated" style="cursor:pointer;" ng-class="{pulse: hover, active: newEduMat.phase_in_tx.serial == phase.serial}" ng-click="phaseUpdate(phase)" ng-mouseenter="hover=true" ng-mouseleave="hover=false">
                  <div class="panel-info" ng-class="{active: newEduMat.phase_in_tx.serial == phase.serial}">
                    <div class="panel-content" style="text-align:center">
                      <span ng-hide="newEduMat.phase_in_tx.serial == phase.serial" style="font-size:30px;" class="glyphicon glyphicon-minus"></span>
                      <span ng-show="newEduMat.phase_in_tx.serial == phase.serial" style="font-size:30px;" class="glyphicon glyphicon-ok"></span>
                      <div class="option-panel-title">{{phase.name_EN}}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>     

            <div class="row" ng-hide="newEduMat.url_EN || newEduMat.url_FR">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Table Of Contents</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row" ng-hide="newEduMat.url_EN || newEduMat.url_FR">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  For more than one educational material in a table of contents, please assign the respective title, URL, and type for both English and French versions.
                </p>
              </div>
            </div> 
            <div class="row" ng-hide="newEduMat.url_EN || newEduMat.url_FR" ng-repeat="toc in newEduMat.tocs" style="margin-bottom: 7px;">
              <div class="col-md-5">
                <div class="panel" ng-class="(toc.name_EN && toc.url_EN && toc.type_EN) ? 'panel-success' : 'panel-danger'">
                  <div class="panel-heading"><strong>English -- Order {{toc.order}}</strong>
                    <span style="float:right; cursor:pointer; margin-left:10px;" ng-click="removeTOC(toc.order)" class="glyphicon glyphicon-remove"></span></span>
                    <span ng-hide="toc.name_EN && toc.url_EN && toc.type_EN" style="float:right;"><em>Incomplete</em></span>
                    <span ng-show="toc.name_EN && toc.url_EN && toc.type_EN" style="float:right;"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Title</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-change="tocUpdate()" ng-model="toc.name_EN" placeholder="English Title" required="required">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">URL</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-change="tocUpdate()" ng-model="toc.url_EN" placeholder="English URL" required="required">
                        </div>
                      </div>
                    </div>  
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Type</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="toc.type_EN" ng-change="tocUpdate()" typeahead-on-select="tocUpdate()" uib-typeahead="type for type in EduMatTypes_EN | filter:$viewValue" typeahead-min-length="0" placeholder="English Type" required="required"> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-5">
                <div class="panel" ng-class="(toc.name_FR && toc.url_FR && toc.type_FR) ? 'panel-success' : 'panel-danger'">
                  <div class="panel-heading"><strong>Français -- Ordre {{toc.order}}</strong>
                    <span style="float:right; cursor:pointer; margin-left:10px;" ng-click="removeTOC(toc.order)" class="glyphicon glyphicon-remove"></span></span>
                    <span ng-hide="toc.name_FR && toc.url_FR && toc.type_FR" style="float:right;"><em>Incomplete</em></span>
                    <span ng-show="toc.name_FR && toc.url_FR && toc.type_FR" style="float:right;"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Titre</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-change="tocUpdate()" ng-model="toc.name_FR" placeholder="Title Français" required="required">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">URL</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-change="tocUpdate()" ng-model="toc.url_FR" placeholder="URL Français" required="required">
                        </div>
                      </div>
                    </div>  
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Type</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="toc.type_FR" ng-change="tocUpdate()" typeahead-on-select="tocUpdate()" uib-typeahead="type for type in EduMatTypes_FR | filter:$viewValue" typeahead-min-length="0" placeholder="Type Français" required="required"> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div ng-hide="newEduMat.url_EN || newEduMat.url_FR" class="row">
              <div class="col-md-1">
                <div class="panel-container animated" style="cursor:pointer;" ng-class="{pulse: hoverJ}" ng-click="addTOC()" ng-mouseenter="hoverJ=true" ng-mouseleave="hoverJ=false">
                  <div class="panel-info">
                    <div class="panel-content" style="text-align:center">
                      <span style="font-size:30px;" class="glyphicon glyphicon-plus"></span>
                      <div class="option-panel-title">Add</div>
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
                    <h2>Supporting PDF</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span><strong>Optional field:</strong></span>
                  Please assign the supporting PDF for when users want to share this educational material.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-5">
                <div class="panel">
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:16px; text-align:right;"> English URL</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group" style="margin-bottom:0px;">
                          <input class="form-control" type="text" ng-model="newEduMat.share_url_EN" placeholder="English URL" required="required">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-5">
                <div class="panel">
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:16px; text-align:right;">URL Français</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group" style="margin-bottom:0px;">
                          <input class="form-control" type="text" ng-model="newEduMat.share_url_FR" placeholder="URL Français" required="required">
                        </div>
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
                    <h2>Demographic Filters</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span><strong>Optional field:</strong></span>
                  Please assign a demographic filter. Leave unmarked for no filter.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-5">
                <h2 style="margin:0 0 7px 0; padding: 0 15px; font-size:30px;">Sex</h2>
              </div>  
              <div class="row">
                <h2 style="margin:0 0 7px 0; padding: 0 15px; font-size:30px;">Age Group</h2>
              </div>
            </div>
            <div class="row">
              <div ng-repeat="sex in sexes" class="col-md-2">
                <div class="panel-container animated" style="cursor:pointer;" ng-class="{pulse: hover, active: demoFilter.sex == sex.name}" ng-click="sexUpdate(sex)" ng-mouseenter="hover=true" ng-mouseleave="hover=false">
                  <div class="panel-info" ng-class="{active: demoFilter.sex == sex.name}">
                    <div class="panel-content" style="text-align:center">
                      <span style="font-size:30px;" class="fa fa-{{sex.icon}}"></span>
                      <div class="option-panel-title">{{sex.name}}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-1"></div>
              <div class="col-md-3">
                <div class="panel">
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:16px; text-align:right;">MIN</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group" style="margin-bottom:0px;">
                          <input class="form-control" ng-model="demoFilter.age.min" type="number" ng-max="demoFilter.age.max" min="0">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-3">
                <div class="panel">
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:16px; text-align:right;">MAX</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group" style="margin-bottom:0px;">
                          <input class="form-control" ng-model="demoFilter.age.max" type="number" max="100" ng-min="demoFilter.age.min">
                        </div>
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
                    <h2>Expression Filters</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span><strong>Optional field:</strong></span>
                  Select one of more expression filters below. This will target patients with these specific filters. Leave blank for no filters.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-8">
                <div class="panel-container">
                  <div class="panel-info">
                    <div class="panel-content">
                      <div class="panel-input">  
                        <div class="list-space">
                          <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                            <input class="form-control" type="text" ng-model="termSearchField" ng-change="searchTerm(termSearchField)" placeholder="Search Terms"/>
                          </div>
                          <div style="padding: 10px;">
                            <label>
                              <input type="checkbox" ng-click="selectAllTerms()"> Select All
                            </label>
                          </div>
                          <ul class="list-items">
                            <li ng-repeat="term in termList | filter: searchTermsFilter">
                              <label>
                                <input type="checkbox" ng-click="selectItem(term)" ng-checked="term.added" /> {{term.name}}
                              </label>
                            </li>
                          </ul>
                        </div>
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
                    <h2>Diagnosis Filters</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span><strong>Optional field:</strong></span>
                  Select one of more diagnosis filters below. This will target patients with these specific diagnoses. Leave blank for no filters.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-8">
                <div class="panel-container">
                  <div class="panel-info">
                    <div class="panel-content">
                      <div class="panel-input">  
                        <div class="list-space">
                          <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                            <input class="form-control" type="text" ng-model="dxSearchField" ng-change="searchDiagnosis(dxSearchField)" placeholder="Search Diagnosis"/>
                          </div>
                          <ul class="list-items">
                            <li ng-repeat="Filter in dxFilterList | filter: searchDxFilter">
                              <label>
                                <input type="checkbox" ng-click="selectItem(Filter)" ng-checked="Filter.added" /> {{Filter.name}}
                              </label>
                            </li>
                          </ul>
                        </div>
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
                    <h2>Doctor Filters</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span><strong>Optional field:</strong></span>
                  Select one of more doctor filters below. This will target patients with these specific doctors. Leave blank for no filters.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-8">
                <div class="panel-container">
                  <div class="panel-info">
                    <div class="panel-content">
                      <div class="panel-input">  
                        <div class="list-space">
                          <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                            <input class="form-control" type="text" ng-model="doctorSearchField" ng-change="searchDoctor(doctorSearchField)" placeholder="Search Doctor"/>
                          </div>
                          <ul class="list-items">
                            <li ng-repeat="Filter in doctorFilterList | filter: searchDoctorFilter">
                              <label>
                                <input type="checkbox" ng-click="selectItem(Filter)" ng-checked="Filter.added" /> {{Filter.name}}
                              </label>
                            </li>
                          </ul>
                        </div>
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
                    <h2>Resource Filters</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span><strong>Optional field:</strong></span>
                  Select one of more resource filters below. This will target patients with these specific resources. Leave blank for no filters.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-8">
                <div class="panel-container">
                  <div class="panel-info">
                    <div class="panel-content">
                      <div class="panel-input">  
                        <div class="list-space">
                          <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                            <input class="form-control" type="text" ng-model="resourceSearchField" ng-change="searchResource(resourceSearchField)" placeholder="Search Resource"/>
                          </div>
                          <ul class="list-items">
                            <li ng-repeat="Filter in resourceFilterList | filter: searchResourceFilter">
                              <label>
                                <input type="checkbox" ng-click="selectItem(Filter)" ng-checked="Filter.added" /> {{Filter.name}}
                              </label>
                            </li>
                          </ul>
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
	$(".global-nav li.nav-new-edu-mat").addClass("active");
    </script>


