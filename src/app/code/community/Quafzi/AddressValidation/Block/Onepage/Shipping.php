<?php
/**
 * @package    Quafzi_AddressValidation
 * @copyright  Copyright (c) 2016 Thomas Birke
 * @author     Thomas Birke <magento@netextreme.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Quafzi_AddressValidation_Block_Onepage_Shipping
    extends Kkoepke_CheckoutAddressFix_Block_Onepage_Shipping
{

    protected function getValidAddresses()
    {
        $helper = Mage::helper('quafzi_addressvalidation');
        return array_filter($this->getCustomer()->getAddresses(), function ($address) use ($helper) {
            try {
                $helper->check($address);
                return true;
            } catch (Quafzi_AddressValidation_Model_Exception $e) {
                return false;
            }
        });
    }

    public function customerHasAddresses()
    {
        return count($this->getValidAddresses());
    }

    /**
     * type is "shipping" in this case, but we need this variable for compatibility reasons in strict mode
     */
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            $validAddressIds = [];
            foreach ($this->getValidAddresses() as $address) {
                $options[$address->getId()] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                $address = $this->getCustomer()->getPrimaryShippingAddress();
                if ($address && isset($options[$address->getId()])) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName('shipping_address_id')
                ->setId('shipping-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="shipping.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

}
