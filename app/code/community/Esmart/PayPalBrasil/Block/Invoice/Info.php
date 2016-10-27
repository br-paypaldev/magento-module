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
 * PayPal common payment info block
 * Uses default templates
 */
class Esmart_PayPalBrasil_Block_Invoice_Info extends Mage_Payment_Block_Info_Cc
{

    /**
     * Don't show CC type for non-CC methods
     *
     * @return string|null
     */
    public function getCcTypeName()
    {
        if (Mage_Paypal_Model_Config::getIsCreditCardMethod($this->getInfo()->getMethod())) {
            return parent::getCcTypeName();
        }
    }

    /**
     * Prepare PayPal-specific payment information
     *
     * @param Varien_Object|array $transport
     * return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $order =  $payment->getOrder();

        $label = Mage::helper('paypal')->__('Customer Email');
        $info[$label] = $order->getCustomerEmail();     

         // add last_trans_id
        $label = Mage::helper('paypal')->__('Transaction ID');
        $value = $payment->getLastTransId();
        if ($value) {
            $info[$label] = $value;
        } else {
            $info[$label] = Mage::helper('paypal')->__('Invoice not Paid yet.');//'(Não há ainda um ID pois o Invoice ainda não foi pago)';
            // add invoice Id
            $label = Mage::helper('paypal')->__('Invoice ID');
            $info[$label] = $payment->getAdditionalInformation('paypal_invoice_id');
            // add payer link url
            $payerViewUrl = $payment->getAdditionalInformation('paypal_invoice_payerviewurl');
            if ($payerViewUrl) {
                $label = Mage::helper('paypal')->__('Invoice Payer View Url');
                $info[$label] = $payment->getAdditionalInformation('paypal_invoice_payerviewurl');
            }
            
            
        }
        
        return $transport->addData($info);
    }
}