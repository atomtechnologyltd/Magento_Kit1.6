<?php

class Atom_Paynetz_Model_Config extends Varien_Object
{
    /**
     *  Return config var
     *
     *  @param    string Var key
     *  @param    string Default value for non-existing key
     *  @return	  mixed
     */
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
            $value = Mage::getStoreConfig('payment/paynetz_standard/'.$key);
            if (is_null($value) || false===$value) {
                $value = $default;
            }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

    /**
     *  Return Transaction Mode registered in Secure Admnin Panel
     *
     *  @param    none
     *  @return	  string Transaction Mode
     */
    public function getTransactionMode ()
    {
        return $this->getConfigData('mode');
    }

    /**
     *  Return Secret Key registered in Secure  Admnin Panel
     *
     *  @param    none
     *  @return	  string Secret Key
     */
    public function getPassword ()
    {
        return $this->getConfigData('password');
    }

 /**
     *  Return Account ID (general type payments) registered in Secure Admnin Panel
     *
     *  @param    none
     *  @return	  string Account ID
     */
    public function getAccountId ()
    {
        return $this->getConfigData('account_id');
    }
    /**
     *  Return Store description sent to Secure
     *
     *  @return	  string Description
     */
    public function getDescription ()
    {
        $description = $this->getConfigData('description');
        return $description;
    }

    /**
     *  Return new order status
     *
     *  @return	  string New order status
     */
    public function getNewOrderStatus ()
    {
        return $this->getConfigData('order_status');
    }
    /**
     *  Return accepted currency
     *
     *  @param    none
     *  @return	  string Currenc
     */
    public function getCurrency ()
    {
        return $this->getConfigData('currencycode');
    }

	public function getAtomLogin()
    {
        return $this->getConfigData('Atompg_login_id');
    }

	public function getAtomPassword()
    {
        return $this->getConfigData('Atompg_password');
    }

	public function getAtomProductId()
    {
        return $this->getConfigData('Atompg_prodId');
    }


}