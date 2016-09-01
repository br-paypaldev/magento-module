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
 * @author        Ricardo Martins <ricardo.martins@e-smart.com.br>
 * @author        Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 * @author        Rafael K Ventura <rafael.silva@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Plus_Paypal_Autoload
{

    private static $_instance;

    public static function instance()
    {
        if (!self::$_instance) {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }

    /*
     * static method to register classes;
     * unregister Varien to skip of Warnings
     *
     */
    public static function register()
    {
        spl_autoload_unregister(array(Varien_Autoload::instance(), 'autoload'));
        spl_autoload_register(array(self::instance(), 'autoload'), true, true);
    }

    /**
     * Autoload method of lib
     *
     * @param string $className
     *
     * @return void
     */
    public function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $dirs = explode(':', get_include_path());

        foreach ($dirs as $dir) {
            $fullPathFile = $dir . DS . $fileName . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

            if (file_exists($fullPathFile)) {
                require_once $fullPathFile;
                break;
            }
        }
    }
}