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
 * @author     	Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 */

class Esmart_PayPalBrasil_Model_Config extends Mage_Paypal_Model_Config
{

    /**
	 * Instructions for generating proper BN code
	 *
	 * @var array
	 */
    protected $_buildNotationPPMap = array (
        'paypal_standard'   => 'WPS',
        'paypal_express'    => 'EC',
        'paypal_direct'     => 'DP',
        'paypaluk_express'  => 'EC',
        'paypaluk_direct'   => 'DP',
    );


    /**
     * Express checkout shortcut pic URL getter
     * PayPal will ignore "pal", if there is no total amount specified
     *
     * @param string $localeCode
     * @param float  $orderTotal
     * @param string $pal encrypted summary about merchant
     *
     * @return string
     * @see Paypal_Model_Api_Nvp::callGetPalDetails()
     */
    public function getExpressCheckoutShortcutImageUrl($localeCode, $orderTotal = null, $pal = null)
    {
        if ($this->buttonType === self::EC_BUTTON_TYPE_MARK) {
            return $this->getPaymentMarkImageUrl($localeCode);
        }

        return $this->_helper()->getLogoCenterImageUrl('compra_express', 'png');
    }


    /**
     * Get PayPal "mark" image URL
     * Supposed to be used on payment methods selection
     * $staticSize is applicable for static images only
     *
     * @param string $localeCode
     * @param float $orderTotal
     * @param string $pal
     * @param string $staticSize
     *
     * @return string
     */
    public function getPaymentMarkImageUrl($localeCode, $orderTotal = null, $pal = null, $staticSize = null)
    {
        if (null === $staticSize) {
            $staticSize = $this->paymentMarkSize;
        }

        switch ($staticSize) {
            case self::PAYMENT_MARK_37x23:
            case self::PAYMENT_MARK_50x34:
            case self::PAYMENT_MARK_60x38:
            case self::PAYMENT_MARK_180x113:
                break;
            default:
            $staticSize = self::PAYMENT_MARK_37x23;
        }

        return $this->_helper()->getLogoCenterImageUrl('paypal_nf_v01', 'png');
	}


    /**
     * Return supported types for PayPal logo
     *
     * @return array
     */
    public function getAdditionalOptionsLogoTypes()
    {
		$hz = $this->_helper()->__('Horizontal');
		$vt = $this->_helper()->__('Vertical');
		$cl = $this->_helper()->__('Colored');
		$bw = $this->_helper()->__('Black and White');

		return array(
            'selo_aceitacao_horizontal'             => $this->_helper()->__('Now Accepting PayPal Logo (%s, %s)', $hz, $cl),
            'selo_aceitacao_vertical'               => $this->_helper()->__('Now Accepting PayPal Logo (%s, %s)', $vt, $cl),

            'selo_aceitacao_horizontal_pb'          => $this->_helper()->__('Now Accepting PayPal Logo (%s, %s)', $hz, $bw),
            'selo_aceitacao_vertical_pb'            => $this->_helper()->__('Now Accepting PayPal Logo (%s, %s)', $vt, $bw),

            'selo-preferencia_loja_horizontal'      => $this->_helper()->__('This Store Prefer PayPal Logo (%s, %s)', $hz, $cl),
            'selo-preferencia_loja_vertical'        => $this->_helper()->__('This Store Prefer PayPal Logo (%s, %s)', $vt, $cl),

            'selo-preferencia_sbandeira_horizontal' => $this->_helper()->__('We Prefer PayPal Logo (%s, %s)', $hz, $cl),
            'selo-preferencia_sbandeira_vertical'   => $this->_helper()->__('We Prefer PayPal Logo (%s, %s)', $vt, $cl),

            'selo-preferencia_site_horizontal'      => $this->_helper()->__('This Website Prefer PayPal Logo (%s, %s)', $hz, $cl),
            'selo-preferencia_site_vertical'        => $this->_helper()->__('This Website Prefer PayPal Logo (%s, %s)', $vt, $cl),

            'compra_segura_horizontal'              => $this->_helper()->__('Secure Purchase PayPal Logo (%s, %s)', $hz, $cl),
            'compra_segura_vertical'                => $this->_helper()->__('Secure Purchase PayPal Logo (%s, %s)', $vt, $cl),

            'compra_segura_horizontal_pb'           => $this->_helper()->__('Secure Purchase PayPal Logo (%s, %s)', $hz, $bw),
            'compra_segura_vertical_pb'             => $this->_helper()->__('Secure Purchase PayPal Logo (%s, %s)', $vt, $bw),
        );
    }


    /**
     * Return PayPal logo URL with additional options
     *
     * @param string      $localeCode Supported locale code
     * @param bool|string $type       One of supported logo types
     *
     * @return string|bool Logo Image URL or false if logo disabled in configuration
     */
    public function getAdditionalOptionsLogoUrl($localeCode, $type = false)
    {
        $configType = Mage::getStoreConfig($this->_mapGenericStyleFieldset('logo'), $this->_storeId);
        if (!$configType) {
            return false;
        }
        $type = $type ? $type : $configType;
        $locale = $this->_getSupportedLocaleCode($localeCode);
        $supportedTypes = array_keys($this->getAdditionalOptionsLogoTypes());
        if (!in_array($type, $supportedTypes)) {
            $type = self::DEFAULT_LOGO_TYPE;
        }

        return $this->_helper()->getLogoCenterImageUrl($type, 'png');
    }


	/**
	 * Gets the helper singleton instance
	 *
	 * @return Esmart_PayPalBrasil_Helper_Data
	 */
	protected function _helper()
	{
		return Mage::helper('esmart_paypalbrasil');
	}

     protected function _getSpecificConfigPath($fieldName)
    {

        $path = null;
        switch ($this->_methodCode) {
            case Esmart_PayPalBrasil_Model_Plus::CODE:
                $path = $this->_mapPlusFieldset($fieldName);
                break;
            default:
                return parent::_getSpecificConfigPath($fieldName);
                break;
        }

        if ($path === null) {
            switch ($this->_methodCode) {
                case Esmart_PayPalBrasil_Model_Plus::CODE:
                    $path = $this->_mapWppFieldset($fieldName);
                    break;
            }
        }
        if ($path === null) {
            $path = $this->_mapGeneralFieldset($fieldName);
        }
        if ($path === null) {
            $path = $this->_mapGenericStyleFieldset($fieldName);
        }
        return $path;
    }


    /**
     * Map PayPal Standard config fields
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapPlusFieldset($fieldName)
    {
        switch ($fieldName)
        {
            case 'sandbox_flag':
                return 'payment/' . Esmart_PayPalBrasil_Model_Plus::CODE . "/{$fieldName}";
            default:
                return $this->_mapMethodFieldset($fieldName);
        }
    }

    /* return a payment methods accepted on country */
    public function getCountryMethods($countryCode = null)
    {

        $Brazilmethods = array(
            'BR' => array(
                self::METHOD_WPS,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
                Esmart_PayPalBrasil_Model_Plus::CODE
            )
        );

        switch ($countryCode) {
            case 'BR':
                return $Brazilmethods[$countryCode];
                break;            
            default:
                 return parent::getCountryMethods($countryCode);
                break;
        }
    }

}
