<?php

/**
 * Class WC_Retailcrm_Customer_Data_Replacer_State
 * Holds WC_Retailcrm_Customer_Data_Replacer state. It exists only because we need to comply with builder interface.
 */
class WC_Retailcrm_Customer_Data_Replacer_State
{
    /** @var \WC_Order $wcOrder */
   private $wcOrder;

   /** @var array */
   private $newCustomer;

   /** @var array */
   private $newContact;

   /** @var array */
   private $newCorporateCustomer;

   /** @var array $newCompany */
    private $newCompany;

    /**
     * @return \WC_Order
     */
    public function getWcOrder()
    {
        return $this->wcOrder;
    }

    /**
     * @param \WC_Order $wcOrder
     *
     * @return WC_Retailcrm_Customer_Data_Replacer_State
     */
    public function setWcOrder($wcOrder)
    {
        $this->wcOrder = $wcOrder;
        return $this;
    }

    /**
     * @return array
     */
    public function getNewCustomer()
    {
        return $this->newCustomer;
    }

    /**
     * @param array $newCustomer
     *
     * @return WC_Retailcrm_Customer_Data_Replacer_State
     */
    public function setNewCustomer($newCustomer)
    {
        $this->newCustomer = $newCustomer;
        return $this;
    }

    /**
     * @return array
     */
    public function getNewContact()
    {
        return $this->newContact;
    }

    /**
     * @param array $newContact
     *
     * @return WC_Retailcrm_Customer_Data_Replacer_State
     */
    public function setNewContact($newContact)
    {
        $this->newContact = $newContact;
        return $this;
    }

    /**
     * @return array
     */
    public function getNewCorporateCustomer()
    {
        return $this->newCorporateCustomer;
    }

    /**
     * @param array $newCorporateCustomer
     *
     * @return WC_Retailcrm_Customer_Data_Replacer_State
     */
    public function setNewCorporateCustomer($newCorporateCustomer)
    {
        $this->newCorporateCustomer = $newCorporateCustomer;
        return $this;
    }

    /**
     * @return array
     */
    public function getNewCompany()
    {
        return $this->newCompany;
    }

    /**
     * @param array $newCompany
     *
     * @return WC_Retailcrm_Customer_Data_Replacer_State
     */
    public function setNewCompany($newCompany)
    {
        $this->newCompany = $newCompany;
        return $this;
    }

    /**
     * Throws an exception if state is not valid
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    public function validate()
    {
        if (empty($this->getWcOrder())) {
            throw new \InvalidArgumentException('Empty WC_Order.');
        }

        if (empty($this->getNewCustomer())
            && empty($this->getNewContact())
            && empty($this->getNewCorporateCustomer())
        ) {
            throw new \InvalidArgumentException('New customer, new contact and new corporate customer is empty.');
        }

        if (!empty($this->getNewCustomer())
            && (!empty($this->getNewContact()) || !empty($this->getNewCorporateCustomer()))
        ) {
            throw new \InvalidArgumentException(
                'Too much data in state - cannot determine which customer should be used.'
            );
        }
    }
}