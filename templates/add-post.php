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
                        <span ng-hide="newPost.type" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newPost.type" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
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
                        <div ng-show="newPost.type" class="description">
                          <p>{{newPost.type.name}}</p>
                        </div>
                      </td>
                    </tr>       

                    <tr>
                      <td>
                        <span ng-hide="newPost.name_EN && newPost.name_FR" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newPost.name_EN && newPost.name_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
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
                        <div ng-show="newPost.name_EN" class="description">
                          <p style="margin-bottom:5px;"><strong>EN: </strong>{{newPost.name_EN}}</p>
                        </div>
                        <div ng-show="newPost.name_FR" class="description">
                          <p style="margin-bottom:5px;"><strong>FR: </strong>{{newPost.name_FR}}</p>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td>    
                        <span ng-hide="newPost.body_EN && newPost.body_FR" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newPost.body_EN && newPost.body_FR" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Body</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newPost.body_EN && newPost.body_FR" class="description">
                          <p>See rendered output on the right</p>
                        </div>
                      </td>
                    </tr>
 
                    <tr>
                      <td>    
                        <span ng-hide="newPost.publish_date && newPost.publish_time" class="glyphicon glyphicon-minus"></span>
                        <span ng-show="newPost.publish_date && newPost.publish_time" style="color:#5cb85c" class="glyphicon glyphicon-ok"></span>
                      </td>
                      <td>
                        <div class="horz-line" style="line-height: 0px;">
                          <span class="title" style="background-color: #fff;">
                            <strong>Publish Date</strong>
                          </span>
                        </div> 
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <div ng-show="newPost.publish_date && newPost.publish_time" class="description">
                          <p>{{newPost.publish_date | date:'fullDate'}} at {{newPost.publish_time | date:'HH:mm'}}</p>
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

                  <div ng-hide="toggleAlertText()" class="table-buttons" style="text-align: center">
                    <form ng-submit="submitPost()" method="post">
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
          <div class="col-md-10 animated fadeInRight" style="margin-left:17%">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="glyphicon glyphicon-plus"></span>
                <h1><strong>Add Post</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span>
                  <span><a href="#/">Home</a></span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span><a href="#/post">Posts</a></span>
                  <span class="teflon glyphicon glyphicon-menu-right"></span> 
                  <span><strong>Add Post</strong></span>
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
                  Please select the type of post.
                </p>
              </div>
            </div>    
            <div class="row">
              <div ng-repeat="type in postTypes" class="col-md-2"> 
                <div class="panel-container animated" style="cursor:pointer;" ng-class="{pulse: hover, active: newPost.type.name == type.name}" ng-click="typeUpdate(type)" ng-mouseenter="hover=true" ng-mouseleave="hover=false">
                  <div class="panel-info" ng-class="{active: newPost.type.name == type.name}">
                    <div class="panel-content" style="text-align:center">
                      <span style="font-size:30px;" class="fa fa-{{type.icon}}"></span>
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
                <div class="panel" ng-class="(newPost.name_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newPost.name_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newPost.name_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Title</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newPost.name_EN" ng-change="titleUpdate()" placeholder="English Title" required="required">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>          
              <div class="col-md-5">
                <div class="panel" ng-class="(newPost.name_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newPost.name_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newPost.name_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-2">
                        <div style="font-size:18px; text-align:right;">Titre</div>
                      </div>
                      <div class="col-md-10">
                        <div class="form-group">
                          <input class="form-control" type="text" ng-model="newPost.name_FR" ng-change="titleUpdate()" placeholder="Titre Français" required="required">
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
                    <h2>Body</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 5px;">
                  <span style="color:#d9534f"><strong>Required field:</strong></span>
                  Please assign the message content in both english and in french.
                </p>
                <p style="margin-bottom: 5px;">
                  <span style="color:#d9534f"><strong>HTML exceptions</strong></span><br>
                  <ul style="margin-left: 15px; margin-bottom: 10px;">
                    <li>Add <strong>img-responsive</strong> class to img tags. Ex: 
                      <code data-lang="html">&lt;img class="img-responsive"&gt;</code> 
                    </li>
                    <li>No <strong>absolute</strong> measurements! Ie. 
                      <code data-lang="html">&lt;span style="width:200px"&gt;</code>
                    </li>
                  </ul>
                </p>
              </div>
            </div>  
            <div class="row">
              <div class="col-md-10">
                <div class="panel" ng-class="(newPost.body_EN) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>English</strong>
                    <span ng-hide="newPost.body_EN" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newPost.body_EN" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-8">
                        <div class="form-group">
                          <div text-angular ng-model="newPost.body_EN" ng-change="bodyUpdate()"></div>
                        </div> 
                      </div>  
                      <div class="col-md-4">
                        <div style="text-align:center;">
                          <span style="font-size:20px;">iPhone 4 Rendered</span>
                        </div>
                        <div class="render-html-iphone4"> 
                          <iframe height="100%" width="100%" srcdoc="{{newPost.body_EN | deliberatelyTrustAsHtml}}" frameborder="0"></iframe>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>           
            <div class="row">
              <div class="col-md-10">
                <div class="panel" ng-class="(newPost.body_FR) ? 'panel-success': 'panel-danger'">
                  <div class="panel-heading"><strong>Français</strong>
                    <span ng-hide="newPost.body_FR" style="float:right"><em>Incomplete</em></span>
                    <span ng-show="newPost.body_FR" style="float:right"><em>Complete</em></span>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-8">
                        <div class="form-group">
                          <div text-angular ng-model="newPost.body_FR" ng-change="bodyUpdate()"></div>
                        </div> 
                      </div>  
                      <div class="col-md-4">
                        <div style="text-align:center;">
                          <span style="font-size:20px;">iPhone 4 Rendered</span>
                        </div>
                        <div class="render-html-iphone4"> 
                          <iframe height="100%" width="100%" srcdoc="{{newPost.body_FR | deliberatelyTrustAsHtml}}" frameborder="0"></iframe>
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
                    <h2>Publish Date</h2>
                  </span>  
                </div>  
              </div>
            </div>   
            <div class="row">
              <div class="col-md-10">
                <p style="margin-bottom: 10px;">
                  <span ng-hide="newPost.type.name =='Announcement'"><strong>Optional field:</strong></span>
                  <span ng-show="newPost.type.name =='Announcement'" style="color:#d9534f"><strong>Required field:</strong></span>
                  Please select the publish date. 
                </p>
              </div>
            </div>    
            <div class="row">
              <div class="col-md-8">
                <div class="row">
                  <div class="col-md-6" style="padding-top: 10px;">
                    <p class="input-group">
                      <input type="text" class="form-control" uib-datepicker-popup="{{format}}" ng-model="newPost.publish_date" ng-change="publishDateUpdate()" is-open="popup.opened" min="minDate" datepicker-options="dateOptions" ng-required="true" close-text="Close" />
                      <span class="input-group-btn">
                        <button class="btn btn-default" ng-click="open()"><i class="glyphicon glyphicon-calendar"></i></button>
                      </span>
                    </p>
                  </div>
                  <div class="col-md-6" style="margin-top: -24px;">
                    <div>
                      <uib-timepicker ng-model="newPost.publish_time" ng-change="publishDateUpdate()" minute-step="5" show-meridian="false"></uib-timepicker>
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
	$(".global-nav li.nav-new-post").addClass("active");
    </script>



