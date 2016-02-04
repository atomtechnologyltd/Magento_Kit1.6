<?php
class Atom_Paynetz_Model_Source_Language
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'EN', 'label' => Mage::helper('paynetz')->__('English')),
            /*array('value' => 'RU', 'label' => Mage::helper('hdfc')->__('Russian')),
            array('value' => 'NL', 'label' => Mage::helper('hdfc')->__('Dutch')),
            array('value' => 'DE', 'label' => Mage::helper('hdfc')->__('German')),*/
        );
    }
}



