<?php
require_once 'AW/Onestepcheckout/controllers/AjaxController.php';

class Esmart_PayPalBrasil_AjaxController extends AW_Onestepcheckout_AjaxController
{
    protected function _savePayment()
    {
        $data = $this->getRequest()->getPost('payment', array());
        if (!empty($data)) {
            // hack for AW_Points compatibility
            $session = Mage::getSingleton('checkout/session');
            $data['use_points'] = $session->getData('use_points');
            $data['points_amount'] = $session->getData('points_amount');
            if( $data['method'] == null ){
                $data['method'] = 'paypal_plus';
            }
            $saveResult = $this->getOnepage()->savePayment($data);
            if (isset($saveResult['error'])) {
                throw new Exception($saveResult['message']);
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
        }
        return $this;
    }

}
