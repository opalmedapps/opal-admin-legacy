<?php 

	/* To update a test result for any changes */
    include_once('test-result.inc');

    $testResult = new TestResult; // Object

    // Construct array from FORM params
    $testResultArray = array(
        'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => $_POST['description_EN'],
        'description_FR'    => $_POST['description_FR'],
        'group_EN'          => $_POST['group_EN'],
        'group_FR'          => $_POST['group_FR'],
        'edumatser'         => $_POST['eduMatSer'],
        'serial'            => $_POST['serial'],
        'tests'             => $_POST['tests'],
        'additional_links'  => $_POST['additional_links'],
        'user'              => $_POST['user']
    );

    // Call function
    $response = $testResult->updateTestResult($testResultArray);
    print json_encode($response); // Return response
?>

