<?php

/**
 * Filter class
 */
class Filter extends OpalProject {

    /*
     * Returns all the filters/triggers for publications
     * @params  void
     * @return  $results (array) filter/triggers found
     * */
    function getFilters() {
        $results = array();

        $results["patients"] = $this->opalDB->getPatientsTriggers();
        $results["dx"] = $this->opalDB->getDiagnosisTriggers();
        $results["appointments"] = $this->opalDB->getAppointmentsTriggers();
        $results["appointmentStatuses"] = $this->opalDB->getAppointmentsStatusTriggers();
        $results["doctors"] = $this->opalDB->getDoctorsTriggers();
        $results["machines"] = $this->opalDB->getTreatmentMachinesTriggers();

        foreach($results["doctors"] as &$doctor) {
            $doctor["name"] = ucwords(strtolower($doctor["LastName"] . ", " . preg_replace("/^[Dd][Rr]([.]?[ ]?){1}/", "", $doctor["FirstName"]) . " " . " (" . $doctor["id"] . ")"));
            unset($doctor["FirstName"]);
            unset($doctor["LastName"]);
        }
        return $results;
    }
}