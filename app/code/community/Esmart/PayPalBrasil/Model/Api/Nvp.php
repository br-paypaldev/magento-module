<?php
/**
 *
 */

/**
 *
 *
 */
class Esmart_PayPalBrasil_Model_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{


    /**
     * Global public interface map
     * @var array
     */
    protected $_globalMap = array(
        // each call
        'VERSION'      => 'version',
        'USER'         => 'api_username',
        'PWD'          => 'api_password',
        'SIGNATURE'    => 'api_signature',
        'BUTTONSOURCE' => 'build_notation_code',

        // for Unilateral payments
        'SUBJECT'      => 'business_account',

        // commands
        'PAYMENTACTION' => 'payment_action',
        'RETURNURL'     => 'return_url',
        'CANCELURL'     => 'cancel_url',
        'INVNUM'        => 'inv_num',
        'TOKEN'         => 'token',
        'CORRELATIONID' => 'correlation_id',
        'SOLUTIONTYPE'  => 'solution_type',
        'GIROPAYCANCELURL'  => 'giropay_cancel_url',
        'GIROPAYSUCCESSURL' => 'giropay_success_url',
        'BANKTXNPENDINGURL' => 'giropay_bank_txn_pending_url',
        'IPADDRESS'         => 'ip_address',
        'NOTIFYURL'         => 'notify_url',
        'RETURNFMFDETAILS'  => 'fraud_management_filters_enabled',
        'NOTE'              => 'note',
        'REFUNDTYPE'        => 'refund_type',
        'ACTION'            => 'action',
        'REDIRECTREQUIRED'  => 'redirect_required',
        'SUCCESSPAGEREDIRECTREQUESTED'  => 'redirect_requested',
        'REQBILLINGADDRESS' => 'require_billing_address',
        // style settings
        'PAGESTYLE'      => 'page_style',
        'HDRIMG'         => 'hdrimg',
        'HDRBORDERCOLOR' => 'hdrbordercolor',
        'HDRBACKCOLOR'   => 'hdrbackcolor',
        'PAYFLOWCOLOR'   => 'payflowcolor',
        'LOCALECODE'     => 'locale_code',
        'PAL'            => 'pal',
        'USERSELECTEDFUNDINGSOURCE' => 'funding_source',

        // transaction info
        'TRANSACTIONID'   => 'transaction_id',
        'AUTHORIZATIONID' => 'authorization_id',
        'REFUNDTRANSACTIONID' => 'refund_transaction_id',
        'COMPLETETYPE'    => 'complete_type',
        'AMT' => 'amount',
        'ITEMAMT' => 'subtotal_amount',
        'GROSSREFUNDAMT' => 'refunded_amount', // possible mistake, check with API reference

        // payment/billing info
        'CURRENCYCODE'  => 'currency_code',
        'PAYMENTSTATUS' => 'payment_status',
        'PENDINGREASON' => 'pending_reason',
        'PROTECTIONELIGIBILITY' => 'protection_eligibility',
        'PAYERID' => 'payer_id',
        'PAYERSTATUS' => 'payer_status',
        'ADDRESSID' => 'address_id',
        'ADDRESSSTATUS' => 'address_status',
        'EMAIL'         => 'email',
            // backwards compatibility
            'FIRSTNAME'     => 'firstname',
            'LASTNAME'      => 'lastname',

        // shipping rate
        'SHIPPINGOPTIONNAME' => 'shipping_rate_code',
        'NOSHIPPING'         => 'suppress_shipping',

        // paypal direct credit card information
        'CREDITCARDTYPE' => 'credit_card_type',
        'ACCT'           => 'credit_card_number',
        'EXPDATE'        => 'credit_card_expiration_date',
        'CVV2'           => 'credit_card_cvv2',
        'STARTDATE'      => 'maestro_solo_issue_date', // MMYYYY, always six chars, including leading zero
        'ISSUENUMBER'    => 'maestro_solo_issue_number',
        'CVV2MATCH'      => 'cvv2_check_result',
        'AVSCODE'        => 'avs_result',
        // cardinal centinel
        'AUTHSTATUS3DS' => 'centinel_authstatus',
        'MPIVENDOR3DS'  => 'centinel_mpivendor',
        'CAVV'         => 'centinel_cavv',
        'ECI3DS'       => 'centinel_eci',
        'XID'          => 'centinel_xid',
        'VPAS'         => 'centinel_vpas_result',
        'ECISUBMITTED3DS' => 'centinel_eci_result',

        // recurring payment profiles
//'TOKEN' => 'token',
        'SUBSCRIBERNAME'    =>'subscriber_name',
        'PROFILESTARTDATE'  => 'start_datetime',
        'PROFILEREFERENCE'  => 'internal_reference_id',
        'DESC'              => 'schedule_description',
        'MAXFAILEDPAYMENTS' => 'suspension_threshold',
        'AUTOBILLAMT'       => 'bill_failed_later',
        'BILLINGPERIOD'     => 'period_unit',
        'BILLINGFREQUENCY'    => 'period_frequency',
        'TOTALBILLINGCYCLES'  => 'period_max_cycles',
//'AMT' => 'billing_amount', // have to use 'amount', see above
        'TRIALBILLINGPERIOD'      => 'trial_period_unit',
        'TRIALBILLINGFREQUENCY'   => 'trial_period_frequency',
        'TRIALTOTALBILLINGCYCLES' => 'trial_period_max_cycles',
        'TRIALAMT'            => 'trial_billing_amount',
// 'CURRENCYCODE' => 'currency_code',
        'SHIPPINGAMT'         => 'shipping_amount',
        'TAXAMT'              => 'tax_amount',
        'INITAMT'             => 'init_amount',
        'FAILEDINITAMTACTION' => 'init_may_fail',
        'PROFILEID'           => 'recurring_profile_id',
        'PROFILESTATUS'       => 'recurring_profile_status',
        'STATUS'              => 'status',

        //Next two fields are used for Brazil only
        'TAXID'               => 'buyer_tax_id',
        'TAXIDTYPE'           => 'buyer_tax_id_type',

        'BILLINGAGREEMENTID' => 'billing_agreement_id',
        'REFERENCEID' => 'reference_id',
        'BILLINGAGREEMENTSTATUS' => 'billing_agreement_status',
        'BILLINGTYPE' => 'billing_type',
        'SREET' => 'street',
        'CITY' => 'city',
        'STATE' => 'state',
        'COUNTRYCODE' => 'countrycode',
        'ZIP' => 'zip',
        'PAYERBUSINESS' => 'payer_business',

        // this fiels is to version 124.0      
        'PAYMENTINFO_0_FINANCINGTERM' => 'plots',
        'PAYMENTINFO_0_FINANCINGMONTHLYPAYMENT' => 'plots_val',
    );

 /**
     * Payment information response specifically to be collected after some requests
     * @var array
     */
    protected $_paymentInformationResponse = array(
        'PAYERID', 'PAYERSTATUS', 'CORRELATIONID', 'ADDRESSID', 'ADDRESSSTATUS',
        'PAYMENTSTATUS', 'PENDINGREASON', 'PROTECTIONELIGIBILITY', 'EMAIL', 'SHIPPINGOPTIONNAME', 'TAXID', 'TAXIDTYPE','PAYMENTINFO_0_FINANCINGTERM','PAYMENTINFO_0_FINANCINGMONTHLYPAYMENT'
        );


    /**
     * Return Paypal Api version
     *
     * @return string
     */
    public function getVersion()
    {
        return '124.0';
    }

    /**
     * GetExpressCheckoutDetails call
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetExpressCheckoutDetails
     */
    function callGetExpressCheckoutDetails()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_getExpressCheckoutDetailsRequest);
        $request = $this->_exportToRequest($this->_getExpressCheckoutDetailsRequest);
        $response = $this->call(self::GET_EXPRESS_CHECKOUT_DETAILS, $request);

        if(isset($response['PAYMENTINFO_0_FINANCINGDISCOUNTAMOUNT']))
            Mage::app()->getRequest()->setParam('pp_express_discount', $response['PAYMENTINFO_0_FINANCINGDISCOUNTAMOUNT']);

        if(isset($response['PAYMENTREQUEST_0_SHIPTONAME']))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTONAME', $response['PAYMENTREQUEST_0_SHIPTONAME']);

        if(isset($response['PAYMENTREQUEST_0_SHIPTOSTREET']))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTOSTREET', $response['PAYMENTREQUEST_0_SHIPTOSTREET']);

        if(isset($response['PAYMENTREQUEST_0_SHIPTOSTREET2']))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTOSTREET2', $response['PAYMENTREQUEST_0_SHIPTOSTREET2']);

        if(isset($response['PAYMENTREQUEST_0_SHIPTOCITY']))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTOCITY', $response['PAYMENTREQUEST_0_SHIPTOCITY']);

        if(isset($response['PAYMENTREQUEST_0_SHIPTOSTATE']))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTOSTATE', $response['PAYMENTREQUEST_0_SHIPTOSTATE']);

        if(isset($response['PAYMENTREQUEST_0_SHIPTOZIP']))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTOZIP', $response['PAYMENTREQUEST_0_SHIPTOZIP']);

        if(isset($response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE', $response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']);

        if(Mage::app()->getRequest()->getParam('telephone'))
            Mage::app()->getRequest()->setParam('PAYMENTREQUEST_0_SHIPTOPHONENUM', Mage::app()->getRequest()->getParam('telephone'));

        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_exportAddressses($response);
    }

    /**
     * DoExpressCheckout call
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoExpressCheckoutPayment
     */
    public function callDoExpressCheckoutPayment()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_doExpressCheckoutPaymentRequest);
        $request = $this->_exportToRequest($this->_doExpressCheckoutPaymentRequest);
        $this->_exportLineItems($request);

        $lenharo = Mage::getStoreConfig('payment/moip_transparente_standard/validador_retorno');

        if( empty($lenharo) && ($pp_express_discount =  Mage::app()->getRequest()->getParam('pp_express_discount')) ){
            $request['ITEMAMT'] = $request['ITEMAMT'] + $pp_express_discount;
        }

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTONAME') )
            $request['PAYMENTREQUEST_0_SHIPTONAME'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTONAME');

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOSTREET') )
            $request['PAYMENTREQUEST_0_SHIPTOSTREET'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOSTREET');

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOSTREET2') )
            $request['PAYMENTREQUEST_0_SHIPTOSTREET2'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOSTREET2');

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOCITY') )
            $request['PAYMENTREQUEST_0_SHIPTOCITY'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOCITY');

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOSTATE') )
            $request['PAYMENTREQUEST_0_SHIPTOSTATE'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOSTATE');

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOZIP') )
            $request['PAYMENTREQUEST_0_SHIPTOZIP'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOZIP');

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE') )
            $request['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE');

        if( Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOPHONENUM') )
            $request['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = Mage::app()->getRequest()->getParam('PAYMENTREQUEST_0_SHIPTOPHONENUM');

        $token = Mage::app()->getRequest()->getParam('token');
        $payer_id = Mage::app()->getRequest()->getParam('PayerID');

        if(is_null($request["TOKEN"]) && isset($token)) {
            $request["TOKEN"] = $token;
        }

        if(is_null($request["PAYERID"]) && isset($payer_id)) {
            $request["PAYERID"] = $payer_id;
        }

        $response = $this->call(self::DO_EXPRESS_CHECKOUT_PAYMENT, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doExpressCheckoutPaymentResponse, $response);
        $this->_importFromResponse($this->_createBillingAgreementResponse, $response);
    }

    /**
     * Do the API call
     *
     * @param string $methodName
     * @param array $request
     * @return array
     * @throws Mage_Core_Exception
     */
    public function call($methodName, array $request)
    {
        $request = $this->_addMethodToRequest($methodName, $request);
        $eachCallRequest = $this->_prepareEachCallRequest($methodName);
        if ($this->getUseCertAuthentication()) {
            if ($key = array_search('SIGNATURE', $eachCallRequest)) {
                unset($eachCallRequest[$key]);
            }
        }
        $request = $this->_exportToRequest($eachCallRequest, $request);
        $debugData = array('url' => $this->getApiEndpoint(), $methodName => $request);

        try {
            $http = new Varien_Http_Adapter_Curl();
            $config = array(
                'timeout'    => 60,
                'verifypeer' => $this->_config->verifyPeer
            );

            if ($this->getUseProxy()) {
                $config['proxy'] = $this->getProxyHost(). ':' . $this->getProxyPort();
            }
            if ($this->getUseCertAuthentication()) {
                $config['ssl_cert'] = $this->getApiCertificate();
            }
            $http->setConfig($config);
            $http->write(
                Zend_Http_Client::POST,
                $this->getApiEndpoint(),
                '1.1',
                $this->_headers,
                $this->_buildQuery($request)
            );
            $response = $http->read();
        } catch (Exception $e) {
            $debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $this->_debug($debugData);
            throw $e;
        }

        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);
        $response = $this->_deformatNVP($response);

        $debugData['response'] = $response;
        $this->_debug($debugData);
        $response = $this->_postProcessResponse($response);

        // handle transport error
        if ($http->getErrno()) {
            Mage::logException(new Exception(
                sprintf('PayPal NVP CURL connection error #%s: %s', $http->getErrno(), $http->getError())
            ));
            $http->close();

            Mage::throwException(Mage::helper('paypal')->__('Unable to communicate with the PayPal gateway.'));
        }

        // cUrl resource must be closed after checking it for errors
        $http->close();

        if (!$this->_validateResponse($methodName, $response)) {
            Mage::logException(new Exception(
                Mage::helper('paypal')->__("PayPal response hasn't required fields.")
            ));
            Mage::throwException(Mage::helper('paypal')->__('There was an error processing your order. Please contact us or try again later.'));
        }

        $this->_callErrors = array();
        if ($this->_isCallSuccessful($response)) {
            if ($this->_rawResponseNeeded) {
                $this->setRawSuccessResponseData($response);
            }
            return $response;
        }
        $this->_handleCallErrors($response);
        return $response;
    }
}
