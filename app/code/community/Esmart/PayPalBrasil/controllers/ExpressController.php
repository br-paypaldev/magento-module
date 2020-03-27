<?php
/**
 * Smart E-commerce do Brasil Tecnologia LTDA
 *
 * INFORMAÇÕES SOBRE LICENÇA
 *
 * Open Software License (OSL 3.0).
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Não edite este arquivo caso você pretenda atualizar este módulo futuramente
 * para novas versões.
 *
 * @category    Esmart
 * @package     Esmart_PayPalBrasil
 * @copyright   Copyright (c) 2013 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author      Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 * @author      Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */

/**
 * Express Checkout Controller
 */
class Esmart_PayPalBrasil_ExpressController extends Esmart_PayPalBrasil_Controller_Express
{
    /**
     * Code payment method
     * @const string
     */
    const LOG_FILENAME = 'ppplusbrasil_controller_exception.log';

    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = 'paypal/config';

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = 'esmart_paypalbrasil/express_checkout';

    /**
     * Generate URL PaypalPlus
     */
    public function generateUrlAction()
    {
        $postData = $this->getRequest()->getParams();

        /** @var Esmart_PayPalBrasil_Model_Plus_Iframe $model */
        $model      = Mage::getModel('esmart_paypalbrasil/plus_iframe')->setNonPersistedData($postData);
        $data       = array();
        $helper     = Mage::helper('esmart_paypalbrasil');
        $quote      = $helper->getQuote(null);
        $quote->getPayment()->unsAdditionalInformation();
        $quote->collectTotals();

        try {

            if ($quote->isVirtual()) {
                $quote->getBillingAddress()->setPaymentMethod(Esmart_PayPalBrasil_Model_Plus::CODE);
            } else {
                $quote->getShippingAddress()->setPaymentMethod(Esmart_PayPalBrasil_Model_Plus::CODE);
            }

            if(isset($postData["billing"])) {
                foreach ($postData["billing"] as $billing) {

                    $key = key($postData["billing"]);
                    next($postData["billing"]);
                    if ($key == "address_id" || $key == "quote_id") {
                        continue;
                    }

                    if (!array_key_exists($key, $quote->getBillingAddress()->getData())) {
                        continue;
                    }

                    if ($billing == "") {
                        continue;
                    }

                    if (is_array($billing)) {
                        continue;
                    }

                    if ($quote->getBillingAddress()->getData($key) != $billing) {
                        $quote->getBillingAddress()->setData($key, $billing);
                        $quote->getBillingAddress()->save();
                        $quote->save();
                    }

                    if (isset($postData["billing"]["use_for_shipping"]) && $postData["billing"]["use_for_shipping"]) {
                        if ($quote->getShippingAddress()->getData($key) != $billing) {
                            $quote->getShippingAddress()->setData($key, $billing);
                            $quote->getShippingAddress()->save();
                            $quote->save();
                        }
                    }
                }
            }

            $payerInfo   = $model->getCustomerInformation($quote);

            if(!empty($postData['installment'])){
                $payerInfo = $model->getCustomerInformationInstallments($quote,$postData['installment'],$payerInfo);
            }

            $approvalUrl = $model->getApprovalUrlPaypalPlus();

            $data = array_merge($payerInfo, $approvalUrl);
            if(empty($data['approvalUrl'])){ throw new Exception('Ocorreu um erro inesperado, tente novamente, caso o problema persista por favor entre em contato.',3); }
            $return     = array('success' => $data);

        } catch (Exception $exception) {
            $data['invoice_number'] = $quote->getReservedOrderId();
            Mage::helper('esmart_paypalbrasil')->logException(__FILE__, __CLASS__, __FUNCTION__, __LINE__, self::LOG_FILENAME, null, $data);

            $general_message = 'Prezado cliente, favor preencher e/ou validar os dados dos passos anteriores antes de selecionar a Forma de Pagamento. Caso o problema persista por favor entre em contato.';

            if ($exception->getCode() == 3){
                $general_message = $exception->getMessage();
            }

            $return = array(
                'error'  => $exception->getCode(),
                'message' => $general_message
            );
        }

        Esmart_PayPalBrasil_Model_Debug::writeLog();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }

    /**
     * Save Paypal return information
     */
    public function savePaypalInformationAction()
    {
        /** @var Esmart_PayPalBrasil_Model_Plus $model */
        $model = Mage::getModel('esmart_paypalbrasil/plus');

        $postData = $this->getRequest()->getPost();

        try {
            $model->saveReturnPaypal($postData);
            $return = array('success' => true);
        } catch (Exception $exception) {
            $return = array(
                'error'  => true,
                'message' => $exception->getMessage()
            );
        }

        Esmart_PayPalBrasil_Model_Debug::writeLog();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }

    /**
     * Check if Paypal In-Context is enabled
     */
    public function checkPaypalInContextAction()
    {
        $config = Mage::getStoreConfig('payment/incontext/enable');
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($config));
    }
}