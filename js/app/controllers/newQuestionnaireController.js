angular.module('opalAdmin.controllers.newQuestionnaireController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.pagination', 'ui.grid.selection', 'ui.grid.resizeColumns']).

controller('newQuestionnaireController',function($scope, $state, $filter, questionnaireAPIservice, filterAPIservice, Session, uiGridConstants){
    
    // navigation function
    $scope.goBack = function () {
        $state.go('questionnaire-manage');
    };

    // Default booleans
    $scope.title = {open:false, show:true};
    $scope.privacy = {open:false, show:false};
    $scope.questions = {open:false, show:false};
    $scope.tags = {open:false, show:false};

    // get current user id
    var user = Session.retrieveObject('user');
    var userid = user.id;

    // initialize variables
    $scope.tagList = [];
    $scope.groupList = [];
    $scope.selectedGroups;
    $scope.tagFilter = "";

    // step bar
    var steps = {
        title: { completed: false },
        privacy: { completed: false },
        questions: { completed: false },
        tags: { completed: false }
    };

    $scope.numOfCompletedSteps = 0;
    $scope.stepTotal = 4;
    $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

    /* Function for the "Processing" dialog */
    var processingModal;
    $scope.showProcessingModal = function () {

        processingModal = $uibModal.open({
            templateUrl: 'processingModal.htm',
            backdrop: 'static',
            keyboard: false,
        });
    };

    // Function to calculate / return step progress
    function trackProgress(value, total) {
        return Math.round(100 * value / total);
    };

    // Function to return number of steps completed
    function stepsCompleted(steps) {
        var numberOfTrues = 0;
        for (var step in steps) {
            if (steps[step].completed === true) {
                numberOfTrues++;
            }
        }
        return numberOfTrues;
    };

     // new questionnaire object
    $scope.newQuestionnaire = {
        name_EN: "",
        name_FR: "",
        private: undefined,
        publish: 0,
        created_by: userid,
        last_updated_by: userid,
        groups: [],
        tags: []
    };


    // update form functions
    $scope.titleUpdate = function () {

        $scope.title.open = true;

        if (!$scope.newQuestionnaire.name_EN && !$scope.newQuestionnaire.name_FR) {
            $scope.title.open = false;
        }

        if ($scope.newQuestionnaire.name_EN && $scope.newQuestionnaire.name_FR) {

            $scope.privacy.show = true;

            steps.title.completed = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        } else {

            steps.title.completed = false; 
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        }
    };

    $scope.privacyUpdate = function (value) {

        $scope.privacy.open = true;

        if (value==0 || value==1) {

            // update value
            $scope.newQuestionnaire.private = value;

            $scope.questions.show = true;

            steps.privacy.completed = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        } else {

            steps.privacy.completed = false; 
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        }
    };

    var tagsUpdate = function (tagList) {

        $scope.tags.open = true;

        // update steps bar
        if ($scope.checkTags(tagList)) {

            steps.tags.completed = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        } else {

            $scope.tags.open = false;
            steps.tags.completed = false; 
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        }
    };

    var questionsUpdate = function () {

        $scope.questions.open = true;
        if ($scope.newQuestionnaire.groups.length) {

            $scope.tags.show = true;
            steps.questions.completed = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        } else {

            $scope.questions.open = false
            steps.questions.completed = false; 
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        }
    };


    // table
    // Filter in table
    $scope.filterOptions = function (renderableRows) {
        var matcher = new RegExp($scope.filterValue, 'i');
        renderableRows.forEach(function (row) {
            var match = false;
            ['name_EN', 'category_EN', 'library_name_EN'].forEach(function (field) {
                if (row.entity[field].match(matcher)) {
                    match = true;
                }
            });
            if (!match) {
                row.visible = false;
            }
        });
        return renderableRows;
    };

    // Template for table
    var cellTemplateName = '<div class="ui-grid-cell-contents" ' +
        '<p>{{row.entity.name_EN}} / {{row.entity.name_FR}}</p></div>';
    var cellTemplateCat =  '<div class="ui-grid-cell-contents" ' +
        '<p>{{row.entity.category_EN}} / {{row.entity.category_FR}}</p></div>';
    var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
        '<p>{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</p></div>';
    var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' + 
        '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
    var cellTemplateTags = '<div class="ui-grid-cell-contents" ng-repeat="tag in row.entity.tags"' +
        '<p>{{tag.name_EN}} / {{tag.name_FR}} ;</p></div>';
    
     
    // Table Data binding
    $scope.gridOptions = {
        data: 'groupList',
        columnDefs: [
            { field: 'name_EN', displayName: 'Group (EN / FR)', cellTemplate: cellTemplateName, width: '20%' },
            { field: 'category_EN', displayName: 'Category (EN / FR)', cellTemplate: cellTemplateCat, width: '25%' },
            { field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '15%' },
            { field: 'private', displayName: 'Privacy', cellTemplate:cellTemplatePrivacy, width: '10%', filter: {
                        type: uiGridConstants.filter.SELECT,
                        selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
                    }
            },
            { field: 'tags', displayName: 'Tags (EN / FR)', cellTemplate: cellTemplateTags, enableFiltering:false, width:'30%'}
        ],
        enableColumnResizing: true,
        enableFiltering: true,
        enableSorting: true,
        enableRowSelection: true,
        //enableSelectAll: true,
        enableSelectionBatchEvent: true,
        //showGridFooter:true,
        onRegisterApi: function (gridApi) {
            $scope.gridApi = gridApi;
            gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
            gridApi.selection.on.rowSelectionChanged($scope,function(row){
                selectUpdate();
                questionsUpdate();
            });
        },
    };

    // cancel selection
    $scope.cancelSelection = function () {
        $scope.gridApi.selection.clearSelectedRows();
        selectUpdate();
    };

    // select all rows
    $scope.selectAll = function() {
        $scope.gridApi.selection.selectAllRows();
        selectUpdate();
    };

    // function to update the newQuestionnaire content after changing selection
    var selectUpdate = function () {
        $scope.selectedGroups = $scope.gridApi.selection.getSelectedGridRows();
        var selectedNum = $scope.gridApi.selection.getSelectedCount();
        if (selectedNum === 0) { 
            $scope.newQuestionnaire.groups = [];
        } else {
            var tempGroupArray = [];
            for (var i=0; i<selectedNum; i++) {
                var group = {
                    position: i+1,
                    questiongroup_serNum: $scope.selectedGroups[i].entity.serNum,
                    optional: 0,
                    name_EN: $scope.selectedGroups[i].entity.name_EN,
                    name_FR: $scope.selectedGroups[i].entity.name_FR
                };
                tempGroupArray.push(group);
                $scope.newQuestionnaire.groups = tempGroupArray.slice(0);
            }
        }
    };

    // API getting group list
    questionnaireAPIservice.getGroupsWithQuestions(userid).success(function (response) {
        $scope.groupList = response;
    });

    // get tag list
    questionnaireAPIservice.getTag().success(function (response) {
        $scope.tagList = response;
    });

    // assign search field 
    $scope.searchTag = function (field) {
        $scope.tagFilter = field;
    };

    // search filter
    $scope.searchTagFilter = function (Filter) {
        var keyword = new RegExp($scope.tagFilter, 'i');
        return !$scope.tagFilter || keyword.test(Filter.name_EN);
    };

    // Function to toggle Item in a list on/off
    $scope.selectTag = function (tag) {
        if (tag.added) {
            tag.added = 0;
        } else {
            tag.added = 1;
        }

        tagsUpdate($scope.tagList);
    };

    // add tags
    function addTags(tagList) {
        angular.forEach(tagList, function (Filter) {
            if (Filter.added)
                $scope.newQuestionnaire.tags.push(Filter.serNum);
        });
    }

    // check if there's any tag added
    $scope.checkTags = function (tagList) {
        var tagsAdded = false;
        angular.forEach(tagList, function (Filter) {
            if (Filter.added)
                tagsAdded = true;
        });
        return tagsAdded;
    };

    // new tag
    $scope.newTag = {
        name_EN: '',
        name_FR: '',
        level: undefined,
        last_updated_by: userid,
        created_by: userid
    };

    $scope.addNewTag = function () {

        // Prompt to confirm user's action
        var confirmation = confirm("Confirm to create the new tag [" + $scope.newTag.name_EN + "].");
        if (confirmation) {
            // write in to db
            $.ajax({
                type: "POST",
                url: "php/questionnaire/addTag.php",
                data: $scope.newTag,
                success: function () {
                    alert('Successfully added the new tag. Please find your new tag in the form above.');
                    // update answer type list
                    questionnaireAPIservice.getTag().success(function (response) {
                        $scope.tagList = response;
                    });

                },
                error: function (){
                    alert("Something went wrong.");
                }
            });
        } else {
            // do nothing
            console.log("Cancel creating new tage.")
        }
    
    };

    // Function to return boolean for form completion
    $scope.checkForm = function () {
        if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100)
            return true;
        else
            return false;
    };

    // submit 
    $scope.submitQuestionnaire = function () {
        if ($scope.checkForm()) {
            // Add tags
            addTags($scope.tagList);

            // Submit 
            $.ajax({
                type: "POST",
                url: "php/questionnaire/addQuestionnaire.php",
                data: $scope.newQuestionnaire,
                success: function () {
                    $state.go('questionnaire-manage');
                }
            });
        }
    };

    var fixmeTop = $('.summary-fix').offset().top;
        $(window).scroll(function() {
            var currentScroll = $(window).scrollTop();
            if (currentScroll >= fixmeTop) {
                $('.summary-fix').css({
                    position: 'fixed',
                    top: '0',
                    width: '15%'
                });
            } else {
                $('.summary-fix').css({
                    position: 'static',
                    width: ''
                });
            }
        });

});
