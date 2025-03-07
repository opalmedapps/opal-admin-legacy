<?php

// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");
$data = json_decode(file_get_contents('php://input'), true);
$appointment = new Appointment(); // Object
$response = $appointment->getAppointment($data);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>