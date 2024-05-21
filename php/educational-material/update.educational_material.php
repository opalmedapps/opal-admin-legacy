<?php
	header('Content-Type: application/javascript');
  /* To update an edu material for any changes */
  include_once('educational-material.inc');

  $eduMat = new EduMaterial; // Object

  // Construct array from FORM params
  $eduMatArray = array(
  'name_EN'           => $_POST['name_EN'],
  'name_FR'           => $_POST['name_FR'],
  'url_EN'            => $_POST['url_EN'],
  'url_FR'            => $_POST['url_FR'],
  'share_url_EN'      => $_POST['share_url_EN'],
  'share_url_FR'      => $_POST['share_url_FR'],
  'type_EN'           => $_POST['type_EN'],
  'type_FR'           => $_POST['type_FR'],
  'phase_serial'      => $_POST['phase_serial'],
  'triggers'          => $_POST['triggers'],
  'tocs'              => $_POST['tocs'],
  'serial'            => $_POST['serial'],
  'user'              => $_POST['user'],
  'content_types'     => $_POST['content_types'],
  'details_updated'   => $_POST['details_updated'],
  'triggers_updated'  => $_POST['triggers_updated'],
  'tocs_updated'      => $_POST['tocs_updated'],
  'purpose_ID'        => $_POST['purpose_ID']
  );

  // Call function
  $response = $eduMat->updateEducationalMaterial($eduMatArray);
  print json_encode($response); // Return response

?>
