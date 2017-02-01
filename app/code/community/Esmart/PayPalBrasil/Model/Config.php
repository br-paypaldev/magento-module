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
             'selo_pp_parcelado10x_01' =>  $this->_helper()->__('selo_pp_parcelado10x_01'),
             'selo_pp_parcelado10x_02' =>  $this->_helper()->__('selo_pp_parcelado10x_02'),
             'selo_pp_parcelado11x_01' =>  $this->_helper()->__('selo_pp_parcelado11x_01'),
             'selo_pp_parcelado11x_02' =>  $this->_helper()->__('selo_pp_parcelado11x_02'),
             'selo_pp_parcelado12x_01' =>  $this->_helper()->__('selo_pp_parcelado12x_01'),
             'selo_pp_parcelado12x_02' =>  $this->_helper()->__('selo_pp_parcelado12x_02'),
             'selo_pp_parcelado2x_01' =>  $this->_helper()->__('selo_pp_parcelado2x_01'),
             'selo_pp_parcelado2x_02' =>  $this->_helper()->__('selo_pp_parcelado2x_02'),
             'selo_pp_parcelado3x_01' =>  $this->_helper()->__('selo_pp_parcelado3x_01'),
             'selo_pp_parcelado3x_02' =>  $this->_helper()->__('selo_pp_parcelado3x_02'),
             'selo_pp_parcelado4x_01' =>  $this->_helper()->__('selo_pp_parcelado4x_01'),
             'selo_pp_parcelado4x_02' =>  $this->_helper()->__('selo_pp_parcelado4x_02'),
             'selo_pp_parcelado5x_01' =>  $this->_helper()->__('selo_pp_parcelado5x_01'),
             'selo_pp_parcelado5x_02' =>  $this->_helper()->__('selo_pp_parcelado5x_02'),
             'selo_pp_parcelado6x_01' =>  $this->_helper()->__('selo_pp_parcelado6x_01'),
             'selo_pp_parcelado6x_02' =>  $this->_helper()->__('selo_pp_parcelado6x_02'),
             'selo_pp_parcelado7x_01' =>  $this->_helper()->__('selo_pp_parcelado7x_01'),
             'selo_pp_parcelado7x_02' =>  $this->_helper()->__('selo_pp_parcelado7x_02'),
             'selo_pp_parcelado8x_01' =>  $this->_helper()->__('selo_pp_parcelado8x_01'),
             'selo_pp_parcelado8x_02' =>  $this->_helper()->__('selo_pp_parcelado8x_02'),
             'selo_pp_parcelado9x_01' =>  $this->_helper()->__('selo_pp_parcelado9x_01'),
             'selo_pp_parcelado9x_02' =>  $this->_helper()->__('selo_pp_parcelado9x_02'),
             'selo_pp_rodape_01' =>  $this->_helper()->__('selo_pp_rodape_01'),
             'selo_pp_rodape_02' =>  $this->_helper()->__('selo_pp_rodape_02'),
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
        if ($type) {
            $configType = Mage::getStoreConfig($this->_mapGenericStyleFieldset($type), $this->_storeId);
            $type = null;
        }else{
            $configType = Mage::getStoreConfig($this->_mapGenericStyleFieldset('logo'), $this->_storeId);
        }
     
        if (!$configType) {
            return false;
        }
        $type = $type ? $type : $configType;
        $locale = $this->_getSupportedLocaleCode($localeCode);
        $supportedTypes = array_keys($this->getAdditionalOptionsLogoTypes());
        if (!in_array($type, $supportedTypes)) {
            $type = self::DEFAULT_LOGO_TYPE;
        }

        return $this->_helper()->getLogoCenterImageUrl($type, 'jpg');
    }

        /**
     * Map PayPal common style config fields
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapGenericStyleFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'logo':
            case 'logo_footer':
            case 'logo_productview':
            case 'page_style':
            case 'paypal_hdrimg':
            case 'paypal_hdrbackcolor':
            case 'paypal_hdrbordercolor':
            case 'paypal_payflowcolor':
                return "paypal/style/{$fieldName}";
            default:
                return null;
        }
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
            case Esmart_PayPalBrasil_Model_Invoice::CODE:
                $path = $this->_mapInvoiceFieldset($fieldName);
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

    /**
     * Map PayPal Standard config fields
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapInvoiceFieldset($fieldName)
    {
        switch ($fieldName)
        {
            case 'sandbox_flag':
                return 'payment/' . Esmart_PayPalBrasil_Model_Invoice::CODE . "/{$fieldName}";
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
                Esmart_PayPalBrasil_Model_Plus::CODE,
                Esmart_PayPalBrasil_Model_Invoice::CODE
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
