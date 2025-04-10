<?php

// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$sms = new Sms();
$smsSpecialityList = $sms->getSpecialityMessage();

header('Content-Type: application/javascript');
echo json_encode($smsSpecialityList);
