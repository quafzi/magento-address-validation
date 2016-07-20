<?php
/**
 * @package    Quafzi_AddressValidation
 * @copyright  Copyright (c) 2016 Thomas Birke
 * @author     Thomas Birke <magento@netextreme.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Quafzi_AddressValidation_Model_Exception extends Mage_Core_Exception
{
    protected $fields = [];

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }
}
