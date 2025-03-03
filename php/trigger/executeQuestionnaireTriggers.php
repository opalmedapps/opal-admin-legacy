<?php

// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");
   
$trigger = new Trigger(false); //guest access set false, must use system login

// Need patientQuestionnaireSerNum from caller
$result = $trigger->executeTrigger($_POST, MODULE_QUESTIONNAIRE);

header('Content-Type: application/javascript');
echo json_encode($result);