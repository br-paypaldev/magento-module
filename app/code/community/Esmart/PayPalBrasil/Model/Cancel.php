<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Esmart
 * @package     Esmart_
 * @copyright   Esmart <http://www.e-smart.com.br>
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @author      Leandro Rosa <leandro.rosa@e-mart.com.br>
 *
 */
class Esmart_PayPalBrasil_Model_Cancel
{
    private $error;
    static $errors = array(
        'TRANSACTION_REFUSED',
        'INSTRUMENT_DECLINED',
        'INTERNAL_SERVICE_ERROR',
        'PAYEE_ACCOUNT_RESTRICTED',
        'PAYER_ACCOUNT_LOCKED_OR_CLOSED',
        'PAYER_ACCOUNT_RESTRICTED',
        'PAYER_CANNOT_PAY',
        'TRANSACTION_REFUSED_BY_PAYPAL_RISK',
        'CREDIT_CARD_REFUSED',
    );

    /**
     * Esmart_PayPalBrasil_Model_Cancel constructor.
     * @param array $error
     */
    public function __construct(array $error = array())
    {
        $this->error = new Varien_Object($error);
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool
     */
    public function cancelOrder(Mage_Sales_Model_Order_Payment $payment)
    {
        if (! in_array($this->error->getName(), self::$errors)) {
            return false;
        }

        $message = Mage::helper('esmart_paypalbrasil')->__("Order not created, your payment method was not aproved.");

        Mage::getSingleton('core/session')->addError($message);

        $payment->setAdditionalInformation('paypal_payment_status', 'reversed');
        $payment->setIsTransactionClosed(true);
        $payment->setIsTransactionPending(false);
        #$payment->save();

        $message = Mage::helper('esmart_paypalbrasil')->__("Paypal message: %s", $this->error->getMessage());
        $order = $payment->getOrder();
        $order->addStatusHistoryComment($message);
        $order->setData('paypal_set_cancel', true);

        return true;
    }
}
