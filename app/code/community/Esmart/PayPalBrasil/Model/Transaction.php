<?php

use PayPal\Api\Transaction;

class Esmart_PayPalBrasil_Model_Transaction extends Transaction {

    /**
     * PayPal set $url Notify
     *
     * @param string $url
     * 
     * @return $this
     */
    public function setNotifyUrl($url) {
        $this->notify_url = $url;
        return $this;
    }

    /**
     * PayPal get Notify url store 
     *
     * @return string
     */
    public function getNotifyUrl() {
        return $this->notify_url;
    }

}
