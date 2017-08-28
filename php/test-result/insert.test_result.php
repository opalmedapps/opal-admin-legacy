<?php

	/* To insert a newly created test result */
    include_once('test-result.inc');

	// Construct array from FORM params
    $testResultArray	= array(
        'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => $_POST['description_EN'],
        'description_FR'    => $_POST['description_FR'],
        'group_EN'          => $_POST['group_EN'],
        'group_FR'          => $_POST['group_FR'],
        'tests'             => $_POST['tests']
    );

    $testResult = new TestResult; // Object

    // Call function
    print $testResult->insertTestResult($testResultArray);

?>

