<?php

namespace App\Service;

class FormValidation
{

    private $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
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

        if (in_array($prefix, $this->settings['areasCovered'])) {
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
