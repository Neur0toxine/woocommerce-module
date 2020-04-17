<?php
/**
 * RetailCRM Integration.
 *
 * @package  WC_Retailcrm_Customers_Repository_Result
 * @category Integration
 * @author   RetailCRM
 */

if (!class_exists('WC_Retailcrm_Customers_Repository_Result')) :

    /**
     * Class WC_Retailcrm_Customers_Repository_Result
     */
    class WC_Retailcrm_Customers_Repository_Result
    {
        /** @var array $customers */
        private $customers;

        /** @var array $notFound */
        private $notFound;

        /**
         * WC_Retailcrm_Customers_Repository_Result constructor.
         *
         * @param \WC_Customer[] $customers
         * @param array<int,\Exception> $notFound
         */
        public function __construct($customers, $notFound)
        {
            $this->customers = $customers;
            $this->notFound = $notFound;
        }

        /**
         * Returns list of customers
         *
         * @return \WC_Customer[]
         */
        public function getCustomers()
        {
            return $this->customers;
        }

        /**
         * Returns list of customer load exceptions with customer ID's.
         * Format:
         *  array(customerId => Exception)
         *
         * @return \Exception[]
         */
        public function getNotFound()
        {
            return $this->notFound;
        }
    }
endif;
