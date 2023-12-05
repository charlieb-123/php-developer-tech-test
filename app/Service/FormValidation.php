<?php

namespace App\Service;

class FormValidation
{

    private $settings;
    private $db;

    public function __construct($settings, $db)
    {
        $this->settings = $settings;
        $this->db = $db;
    }

    public function validateBedrooms($bedrooms)
    {
        if (filter_var((int)$bedrooms, FILTER_VALIDATE_INT, ["options" => $this->settings['bedroomRange']])) {
            return (int)$bedrooms;
        }

        return false;
    }

    public function extractPostcodePrefix($postcode)
    {
        $prefix = false;
        if (preg_match('/^([A-Z]+)/', $postcode, $matches)) {
            $prefix = $matches[1];
        }

        //If we don't have any matches in the area at all we can say we don't cover that area rather than just showing zero results.
        $stmt = $this->db->query("SELECT distinct postcodes FROM company_matching_settings");
        $areasCovered = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if (in_array("[" . json_encode($prefix) . "]", array_values($areasCovered))) {
            return $prefix;
        }

        return false;
    }

    public function validateSurveyType($surveyType)
    {
        if (in_array($surveyType, $this->settings['surveyTypes'])) {
            return $surveyType;
        }

        return false;
    }
}
