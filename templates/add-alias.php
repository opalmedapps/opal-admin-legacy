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
            <div class="panel-container">
              <div class="panel-info">
                <div class="panel-content" style="padding-top: 0">
                  <table class="summary-box" style="width: 100%">
                    <col width="7%">
                    <col width="93%"> 

                    <tr>
                      <td>
                        <span ng-hide="newAlias.source_db" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newAlias.source_db" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Source Database</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newAlias.source_db.name" class="description">
                          <p>{{newAlias.source_db.name}}</p>
                        </div>
                      </td>
                    </tr>     

                    <tr>
                      <td>
                        <span ng-hide="newAlias.name_EN && newAlias.name_FR && newAlias.description_EN && newAlias.description_FR" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newAlias.name_EN && newAlias.name_FR && newAlias.description_EN && newAlias.description_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
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
                        <div ng-show="newAlias.name_EN || newAlias.description_EN" class="description">
                          <p ng-show="newAlias.name_EN" style="margin-bottom:5px;"><strong>EN: </strong>{{newAlias.name_EN}}</p>
                          <p ng-show="newAlias.description_EN"><em>{{newAlias.description_EN}}</em></p>
                        </div>
                        <div ng-show="newAlias.name_FR || newAlias.description_FR" class="description">
                          <p ng-show="newAlias.name_FR" style="margin-bottom:5px;"><strong>FR: </strong>{{newAlias.name_FR}}</p>
                          <p ng-show="newAlias.description_FR"><em>{{newAlias.description_FR}}</em></p>
                        </div>
                      </td>
                    </tr>    

                    <tr>
                      <td>
                        <span ng-hide="newAlias.eduMat" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newAlias.eduMat" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Education Material</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newAlias.eduMat" class="description">
                          <p>{{newAlias.eduMat.name_EN}}</p>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <span ng-hide="newAlias.type" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newAlias.type" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
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
                        <div ng-show="newAlias.type" class="description">
                          <p>{{newAlias.type.name}}</p>
                        </div>
                      </td>  
                    </tr>  

                    <tr>
                      <td>
                        <span ng-hide="newAlias.color" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newAlias.color" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Icon Color</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newAlias.color" class="description"> 
                          <div style="display:inline-block" ng-show="!newAlias.type" class="color-palette" ng-style="{'background-color': newAlias.color}"></div>
                          <span ng-show="newAlias.type" class="glyphicon glyphicon-{{newAlias.type.icon}}" ng-style="{'color': newAlias.color}" style="font-size:30px;"></span>
                          <h3 style="display: inline" ng-show="newAlias.color"> 
                            {{newAlias.color}}
                          </h3>
                        </div> 
                      </td>
                    </tr>
                
                    <tr>
                      <td>
                        <span ng-hide="checkTermsAdded(termList)" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="checkTermsAdded(termList)" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Aliased Expressions</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="checkTermsAdded(termList)" class="description">
                          <p>
                            <ul style="max-height: 100px; overflow-y: auto;">
                              <li ng-repeat="term in termList | filter: {added: true} : true">
                                {{term.id}}
                              </li>
                            </ul>
                          </p>
                        </div>
                      </td>
                    </tr>


                  </table>      

                  <div ng-hide="toggleAlertText()" class="table-buttons" style="text-align: center">
                    <form ng-submit="submitAlias()" method="post">
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
          <div class="col-md-10 animated fadeInRight" style="margin-left:17%">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-plus"></span>
                <h1><strong>Add Alias</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>
                  <span>Home</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span>Aliases</span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span><strong>Add Alias</strong></span>
                </span>
              </div>
            </div>    
            <div class="row">
              <div class="col-md-10 side-menu-title">
                <div style="height: 10px; border-bottom: 1px solid #6f5499;">
                  <span style="background-color:#e6edfa; padding: 0 10px;">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    <h2>Source Database</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please select a source database.
                </p>
              </div>
            </div>    
            <div class="row">
              <div class="col-md-12">
                <div ng-repeat="sourceDB in sourceDBList" class="col-md-1"> 
                  <div class="panel-container animated" style="cursor:pointer;" ng-class="{pulse: hover, active: newAlias.source_db.name == sourceDB.name}" ng-click="sourceDBUpdate(sourceDB)" ng-mouseenter="hover=true" ng-mouseleave="hover=false">
                    <div class="panel-info" ng-class="{active: newAlias.source_db.name == sourceDB.name}">
                      <div class="panel-content" style="text-align:center">
                        <span style="font-size:30px;" class="fa fa-database"></span>
                        <div class="option-panel-title">{{sourceDB.name}}</div>
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
                    <h2>Title & Description</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please assign an english and french title and description.
                </p>
              </div>
            </div>  
            <div class="row"> 
              <div class="col-md-5">
                <div class="panel" ng-class="(newAlias.name_EN && newAlias.description_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newAlias.name_EN && newAlias.description_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newAlias.name_EN && newAlias.description_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body form-horizontal">
                    <div class="form-group">
                      <label class="control-label col-md-2">
                        Title
                      </label>
                      <div class="col-md-10">
                        <input class="form-control" type="text" ng-model="newAlias.name_EN" ng-change="titleUpdate()" placeholder="English Title" required="required">
                      </div>
                    </div>    
                    <div class="form-group">
                      <label class="control-label col-md-2">
                        Description
                      </label>
                      <div class="col-md-10">
                        <textarea class="form-control" rows="5" ng-model="newAlias.description_EN" ng-change="descriptionUpdate()" placeholder="English Description" required="required"></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-5">
                <div class="panel" ng-class="(newAlias.name_FR && newAlias.description_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newAlias.name_FR && newAlias.description_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newAlias.name_FR && newAlias.description_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body form-horizontal">
                    <div class="form-group">
                      <label class="control-label col-md-2">
                        Titre
                      </label>
                      <div class="col-md-10">
                        <input class="form-control" type="text" ng-model="newAlias.name_FR" ng-change="titleUpdate()" placeholder="Titre Français" required="required">
                      </div>
                    </div>    
                    <div class="form-group">
                      <label class="control-label col-md-2">
                        Description
                      </label>
                      <div class="col-md-10">
                        <textarea class="form-control" rows="5" ng-model="newAlias.description_FR" ng-change="descriptionUpdate()" placeholder="Description Français" required="required"></textarea>
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
                    <h2>Educational Material</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span><strong>Optional field:</strong></span>
                  Assigning an educational material will provide a rich-text description of the alias.
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
                            <input class="form-control" type="text" ng-model="eduMatFilter" ng-change="changeEduMatFilter(eduMatFilter)" placeholder="Search Educational Material"/>
                          </div>
                          <ul class="list-items">
                            <li ng-repeat="eduMat in eduMatList | filter: searchEduMatsFilter">
                              <label>
                                <input type="radio" ng-model="newAlias.eduMat" ng-change="eduMatUpdate()" ng-value="eduMat" /> {{eduMat.name_EN}}
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
                    <h2>Type</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please select the alias type.
                </p>
              </div>
            </div>    
            <div class="row">
              <div ng-repeat="type in aliasTypes" class="col-md-2"> 
                <div class="panel-container animated" ng-style="{cursor: newAlias.source_db ? 'pointer' : ''}" ng-class="{pulse: hover && newAlias.source_db, active: newAlias.type.name == type.name, 'disabled': !newAlias.source_db}" ng-click="typeUpdate(type)" ng-mouseenter="hover=true" ng-mouseleave="hover=false">
                  <div class="panel-info" ng-class="{active: newAlias.type.name == type.name}">
                    <div class="panel-content" style="text-align:center">
                      <span style="font-size:30px;" class="glyphicon glyphicon-{{type.icon}}"></span>
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
                    <h2>Icon Color</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please select the icon color to be displayed.
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-4">
                <div class="panel-content">
                  <div class="panel-input">  
                    <div class="color-picker">
                      <div class="cp-main">
                        <material-picker ng-model="newAlias.color" format="'hex'" size="20" hover-model="hoverColor"></material-picker>
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
                    <h2>Aliased Expressions</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please add expressions to alias.
                </p>
              </div>
            </div>  
            <div class="row"> 
              <div class="col-md-8">
                <div class="panel" ng-class="checkTermsAdded(termList) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Expression List</strong>
                    <span ng-hide="checkTermsAdded(termList)" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="checkTermsAdded(termList)" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="list-space" ng-show="newAlias.source_db && newAlias.type">
                      <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                        <input class="form-control" type="text" ng-model="termFilter" ng-change="changeTermFilter(termFilter)" placeholder="Search Terms"/>
                      </div>
                 	  <div style="padding: 10px;">
                        <label>
                          <input type="checkbox" ng-click="selectAllFilteredTerms()"> Select All
                        </label>
                      </div>
                      <ul class="list-items">
                        <li ng-repeat="term in termList | filter: searchTermsFilter">
                          <label>
                            <input type="checkbox" ng-click="toggleTermSelection(term)" ng-checked="term.added" /> {{term.name}}
                          </label>
                        </li>
                      </ul>
                    </div>
                    <div style="text-align:center" ng-hide="newAlias.source_db && newAlias.type">
                      <h1>Please select a source database AND assign an alias type!</h1>
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
	$(".global-nav li.nav-new-alias").addClass("active");
    </script>



