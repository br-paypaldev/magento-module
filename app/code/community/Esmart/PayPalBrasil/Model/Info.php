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
 * PayPal payment information model
 *
 * Aware of all PayPal payment methods
 * Collects and provides access to PayPal-specific payment data
 * Provides business logic information about payment flow
 */
class Esmart_PayPalBrasil_Model_Info extends Mage_Paypal_Model_Info
{

    /**
     * All payment information map
     *
     * @var array
     */
    protected $_paymentMap = array(
        self::PAYER_ID       => 'paypal_payer_id',
        self::PAYER_EMAIL    => 'paypal_payer_email',
        self::PAYER_STATUS   => 'paypal_payer_status',
        self::ADDRESS_ID     => 'paypal_address_id',
        self::ADDRESS_STATUS => 'paypal_address_status',
        self::PROTECTION_EL  => 'paypal_protection_eligibility',
        self::FRAUD_FILTERS  => 'paypal_fraud_filters',
        self::CORRELATION_ID => 'paypal_correlation_id',
        self::AVS_CODE       => 'paypal_avs_code',
        self::CVV2_MATCH     => 'paypal_cvv2_match',
        self::CENTINEL_VPAS  => self::CENTINEL_VPAS,
        self::CENTINEL_ECI   => self::CENTINEL_ECI,
        self::BUYER_TAX_ID   => self::BUYER_TAX_ID,
        self::BUYER_TAX_ID_TYPE => self::BUYER_TAX_ID_TYPE,
        'plots' => 'plots',
        'plots_val' => 'plots_val',
    );

    /**
     * Render info item labels
     *
     * @param string $key
     */
    protected function _getLabel($key)
    {       
        switch ($key) {
            case 'plots':
                return Mage::helper('paypal')->__('Plots');
            case 'plots_val':
                return Mage::helper('paypal')->__('Plots Value');
            default:
                return parent::_getLabel($key);
        }
    }
}
