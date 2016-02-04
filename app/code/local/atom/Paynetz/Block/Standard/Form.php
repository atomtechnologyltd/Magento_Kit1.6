<?php

class Atom_Paynetz_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynetz/standard/form.phtml');
        parent::_construct();
    }
}