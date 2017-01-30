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
          <div class="col-md-2">
            <div ng-include="'templates/side-panel-menu.php'"></div>
          </div>
          <div class="col-md-10 animated fadeIn">
            <div class="row main-title">
              <div class="col-md-6 title-content">
                <span class="fa fa-hourglass-half"></span>
                <h1><strong>Patient Activity</strong></h1>
              </div>
              <div class="col-md-6 title-breadcrumbs"> 
                <span><a href="#/">Home</a> <span class="glyphicon glyphicon-menu-right teflon"></span> <strong>Patient Activity</strong></span>
              </div>
            </div>    

            <div class="panel-container" style="text-align:left">
              <div class="panel-info">
                <div class="panel-input">
                  <div class="row clearfix" style="margin-bottom: 10px;">
                    <div class="col-md-9">
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
                    <!--<div class="table-buttons" style="text-align: center;">
                      <form method="post" ng-submit="submitPublishFlags()">
                        <input class="btn btn-primary" ng-class="{'disabled': !changesMade}" type="submit" value="Save Changes">
                      </form>
                    </div>-->
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


       

          
                      
                    
