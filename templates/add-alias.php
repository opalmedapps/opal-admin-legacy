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
          <div class="col-md-3 animated fadeInDown">
            <div class="panel-container">
              <div class="panel-info">
                <div class="panel-title-custom" style="border-bottom: 0">
                  <h2 style="margin-bottom: 15px; margin-top: 0;">
                    <span style="font-size: 20px;" class="glyphicon glyphicon-th-list" aria-hidden="true"></span>
                    Progress: {{stepProgress}}% Complete
                  </h2>
                  <div class="progress progress-striped active">
                    <div class="progress-bar" ng-class="{'progress-bar-success': stepProgress == 100}" role="progressbar" aria-valuenow="{{stepProgress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{stepProgress}}%">
                    </div>
                  </div>
                </div>
                <div class="panel-content" style="padding-top: 0">
                  <ul class="list-group">
                    <li class="list-group-item" ng-show="newAlias.name_EN || newAlias.name_FR">
                      <strong>Titles:</strong> EN: {{newAlias.name_EN}} | FR: {{newAlias.name_FR}}
                    </li>
                    <li class="list-group-item" ng-show="newAlias.description_EN || newAlias.description_FR">
                      <strong>Descriptions:</strong> EN: {{newAlias.description_EN}} | FR: {{newAlias.description_FR}}
                    </li>
                    <li class="list-group-item" ng-show="newAlias.eduMat">
                      <strong>Educational Material:</strong> {{newAlias.eduMat.name_EN}}
                    </li>
                    <li class="list-group-item" ng-show="newAlias.type">
                      <strong>Type:</strong> {{newAlias.type}}
                    </li>
                    <li class="list-group-item" ng-show="checkTermsAdded(termList)"> 
                      <strong>Term(s):</strong>
                      <p style="margin-top: 10px;">
                        <ul style="max-height: 100px; overflow-y: auto;">
                          <li ng-repeat="term in termList | filter: {added: true} : true">
                            {{term.name}}
                          </li>
                        </ul>
                      </p>
                    </li>
                  </ul>
                  <div ng-hide="toggleAlertText()" class="table-buttons" style="text-align: center">
                    <form ng-submit="submitAlias()" method="post">
                      <button class="btn btn-primary" ng-class="{'disabled': !checkForm()}" type="submit">Submit</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="side-panel-menu panel-container animated" ng-class="{pulse: hoverA}" ng-mouseenter="hoverA=true" ng-mouseleave="hoverA=false" style="cursor:pointer;" ng-click="goBack()">
              <div class="panel-info">
                <div class="panel-content" style="text-align:center">
                  <span style="font-size: 60px;" class="glyphicon glyphicon-circle-arrow-left" aria-hidden="true"></span><br>
                  <br><br>
                  <span style="font-size: 40px;">Aliases</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-9 animated fadeInRight">
            <div class="panel-container" style="text-align: left">
              <uib-accordion close-others="true">
                <uib-accordion-group ng-class="(newAlias.name_EN && newAlias.name_FR) ? 'panel-success': 'panel-danger'" is-open="true">
                  <uib-accordion-heading> 
                    <h2 class="panel-title"><strong>Assign EN/FR titles</strong>
                      <span ng-hide="newAlias.name_EN && newAlias.name_FR" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="newAlias.name_EN && newAlias.name_FR" style="float:right"><em>Complete</em></span>
                    </h2>
                  </uib-accordion-heading>
                  <div class="panel-input">  
                    <div class="row">
                      <div class="col-md-6">
                        <div class="input-group">
                          <span class="input-group-addon">EN</span>
                          <input class="form-control" type="text" ng-model="newAlias.name_EN" ng-change="titleUpdate()" placeholder="English Title" required="required">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="input-group">
                          <span class="input-group-addon">FR</span>
                          <input class="form-control" type="text" ng-model="newAlias.name_FR" ng-change="titleUpdate()" placeholder="Titre Français" required="required">
                        </div>
                      </div>
                    </div>
                  </div>
                </uib-accordion-group>
                <uib-accordion-group ng-class="(newAlias.description_EN && newAlias.description_FR) ? 'panel-success': 'panel-danger'" is-open="statusB">
                  <uib-accordion-heading>
                    <h2 class="panel-title"><strong>Assign EN/FR descriptions</strong>
                      <span ng-hide="newAlias.description_EN && newAlias.description_FR" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="newAlias.description_EN && newAlias.description_FR" style="float:right"><em>Complete</em></span>
                    </h2>
                  </uib-accordion-heading>
                  <div class="panel-input">  
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <textarea class="form-control" rows="5" ng-model="newAlias.description_EN" ng-change="descriptionUpdate()" placeholder="English Description" required="required"></textarea>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <textarea class="form-control" rows="5" ng-model="newAlias.description_FR" ng-change="descriptionUpdate()" placeholder="Description Français" required="required"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </uib-accordion-group>
                <uib-accordion-group is-open="statusD.open">
                  <uib-accordion-heading>
                    <h2 class="panel-title"><strong>Attach Educational Material</strong>
                      <span style="float:right"><em>Optional</em></span>
                    </h2>
                  </uib-accordion-heading>
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
                </uib-accordion-group>  
                <uib-accordion-group ng-class="newAlias.type ? 'panel-success': 'panel-danger'" is-open="statusC.open">
                  <uib-accordion-heading>
                    <h2 class="panel-title"><strong>Assign a type and icon color</strong>
                      <span ng-hide="newAlias.type" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="newAlias.type" style="float:right"><em>Complete</em></span>
                    </h2>
                  </uib-accordion-heading>
                  <div class="panel-input">  
                    <div class="row" style="margin-bottom: 10px;">
                      <div ng-repeat="type in aliasTypes" class="col-md-4">
                        <a href="" ng-model="newAlias.type" ng-click="typeUpdate(type.name)" class="btn btn-lg btn-block btn-outline" ng-class="{'active-purple-full': newAlias.type == type.name}">{{type.name}}</a>
                        <div class="color-data" ng-show="newAlias.type == type.name">
                          <div class="color-picked" title="Selected color">
                            <div class="color-palette" ng-style="{'background-color': newAlias.color}"></div>
                          </div>
                          <div class="color-label hex">
                            <h4>HEX</h4>
                          </div>   
                          <div class="color-code">
                            <h4>{{newAlias.color}}</h4>
                          </div>
                        </div> 
                      </div>
                    </div>  
                    <div class="row" style="padding-top: 15px; border-top: solid 1px #ddd;" ng-show="newAlias.type">
                      <div class="col-md-4">
                        <div class="panel panel-default">
                          <div class="panel-heading">Existing Colors</div>
                          <table class="table table-condensed fixed-header">
                            <tbody>
                              <tr ng-repeat="existingColor in existingColorTags" class="color"> 
                                <td class="color-picked">
                                  <div class="color-palette" ng-style="{'background-color': existingColor.color}" ng-click="newAlias.color = existingColor.color"></div>
                                </td>
                                <td class="color-assignee">
                                  <h5>{{existingColor.name_EN}}</h5>
                                </td>
                                <td class="color-code">
                                  <h5>{{existingColor.color}}</h5>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <div class="col-md-8">
                        <div class="color-picker">
                          <div class="cp-description">
                            <p class="cp-header">
                              Choose an icon color.
                            </p>
                            <p class="cp-sub-header">
                              Or select an existing color on the left.
                            </p>
                          </div>  
                          <div class="cp-main">
                            <material-picker ng-model="newAlias.color" format="'hex'" size="20" hover-model="hoverColor"></material-picker>
                          </div> 
                        </div>
                      </div>   
                    </div>               
  
                  </div>
                </uib-accordion-group>     
                <uib-accordion-group ng-class="checkTermsAdded(termList) ? 'panel-success': 'panel-danger'" is-open="statusE.open">
                  <uib-accordion-heading>
                    <h2 class="panel-title"><strong>Assign terms</strong>
                      <span ng-hide="checkTermsAdded(termList)" style="float:right"><em>Incomplete</em></span>
                      <span ng-show="checkTermsAdded(termList)" style="float:right"><em>Complete</em></span>
                    </h2>
                  </uib-accordion-heading>
                  <div class="panel-input">  
                    <div class="list-space">
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
	$(".global-nav li.nav-new-alias").addClass("active");
    </script>



