<?php 

	/* To update a test result for any changes */
    include_once('test-result.inc');

    $testResult = new TestResult; // Object

    // Construct array from FORM params
    $testResultArray = array(
        'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
        'group_EN'          => $_POST['group_EN'],
        'group_FR'          => $_POST['group_FR'],
        'edumatser'         => $_POST['eduMatSer'],
        'serial'            => $_POST['serial'],
        'tests'             => $_POST['tests'],
        'additional_links'  => $_POST['additional_links'],
        'user'              => $_POST['user'],
        'details_updated'   => $_POST['details_updated'],
        'test_names_updated'    => $_POST['test_names_updated'],
        'additional_links_updated'  => $_POST['additional_links_updated']
    );

    // Call function
    $response = $testResult->updateTestResult($testResultArray);
    print json_encode($response); // Return response
?>

