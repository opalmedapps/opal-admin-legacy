<?php

// SPDX-FileCopyrightText: Copyright (C) 2024 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once "database.inc";
include_once("../../php/config.php");
include_once("../../php/classes/NewOpalApiCall.php");


class PublisherPatient {
    /**
     * Get patient caregiver devices information (patient's caregivers including self-caregiver)
     *
	 * @param $patientSerNum          - patient serial number for whom the device identifiers are fetched
	 * @param array $ignoredUsernames - an optional list of usernames that should be ignored when device IDs are fetched
	 * @return array                  - a dictionary with caregivers' device info where the key is PatientDeviceIdentifierSerNum
         *                                  and the value contains caregiver-related details.
	 */
    public static function getCaregiverDeviceIdentifiers(
        $patientSerNum,
        $ignoredUsernames = [],
    ) {
        $backendApi = new NewOpalApiCall(
			'/api/patients/legacy/'.$patientSerNum.'/caregiver-devices/',
			'GET',
			'en',
			[],
		);
		$response = $backendApi->execute();
		$response = $response ? json_decode($response, true) : NULL;
		$caregivers = $response && $response['caregivers'] ? $response['caregivers'] : [];
		$userNameArray = [];
		$userLanguageArray = [];
		$identifiers = [];

		foreach ($caregivers as $caregiver) {
			// Check if fetched username exists in an $ignoredUsernames
			// If the username is in the list, skip it
			if (!in_array($caregiver['username'], $ignoredUsernames))
				$userNameArray[] = $caregiver['username'];
				$userLanguageArray[$caregiver['username']] = $caregiver['language'];
		}

		$userNameArrayString = implode(",", $userNameArray);
		$patientDevices = self::getPatientDeviceIdentifiers($userNameArrayString);
		foreach ($patientDevices as $ptdId) {
			$device_id = $ptdId['PatientDeviceIdentifierSerNum'];
			$identifiers[$device_id] = [];
			$identifiers[$device_id]['registration_id'] =  $ptdId['RegistrationId'];
			$identifiers[$device_id]['device_type'] = $ptdId['DeviceType'];
			$language = $userLanguageArray[$ptdId['Username']];
			$identifiers[$device_id]['language'] = $language;
			$identifiers[$device_id]['institution_acronym'] = $response['institution']['acronym_'.$language];
		}
		return $identifiers;
    }

    /**
     * Get patient devices information by user names
     * @param $userNamesStr string - usernames separated by comma
     * @return array - caregiver devices info
     */
    protected static function getPatientDeviceIdentifiers($userNamesStr) {
        global $pdo;

        try {
			$stmt = $pdo->prepare(OPAL_GET_PATIENT_DEVICE_IDENTIFIERS);
			$stmt->execute(
                ['userNamesStr' => $userNamesStr],
            );
			return $stmt->fetchAll();
		} catch(PDOException $e) {
			return array();
		}
    }
}
