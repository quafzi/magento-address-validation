<?php
/**
 * @package    Quafzi_AddressValidation
 * @copyright  Copyright (c) 2016 Thomas Birke
 * @author     Thomas Birke <magento@netextreme.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Quafzi_AddressValidation_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * get validation rules
     *
     * @return array
     */
    public function getConditions()
    {
        return [
            'firstname' => Mage::getStoreConfig('customer/address/regex_firstname'),
            'lastname' => Mage::getStoreConfig('customer/address/regex_lastname'),
            'company' => Mage::getStoreConfig('customer/address/regex_company'),
            'street' => function ($address) { return $this->_checkStreet($address); },
            'street1' => Mage::getStoreConfig('customer/address/regex_street1'), // street name
            'street2' => Mage::getStoreConfig('customer/address/regex_street2'), // house number
            'street3' => Mage::getStoreConfig('customer/address/regex_street3'), // care of
            'street4' => Mage::getStoreConfig('customer/address/regex_street4'),
            'postcode' => Mage::getStoreConfig('customer/address/regex_postcode'),
            'city' => Mage::getStoreConfig('customer/address/regex_city'),
            'telephone' => function ($address) { return $this->_checkTelephone($address); },
        ];
    }

    /**
     * get fields to be validated
     *
     * @return array
     */
    public function getAddressFields()
    {
        return [
            'firstname',
            'lastname',
            'company',
            'street',
            'postcode',
            'city',
            'telephone'
        ];
    }

    public function check(Mage_Customer_Model_Address_Abstract $address)
    {
        $errorFields = [];
        foreach ($this->getAddressFields() as $field) {
            if (!$this->_checkField($address, $field)) {
                $errorFields[$field] = $address->getData($field);
            }
        }
        if (count($errorFields)) {
            $e = new Quafzi_AddressValidation_Model_Exception();
            $e->setFields($errorFields);
            throw $e;
        }
    }

    protected function _checkField(Mage_Customer_Model_Address_Abstract $address, $attribute, $autofix=true)
    {
        $condition = $this->getConditions()[$attribute];
        $value = $address->{'get' . ucfirst($attribute)}();
        $result = (is_string($condition)) ? preg_match($condition, $value) : $condition($address);
        $trimmed = trim($value);
        if ($autofix && !$result && is_string($value) && strlen($trimmed) && $trimmed !== $value) {
            $address->{'set' . ucfirst($attribute)}($trimmed);
            if ($this->_checkField($address, $attribute)) {
                $this->_log('autofix ' . $attribute . ' by trimming its value of address ' . $address->getId());
                $address->getResource()->saveAttribute($address, $attribute);
                return true;
            }
        }
        return $result;
    }

    protected function _checkStreet(Mage_Customer_Model_Address_Abstract $address)
    {
        if ($this->_checkField($address, 'street1', false)
            && $this->_checkField($address, 'street2', false)
            && $this->_checkField($address, 'street3', false)
            && $this->_checkField($address, 'street4', false)
        ) {
            return true;
        }
        $before = json_encode($address->getStreet());
        $parts = Mage::helper('intraship')->splitStreet($address->getStreet(1));
        $address->setStreet([
            $parts['street_name'],
            $parts['street_number'],
            trim($parts['care_of'] . ' ' . $address->getStreet(2))
        ]);
        if ($this->_checkField($address, 'street1', false)
            && $this->_checkField($address, 'street2', false)
            && $this->_checkField($address, 'street3', false)
            && $this->_checkField($address, 'street4', false)
        ) {
            $after = json_encode($address->getStreet());
            $address->getResource()->saveAttribute($address, 'street');
            $this->_log('autofix street ' . "$before\t => $after" . ' of address ' . $address->getId());
            return true;
        }
        return false;
    }

    protected function _checkTelephone(Mage_Customer_Model_Address_Abstract $address)
    {
        $regex = '/^([0-9]{1,30})?$/';
        if (!preg_match($regex, $address->getTelephone())) {
            $before = $address->getTelephone();
            $after = preg_replace('/^\+/', '00', $before);
            $after = preg_replace('/\(0\)/', '', $after);
            $after = preg_replace('/[^0-9]/', '', $after);
            if (preg_match($regex, $after)) {
                $this->_log('autofix telephone ' . "$before\t => $after" . ' of address ' . $address->getId());
                $address->setTelephone($after);
                $address->getResource()->saveAttribute($address, 'telephone');
            }
        }
        return preg_match($regex, $address->getTelephone());
    }

    protected function _log($message)
    {
        Mage::log($message, null, 'address-validation-fix.log');

        return $this;
    }

}
