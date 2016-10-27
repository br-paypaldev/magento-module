<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Instant Payment Notification processor model
 */
class Esmart_PayPalBrasil_Model_Ipn extends Mage_Paypal_Model_Ipn
{


    /**
     * Load and validate order, instantiate proper configuration
     *
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            // get proper order

            if ($this->_request['txn_type'] == 'invoice_payment') {
                 $id = $this->_request['invoice_number'];
            }else{
                 $id = $this->_request['invoice'];
            }
           
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($id);
            if (!$this->_order->getId()) {
                $this->_debugData['exception'] = sprintf('Wrong order ID: "%s".', $id);
                $this->_debug();
                Mage::app()->getResponse()
                    ->setHeader('HTTP/1.1','503 Service Unavailable')
                    ->sendResponse();
                exit;
            }
            // re-initialize config with the method code and store id
            $methodCode = $this->_order->getPayment()->getMethod();
            $this->_config = Mage::getModel('paypal/config', array($methodCode, $this->_order->getStoreId()));
            if (!$this->_config->isMethodActive($methodCode) || !$this->_config->isMethodAvailable()) {
                throw new Exception(sprintf('Method "%s" is not available.', $methodCode));
            }

            $this->_verifyOrder();
        }
        return $this->_order;
    }
}
