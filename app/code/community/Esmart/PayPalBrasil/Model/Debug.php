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
 * @category  Esmart
 * @package   Esmart_PayPalBrasil
 * @copyright Copyright (c) 2015 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author    Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */

/**
 * Class Emsart_PayPalBrasil_Model_Debug
 *
 * @category  Esmart
 * @package   Esmart_PayPalBrasil
 * @author    Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Debug
{
    /**
     * Content
     * @var array
     */
    protected static $content = array(PHP_EOL);

    /**
     * Append content
     *
     * @param string $type
     * @param string $details
     * @param mixed $contentData
     *
     * @return void
     */
    public static function appendContent($details, $type = 'default', $contentData = array())
    {
        if (!Mage::getStoreConfigFlag('payment/paypal_plus/debug_mode')) {
            return;
        }

        if (!isset(self::$content[$type])) {
            self::$content[$type] = array();
        }

        self::$content[$type][] = $details;

        foreach ($contentData as $key => $content) {
            $string = "- ";
            $string .= is_string($key)?$key:gettype($key);
            $string .= " : ";
            $string .= is_string($content)?$content:gettype($content);

            self::$content[$type][] = $string;
        }

        self::$content[$type][] = PHP_EOL;
    }

    /**
     * Write LOG
     *
     * @param string $filename
     *
     * @return void
     */
    public static function writeLog($filename = 'ppplusbrasil_debug_mode.log')
    {
        if (!Mage::getStoreConfigFlag('payment/paypal_plus/debug_mode')) {
            return;
        }

        $finalContent = array();
        foreach (self::$content as $key => $content) {
            $content = is_array($content) ? $content : array($content);
            $finalContent[] = implode(PHP_EOL, $content);
        }

        $finalContent = implode(PHP_EOL, $finalContent);

        Mage::log($finalContent, null, $filename, true);
    }
}