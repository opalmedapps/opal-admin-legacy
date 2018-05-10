<?php

	/* To insert a newly created test result */
    include_once('test-result.inc');

	// Construct array from FORM params
    $testResultArray	= array(
        'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
        'group_EN'          => $_POST['group_EN'],
        'group_FR'          => $_POST['group_FR'],
        'edumat'            => $_POST['eduMat'],
        'tests'             => $_POST['tests'],
        'additional_links'  => $_POST['additional_links'],
        'user'              => $_POST['user']
    );

    $testResult = new TestResult; // Object

    // Call function
    print $testResult->insertTestResult($testResultArray);

?>

