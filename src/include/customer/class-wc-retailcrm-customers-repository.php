<?php
/**
 * RetailCRM Integration.
 *
 * @package  WC_Retailcrm_Customers_Repository
 * @category Integration
 * @author   RetailCRM
 */

if (!class_exists('WC_Retailcrm_Customers_Repository')) :

    /**
     * Class WC_Retailcrm_Customers_Repository
     */
    class WC_Retailcrm_Customers_Repository
    {
        /** @var string */
        const CUSTOMER_ROLE = 'customer';

        /** @var string */
        const CUSTOMER_ID_PROP = '_customer_user';

        static $_allowedProps;

        /**
         * Returns customers which are attached to users
         *
         * @param array  $ids Customers ID's to include
         * @param string $filterRole Customers without this role will be skipped.
         *
         * @return WC_Retailcrm_Customers_Repository_Result
         */
        public static function getUsersCustomers($ids = array(), $filterRole = '')
        {
            $customers = array();
            $notFound = array();
            $params = array();

            if (!empty($ids)) {
                $params['include'] = $ids;
            }

            $users = get_users($params);

            foreach ($users as $user) {
                if (!empty($filterRole)) {
                    if (!\in_array($filterRole, $user->roles)) {
                        continue;
                    }
                }

                try {
                    $customers[] = new WC_Customer($user->ID);
                } catch (\Exception $exception) {
                    $notFound[$user->ID] = $exception;
                }
            }

            return new WC_Retailcrm_Customers_Repository_Result($customers, $notFound);
        }

	    /**
	     * Returns list of WC_Customers assembled from order properties.
	     *
	     * @return \WC_Customer[]
	     */
	    public static function getOrdersCustomers()
	    {
	        return self::assemblyCustomersDataFromOrderProperties(self::getOrdersProperties());
	    }

	    /**
	     * Get orders properties
	     *
	     * @return array
	     */
	    private static function getOrdersProperties()
	    {
		    return self::filterAndSquashOrderProperties(self::retrieveOrdersProperties());
        }

	    /**
	     * Retrieves orders with properties
	     *
	     * @return array|object|null
	     */
        private static function retrieveOrdersProperties()
        {
	        global $wpdb;

	        $allowedMeta = join(', ', array_map(
	        	function ($val) {
	        		return '\'' . ((string) esc_sql($val)) . '\'';
		        },
		        self::allowedOrderMetaList()
	        ));

	        $result = $wpdb->get_results(
		        "SELECT p.ID, pm.meta_key, pm.meta_value " .
		        "FROM {$wpdb->posts} AS p " .
		        "INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id) " .
		        "WHERE p.post_type = 'shop_order' " .
		        "AND pm.meta_key IN ($allowedMeta)"
	        );

	        if (!empty($wpdb->last_error)) {
	        	throw new \RuntimeException($wpdb->last_error);
	        }

	        return $result;
        }

	    /**
	     * Squash order properties by order ID, skip orders with registered customer.
	     *
	     * @param array $properties
	     *
	     * @return array
	     */
	    private static function filterAndSquashOrderProperties($properties)
	    {
	    	$orderProperties = array();

	    	foreach ($properties as $prop) {
	    		if (array_key_exists($prop->ID, $orderProperties) && $orderProperties[$prop->ID] === false) {
	    			continue;
			    }

	    		if ($prop->meta_key == self::CUSTOMER_ID_PROP && !empty($prop->meta_value)) {
				    $orderProperties[$prop->ID] = false;
				    continue;
			    }

	    		$orderProperties[$prop->ID][$prop->meta_key] = $prop->meta_value;
		    }

	    	return array_filter($orderProperties);
        }

	    /**
	     * Assembly WC_Customer list from orders customers data
	     *
	     * @param array $orderPropList
	     *
	     * @return \WC_Customer[]
	     */
	    private static function assemblyCustomersDataFromOrderProperties($orderPropList)
	    {
	    	$customers = array();

	    	foreach ($orderPropList as $props) {
	    		$email = isset($props['_billing_email']) && !empty($props['_billing_email'])
				    ? $props['_billing_email'] : null;

	    		if (!empty($email) && array_key_exists($email, $customers)) {
	    			continue;
			    }

	    		$customer = self::hydrateCustomer($props);

	    		if (!empty($email)) {
	    			$customers[$email] = $customer;
			    } else {
	    			$customers[] = $customer;
			    }
		    }

	    	return $customers;
        }

	    /**
	     * Create new WC_Customer and fill it with customer data from order
	     *
	     * @param array $customerData
	     *
	     * @return \WC_Customer
	     */
	    private static function hydrateCustomer($customerData)
	    {
		    $customer = new WC_Customer();

		    foreach (self::allowedOrderMetaList() as $prop) {
		    	$setter = sprintf('set%s', $prop);

		    	if (array_key_exists($prop, $customerData) && method_exists($customer, $setter)) {
		    		call_user_func(array($customer, $setter), self::arrayValue($customerData, $prop));
			    }
		    }

		    return $customer;
        }

	    /**
	     * Return value from array or default value
	     *
	     * @param array  $arr
	     * @param string $key
	     * @param string $default
	     *
	     * @return mixed
	     */
	    private static function arrayValue($arr, $key, $default = '')
	    {
		    if (!empty($arr[$key])) {
		    	return $arr[$key];
		    }

		    return $default;
        }

	    /**
	     * Returns list of meta keys which will be used to filter meta for orders
	     *
	     * @return string[]
	     */
	    private static function allowedOrderMetaList()
	    {
	    	if (empty(static::$_allowedProps)) {
	    		self::$_allowedProps = array_merge(
	    			array(self::CUSTOMER_ID_PROP),
				    array_filter(array_map(
					    function ($val) {
						    if (is_string($val) && strpos($val, 'set_') !== false) {
							    return substr($val, 3);
						    }

						    return null;
					    },
					    get_class_methods(get_class(new WC_Customer()))
				    ))
			    );
		    }

	    	return self::$_allowedProps;
        }
    }
endif;
