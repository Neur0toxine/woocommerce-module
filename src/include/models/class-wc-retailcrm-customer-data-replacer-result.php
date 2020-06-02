<?php

/**
 * Class WC_Retailcrm_Customer_Data_Replacer_Result
 * Holds modified order and customer which was set in the order.
 * If customer is null, then only order props was updated. Previous customer (if it was registered)
 * will be detached from this order.
 */
class WC_Retailcrm_Customer_Data_Replacer_Result
{
    /** @var \WC_Customer|null */
    private $wcCustomer;

    /** @var \WC_Order $wcOrder */
    private $wcOrder;

    /**
     * WC_Retailcrm_Customer_Data_Replacer_Result constructor.
     *
     * @param \WC_Customer|null $wcCustomer
     * @param \WC_Order         $wcOrder
     */
    public function __construct($wcCustomer, $wcOrder)
    {
        $this->wcCustomer = $wcCustomer;
        $this->wcOrder = $wcOrder;

        if ((!is_null($this->wcCustomer) && !($this->wcCustomer instanceof WC_Customer))
            || !($this->wcOrder instanceof WC_Order)
        ) {
            throw new \InvalidArgumentException(sprintf('Incorrect data provided to %s', __CLASS__));
        }
    }

    /**
     * @return \WC_Customer|null
     */
    public function getWcCustomer()
    {
        return $this->wcCustomer;
    }

    /**
     * @return \WC_Order
     */
    public function getWcOrder()
    {
        return $this->wcOrder;
    }
}
