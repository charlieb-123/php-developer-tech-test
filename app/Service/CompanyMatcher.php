<?php

namespace App\Service;

use Monolog\Logger;

class CompanyMatcher
{
    private $db;
    private $matches = [];
    private $settings = [];
    private $logger;

    const CREDIT_COST = 1;

    public function __construct(\PDO $db, $settings, Logger $logger)
    {
        $this->db = $db;
        $this->settings = $settings;
        $this->logger = $logger;
    }


    /**
     * Perform a matching operation based on specified criteria.
     *
     * @param mixed $bedrooms The bedrooms criteria for matching.
     * @param mixed $postcodePrefix The postcode prefix criteria for matching.
     * @param string $surveyType The survey type criteria for matching.
     *
     * @return $this
     */
    public function match($bedrooms, $postcodePrefix, $surveyType)
    {
        try {
            // Construct the SQL query for matching companies based on criteria
            $sql = "SELECT DISTINCT company_id, `name`, `description`, phone, email, website, credits 
                    FROM company_matching_settings
                        JOIN companies on company_id = companies.id
                    WHERE JSON_contains(bedrooms, :bedrooms) 
                        AND JSON_CONTAINS(postcodes, :postcodePrefix)
                        AND `type` = :surveyType
                        AND active = 1
                        AND credits > 0
                    ORDER BY RAND() LIMIT " . $this->settings['resultLimit'];

            $stmt = $this->db->prepare($sql);

            // Execute the prepared statement with parameter bindings
            $stmt->execute([
                'surveyType' => $surveyType,
                'postcodePrefix' => '"' . $postcodePrefix . '"',
                'bedrooms' => '"' . $bedrooms . '"'
            ]);

            // Fetch the matching results as an associative array
            $this->matches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            //Deduct Credits for matched companies
            $this->deductCredits();

            //Log zero credit companies
            $this->logZeroCreditCompanies();

            return $this;
        } catch (\PDOException $e) {
            $this->logger->addError('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Return the results for display
     *
     * @return array
     */
    public function results(): array
    {
        return $this->matches;
    }

    /**
     * Deduct credits from companies based on the matching results.
     *
     * @return void
     */
    public function deductCredits()
    {
        try {
            // Get the company IDs from the matching results
            $companyIds = array_column($this->matches, 'company_id');

            // Create placeholders for each company ID in the SQL query
            $placeholders = implode(',', array_fill(0, count($companyIds), '?'));

            // Construct the SQL query to update company credits
            $sql = "UPDATE companies SET credits = (credits - " . self::CREDIT_COST . ") WHERE id IN ({$placeholders})";
            $stmt = $this->db->prepare($sql);

            foreach ($companyIds as $index => $companyId) {
                $stmt->bindValue($index + 1, $companyId, \PDO::PARAM_INT);
            }

            $stmt->execute();
        } catch (\PDOException $e) {
            $this->logger->addError('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Log companies with zero credits.
     *
     * @return void
     */
    public function logZeroCreditCompanies()
    {
        // Filter companies that have credits equal to the defined cost
        $filteredCompanies = array_filter($this->matches, function ($company) {
            return (int)$company["credits"] === self::CREDIT_COST;
        });

        if ($filteredCompanies) {
            // Extract names of companies
            $namesWithZeroCredits = array_column($filteredCompanies, "name");

            // Log each company with zero credits
            foreach ($namesWithZeroCredits as $name) {
                $this->logger->addInfo($name . " has run out of credit");
            }
        }
    }
}
