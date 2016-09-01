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
 * @author      Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Block_Plus_Info extends Mage_Payment_Block_Info
{
    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('esmart/paypalbrasil/plus/info.phtml');
    }

    /**
     * Get card information
     *
     * @param array $data
     *
     * @return string
     */
    public function getCardInformation(array $data)
    {
        $convertArray = array(
            '{parcela_qtde}'    => $data['termQty'],
            '{parcela_valor}'   => $data['termValue'],
        );

        $content = Mage::getStoreConfig('payment/paypal_plus/checkout_payment_info');

        foreach ($convertArray as $holder => $value) {
            $content = str_replace($holder, $value, $content);
        }

        return $content;
    }

    /**
     *
     *
     * @param Varien_Object|array $transport
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $helper = Mage::helper('esmart_paypalbrasil');

        $info = $this->getInfo();
          $card = json_decode($info->getAdditionalInformation('paypal_plus_cards'));
        $transport = new Varien_Object(
            array($helper->__("Transaction ID") => $info->getLastTransId(),
                    $helper->__("Installment") => $card->termQty,
                    $helper->__("Installment Value") => $card->termValue,
                    $helper->__("Total") => $card->total)
        );
        $transport = parent::_prepareSpecificInformation($transport);
        
        return $transport;
    }
}