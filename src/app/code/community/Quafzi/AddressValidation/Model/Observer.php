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
            $address->addError(
                Mage::getStoreConfig('customer/address/validation_error_message')
            );
            foreach ($e->getFields() as $field => $value) {
                $address->addError(
                    Mage::getStoreConfig('customer/address/error_' . $field)
                );
            }
        }
    }

    public function forceDifferingShippingAddressForInvalidBillingAddress($observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $data = $action->getRequest()->getPost('billing', array());
        $addressId = $action->getRequest()->getPost('billing_address_id', false);
        if (!$addressId) {
            return;
        }
        if (!isset($data['use_for_shipping']) || $data['use_for_shipping'] == 0) {
            return;
        }
        $address = Mage::getModel('customer/address')->load($addressId);
        if (!$address->getId()) {
            return;
        }
        try {
            Mage::helper('quafzi_addressvalidation')->check($address);
        } catch (Quafzi_AddressValidation_Model_Exception $e) {
            $response = $action->getResponse();
            $result = Mage::helper('core')->jsonDecode($response->getBody());
            $result['goto_section'] = 'shipping';
            $result['forceDifferingShippingAddress'] = true;
            $response->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
}
