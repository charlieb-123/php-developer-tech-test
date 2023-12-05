<?php

namespace App\Controller;

use App\Service\CompanyMatcher;
use App\Service\FormValidation;

class FormController extends Controller
{
    
    public function index()
    {
        echo $this->render('form.twig');
    }

    public function submit()
    {
        $validator = new FormValidation($this->settings());
        $bedrooms = $validator->validateBedrooms($_POST['bedrooms']);
        $surveyType = $validator->validateSurveyType($_POST['type']);
        $postcodePrefix = $validator->extractPostcodePrefix($_POST['postcode']);

        if (!$bedrooms || !$surveyType || !$postcodePrefix) {
            echo $this->render('form.twig', [
                'error' => true,
                'inArea' => $postcodePrefix
            ]);
            return;
        }

        $matcher = new CompanyMatcher($this->db(), $this->settings(), $this->logger());

        $matchedCompanies = $matcher->match($bedrooms, $postcodePrefix, $surveyType)->results();

        $this->render('results.twig', [
            'matchedCompanies'  => $matchedCompanies,
            'matchedCompanyCount' => count($matchedCompanies)
        ]);
    }
}
