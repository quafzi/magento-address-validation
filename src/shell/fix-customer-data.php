<?php
/**
 * @package    Quafzi_AddressValidation
 * @copyright  Copyright (c) 2016 Thomas Birke
 * @author     Thomas Birke <magento@netextreme.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (file_exists(dirname(__FILE__) . '/abstract.php')) {
    // if this file is no symlink
    require_once dirname(__FILE__) . '/abstract.php';
} elseif (file_exists(dirname(__FILE__) . '/../../../../shell/abstract.php')) {
    // if this file is a symlinked pointing to ../composer/quafzi/magento-address-validation/src/shell/fix-customer-data.php
    require_once dirname(__FILE__) . '/../../../../shell/abstract.php';
} elseif (file_exists(dirname(__FILE__) . '/../../../shell/abstract.php')) {
    // if this file is a symlinked pointing to ../.modman/…/src/shell/fix-customer-data.php
    require_once dirname(__FILE__) . '/../../../shell/abstract.php';
}

/**
 * Magento Shell Script to shorten Customer Data for DHL Intraship Compatibility
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Thomas Birke <magento@netextreme.de>
 */
class Mage_Shell_ShortenCustomerData extends Mage_Shell_Abstract
{
    protected $_invalidAddresses = [];
    protected $_totalErrorsCount = 0;
    protected $_totalAddressCount;
    protected $_checkedAddressCount = 0;
    protected $_conditions = [];
    protected $_helper;

    protected $_addressFields = [
        'firstname',
        'lastname',
        'company',
        'street',
        'postcode',
        'city',
        'telephone'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper('quafzi_addressvalidation');
        $this->_conditions = $this->_helper->getConditions();
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        $this->_checkCustomerAddresses();
        //$this->_checkQuoteAddresses();
        echo "\ndone." . PHP_EOL;
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f shorten-customer-data.php

USAGE;
    }

    protected function _getCollectionItems(Varien_Data_Collection $collection)
    {
        $ids = $collection->getAllIds();

        foreach ($ids as $id) {
            yield $collection->getItemById($id);
        }
    }

    protected function _checkCustomerAddresses()
    {
        $collection = Mage::getModel('customer/address')->getCollection();
        foreach ($this->_addressFields as $field) {
            $collection->addAttributeToSelect($field);
        }
        $this->_totalAddressCount = $collection->count();
        foreach ($this->_getCollectionItems($collection) as $address) {
            try {
                $this->_helper->check($address);
            } catch (Quafzi_AddressValidation_Model_Exception $e) {
                $this->_invalidAddresses[$address->getId()] = $e->getFields();
                $this->_totalErrorsCount += count($e->getFields());
            }
            ++$this->_checkedAddressCount;
            echo "\r(" . $this->_checkedAddressCount . ' / ' . $this->_totalAddressCount . ') ';
            echo count($this->_invalidAddresses) . ' addresses contained ' . $this->_totalErrorsCount . ' errors in total.';
        }
        $fieldErrors = [];
        foreach ($this->_invalidAddresses as $addressId => $errors) {
            echo '- ' . $addressId . PHP_EOL;
            foreach ($errors as $field => $value) {
                if (!isset($fieldErrors[$field])) {
                    $fieldErrors[$field] = 0;
                }
                ++$fieldErrors[$field];
                echo "  - $field: $value" . PHP_EOL;
            }
        }
        echo PHP_EOL . count($this->_invalidAddresses) . ' addresses contained ' . $this->_totalErrorsCount . ' errors in total.' . PHP_EOL . PHP_EOL;
        foreach ($fieldErrors as $field => $count) {
            echo "* {$count}x $field" . PHP_EOL;
        }
    }
}

ini_set('display_errors', 1);
$shell = new Mage_Shell_ShortenCustomerData();
$shell->run();

