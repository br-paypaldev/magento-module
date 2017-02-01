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
 * @author     Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 * @author     Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */

class Esmart_PayPalBrasil_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Base Script content
     * @const
     */
    const JS_BASE = '';

    /**
     * JS events default
     * @const string
     */
    #const JS_EVENTS_DEFAULT = 'esmart/paypalbrasil/Esmart_PaypalBrasil.events.default.js';
    const JS_EVENTS_DEFAULT = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.default.js';

    /**
     * JS events MOIP
     * @const string
     */
    #const JS_EVENTS_MOIP = 'esmart/paypalbrasil/Esmart_PaypalBrasil.events.moip.js';
    const JS_EVENTS_MOIP = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.moip.js';

    /**
     * JS events INOVARTI
     * @const string
     */
    #const JS_EVENTS_INOVARTI = 'esmart/paypalbrasil/Esmart_PaypalBrasil.events.inovarti.js';
    const JS_EVENTS_INOVARTI = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.inovarti.js';

    /**
     * JS events INOVARTI
     * @const string
     */
    #const JS_EVENTS_FIRECHECKOUT = 'esmart/paypalbrasil/Esmart_PaypalBrasil.events.firecheckout.js';
    const JS_EVENTS_FIRECHECKOUT = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.firecheckout.js';

    /**
     * JS events AMASTY
     * @const string
     */
    const JS_EVENTS_AMASTY = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.amasty.js';

    /**
     * JS events AMASTY
     * @const string
     */
    const JS_EVENTS_SMARTCHECKOUT = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.smartcheckout.js';

    /**
     * JS events AHEADWORKS
     * @const string
     */
    const JS_EVENTS_AHEADWORKS = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.aheadworks.js';

    /**
     * @var string
     */
    protected $_ppbUrl = 'https://www.paypal-brasil.com.br';

    /**
     * Returns PayPal Brasil URL
     *
     * @return string
     */
    public function getPPBUrl()
    {
        return $this->_ppbUrl;
    }

    /**
     * Returns PayPal's logo center URL
     *
     * @return string
     */
    public function getLogoCenterUrl()
    {
        return implode('/', array(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN), 'frontend', 'base', 'default', 'esmart', 'paypalbrasil', 'image', 'logos'));
    }

    /**
     * Returns the image URL in PayPal Logo Center
     *
     * @param string $imageName
     * @param string $extension
     *
     * @return string
     */
    public function getLogoCenterImageUrl($imageName = null, $extension = null)
    {
        if(!is_null($imageName)) {
            $_imageFullName = is_null($extension) ? $imageName : implode('.', array($imageName, $extension));

            return implode('/', array($this->getLogoCenterUrl(), $_imageFullName));
        }

        return null;
    }

    /**
     * Get General Config
     *
     * @param string $group
     * @param string $field
     *
     * @return string|null
     */
    public function getConfig($group = null, $field = null)
    {
        if(!is_null($group) && !is_null($field)) {
            return Mage::getStoreConfig("payment/{$group}/{$field}");
        }

        return null;
    }

    /**
     * Get PayPal Express Config
     *
     * @param string $field
     *
     * @return string|null
     */
    public function getExpressConfig($field = null)
    {
        if(!is_null($field)) {
            return $this->getConfig('paypal_express', $field);
        }

        return null;
    }

    /**
     * Get PayPal Standard Config
     *
     * @param string $field
     *
     * @return string|null
     */
    public function getStandardConfig($field = null)
    {
        if(!is_null($field)) {
            return $this->getConfig('paypal_standard', $field);
        }

        return null;
    }

    /**
     * Get Quote
     *
     * @param mixed $quote (Mage_Sales_Model_Quote | Quote ID | null)
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote($quote = null)
    {
        if ($quote instanceof Mage_Sales_Model_Quote) {
            return $quote;
        }

        if (is_numeric($quote)) {
            return Mage::getModel('sales/quote')->load($quote);
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Get profiler name suggestion
     *
     * @return string
     */
    public function getProfilerNameSuggestion()
    {
        return vsprintf(Esmart_PayPalBrasil_Model_Plus::PROFILER_BASE_NAME, array('Store Name', time()));
    }

    /**
     * Get CPF, CNPJ, or TaxVAT
     *
     * @param Mage_Core_Model_Abstract $object
     * @param Varien_Object            $nonPersistedData
     *
     * @return string
     */
    public function getCpfCnpjOrTaxvat(Mage_Core_Model_Abstract $object, Varien_Object $nonPersistedData)
    {
        $cpf     = Mage::getStoreConfig('payment/paypal_plus/cpf');
        $cpfData = $this->getDataFromObject($object, $nonPersistedData, $cpf);

        if (!empty($cpfData)) {
            return $cpfData;
        }

        $cnpj     = Mage::getStoreConfig('payment/paypal_plus/cnpj');
        $cnpjData = $this->getDataFromObject($object, $nonPersistedData, $cnpj);

        if (!empty($cnpjData)) {
            return $cnpjData;
        }

        return $this->getDataFromObject($object, $nonPersistedData, 'taxvat');
    }

    /**
     * Get events Script
     *
     * @return string
     */
    public function getEventsScriptBlock()
    {
        $js = <<<JS
        <script type="text/javascript">
            var head;

            var script;

            head = $$('head')[0];
            if (head)
            {
                script = new Element('script', { type: 'text/javascript', src: '%s' });
                head.appendChild(script);
            }

            EsmartPaypalBrasilPPPlus.base_url = "%s";
        </script>
JS;

        return sprintf(
            $js,
            $this->getCheckoutType(),
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)
        );
    }

    /**
     * Get Checkout Type
     *
     * This method return:
     * - JS PATH
     * OR
     * - true  = OSC is enable
     * - false = checkout default in use
     *
     * @param bool $returnJSEvent
     *
     * @return string|bool
     */
    public function getCheckoutType($returnJSEvent = true)
    {
        // system.xml ;)
        $js = $this->getConfig('paypal_plus','oscoptions');

        if (!$js) {
           return ($returnJSEvent ? $this->getFullJsUrl(self::JS_EVENTS_DEFAULT) : true);
        }

        return $this->getFullJsUrl($js);        
    }

    /**
     * Is OCS
     *
     * return bool
     */
    public function isOneStepCheckout()
    {
        return $this->getCheckoutType(false);
    }

    /**
     * Get FULL url JS
     *
     * @param string $path
     *
     * @return string
     */
    public function getFullJsUrl($path)
    {
        return Mage::getBaseUrl('js') . $path;
    }

    /**
     * Log exception
     *
     * @param string $file
     * @param string $class
     * @param string $method
     * @param int $line
     * @param string $file
     * @param Exception $exception
     * @param array $data
     *
     * @return void
     */
    public function logException($fileOrigin, $class, $method, $line, $file, $exception, $data = array())
    {
        $message    = array();
        $message[]  = '';

        if ($exception) {

            $message[]  = "Exception Message : {$exception->getMessage()}";
            
            if (@$exception->getData()) {
                $data = json_decode($exception->getData());
            }
        }

        $message[]  = "Origin error :";
        $message[]  = " - Filename : {$fileOrigin}";
        $message[]  = " - Class : {$class}";
        $message[]  = " - Method : {$method}";
        $message[]  = " - Line : {$line}";

        if ($data) {
            $message[] = "Details :";
        }

        foreach ($data as $key => $value) {
            $key   = ucwords(str_replace('_', ' ', $key));
            $value = (is_array($value) ? json_encode($value) : $value);
            $message[] = " - {$key} : {$value}";
        }

        $message[] = PHP_EOL . PHP_EOL;

        Mage::log(implode(PHP_EOL, $message), Zend_Log::ERR, $file, true);
    }

    /**
     * Check is CPF or CNPJ
     *
     * @return string
     */
    public function checkIsCpfOrCnpj($value)
    {
        $value = preg_replace('/\D/', '', trim($value));

        if (preg_match('/^\d{11}$/', $value)) {
            return Esmart_PayPalBrasil_Model_Plus::PAYER_TAX_ID_TYPE_CPF;
        }

        return Esmart_PayPalBrasil_Model_Plus::PAYER_TAX_ID_TYPE_CNPJ;
    }

    /**
     * Get Data From Object
     *
     * @var mixed $object
     * @var Varien_Object $nonPersistedData
     * @var mixed $index
     *
     * @return mixed
     */
    public function getDataFromObject($object, $nonPersistedData, $index)
    {
        if (empty($object) || !method_exists($object, 'getData')) {
            return null;
        }

        if ($object->getData($index)) {
            return $object->getData($index);
        }

        // assume this element numeric is address so get a number of street
        if (is_numeric($index) && $street = $object->getStreet($index)) {
            return $street;
        }

        return $nonPersistedData->getData($index);
    }
}
