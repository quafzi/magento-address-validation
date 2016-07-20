<?php
/**
 * @package    Quafzi_AddressValidation
 * @copyright  Copyright (c) 2016 Thomas Birke
 * @author     Thomas Birke <magento@netextreme.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Quafzi_AddressValidation_Model_Observer
{
    public function validateAddress($observer)
    {
        $address = $observer->getEvent()->getAddress();
        $helper = Mage::helper('quafzi_addressvalidation');
        try {
            $helper->check($address);
        } catch (Quafzi_AddressValidation_Model_Exception $e) {
            $errorFields = $e->getFields();
            if (count($errorFields)) {
                $address->addError(
                    Mage::getStoreConfig('customer/address/validation_error_message')
                );
            }
            foreach ($errorFields as $field => $value) {
                $address->addError(
                    Mage::getStoreConfig('customer/address/error_' . $field)
                );
            }
        }
    }
}

