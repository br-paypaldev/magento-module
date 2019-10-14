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
            $id = $this->_request['invoice'];
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
        }
        return $this->_order;
    }


    /**
     * Process completed payment (either full or partial)
     *
     * @param bool $skipFraudDetection
     */
    protected function _registerPaymentCapture($skipFraudDetection = false)
    {
        if ($this->getRequestData('transaction_entity') == 'auth') {
            return;
        }
        $parentTransactionId = $this->getRequestData('parent_txn_id');
        $this->_importPaymentInformation();
        $payment = $this->_order->getPayment();
        $payment->setTransactionId($this->getRequestData('txn_id'))
            ->setCurrencyCode($this->getRequestData('mc_currency'))
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setParentTransactionId($parentTransactionId)
            ->setShouldCloseParentTransaction('Completed' === $this->getRequestData('auth_status'))
            ->setIsTransactionClosed(0)
            ->registerCaptureNotification(
                $this->getRequestData('mc_gross'),
                $skipFraudDetection && $parentTransactionId
            );
        $this->_order->save();

        // notify customer
        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$this->_order->getEmailSent()) {
            $this->_order->queueNewOrderEmail()->addStatusHistoryComment(
                Mage::helper('paypal')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
            )
                ->setIsCustomerNotified(true)
                ->save();
        }

        $this->_order->queueOrderUpdateEmail(true);

    }

    /**
     * Process payment reversal and cancelled reversal notification
     */
    protected function _registerPaymentReversal()
    {
        $reasonCode = isset($this->_request['reason_code']) ? $this->_request['reason_code'] : null;
        $reasonComment = $this->_info->explainReasonCode($reasonCode);
        $notificationAmount = $this->_order
            ->getBaseCurrency()
            ->formatTxt($this->_request['mc_gross'] + $this->_request['mc_fee']);
        $paymentStatus = $this->_filterPaymentStatus(isset($this->_request['payment_status'])
            ? $this->_request['payment_status']
            : null
        );
        $orderStatus = ($paymentStatus == Mage_Paypal_Model_Info::PAYMENTSTATUS_REVERSED)
            ? Mage_Paypal_Model_Info::ORDER_STATUS_REVERSED
            : Mage_Paypal_Model_Info::ORDER_STATUS_CANCELED_REVERSAL;
        /**
         * Change order status to PayPal Reversed/PayPal Cancelled Reversal if it is possible.
         */
        $transactionId = Mage::helper('paypal')->getHtmlTransactionId(
            $this->_config->getMethodCode(),
            $this->_request['txn_id']
        );
        $message = Mage::helper('paypal')->__('IPN "%s". %s Transaction amount %s. Transaction ID: "%s"', $this->_request['payment_status'], $reasonComment, $notificationAmount, $transactionId);
        $this->_order->setStatus($orderStatus);
        $payment =  $this->_order->getPayment();

        $payment->setAdditionalInformation('paypal_payment_status', 'reversed');

        $this->_order->save();
        $this->_order->addStatusHistoryComment($message, $orderStatus)
            ->setIsCustomerNotified(true)
            ->save();

        $this->_order->queueOrderUpdateEmail(true);
    }

}
