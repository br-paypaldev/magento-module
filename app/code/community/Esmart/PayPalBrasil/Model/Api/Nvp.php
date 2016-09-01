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
}
