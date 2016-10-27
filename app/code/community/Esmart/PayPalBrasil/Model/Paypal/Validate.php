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
 * @author        Rafael K Ventura <rafael.silva@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Paypal_Validate
{

    /**
     * Validator Regex Types
     *
     * @var array
     */
    protected static $_validators = array(
        'OnlyWords' => '/^[[:alpha:]\s\'"\-_&@!?()\[\]-]*$/u', 
        'OnlyNumbers' => "/^[0-9-]+$/u",
        'AddressMail' => "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/",
        'hasNumeric' => "/[0-9]/",
        'hasWords' => "/[A-Za-z]/",
        );

    /*
     * primeira ideia :)
     * Question if the value is in the correct format
     * All regex already valid if field is empty
     * var $clean clean a string with only numbers
     */
    public static function is($value, $type = null, $clean = false)
    {
        /* if pass a value array with the same validation type 
         * recursive ;) 
         */
        if(is_array($value)){
            foreach ($value as $key => $_field) {
                $valid = self::is($value[$key], $type, $clean);
                if (!$valid) {
                    return false;
                }
            }
            /* all array was Ok*/
            return true;
        }

        if (empty($value)) {
            return false;
        }

        if ($clean && $type == 'OnlyNumbers') {

            $pattern = self::$_validators['hasWords'];

            if(!preg_match($pattern, $value)){
                /* Replace All $value to only Numbers */
                $value = preg_replace('/\D/', '', trim($value));
            }
            
        }else{
            /* remove space */
            $value = preg_replace('/\s+/', '', $value);
        }

        /* exist validator type 
         * instead of use a switch case 
         */
        if(isset(self::$_validators[$type])){

            $pattern = self::$_validators[$type];

            $teste = preg_match($pattern, $value);
        
        }else{
            throw new Exception('Validation type not supported');        
        }

        /* validate id format is ok */
        if (preg_match($pattern, $value)) {
            return true;
        }
        return false;
    }   

    /*
     * possibilidade de limpar caracteres especiais usando a variavel $clean
     * validar telefones
     *
     */
    public static function isOnlyNumeric($value, $clean = false){
        if (!self::hasWords($value)) {
            if ($clean) {
                $value = preg_replace('/\D/', '', trim($value));                
            }         
            $pattern = self::$_validators['OnlyNumbers'];
           
            return self::engine($pattern, $value);
        }
        return false;
    }

    /*
     * pergunta se existe letras e apenas letras
     */
    public static function isOnlyWord($value, $clean = false){
        $pattern = self::$_validators['OnlyWords'];
        if ($clean) {
            $value = preg_replace('/\s+/', '', $value);
        }      
        return self::engine($pattern, $value);
    }

    /*
     * 
     */
    public static function isMailAddress($value){
        $pattern = self::$_validators['AddressMail'];
        return self::engine($pattern, $value);
    }

    /*
     *
     */
    public static function hasNumeric($value){
        $pattern = self::$_validators['hasNumeric'];
        return self::engine($pattern, $value);
    }

    /*
     *
     */
    public static function hasWords($value){
        $pattern = self::$_validators['hasWords'];
        return self::engine($pattern, $value);
    }

    /*
     *
     */
    public static function isValidTaxvat($value){

        $value = preg_replace('/\D/', '', trim($value));

        if (strlen($value) == 11) {
           return self::validateCpf($value);
        }
        return self::validateCnpj($value);
    }

    /*
     *
     */
    private static function engine($pattern, $value){
        return preg_match($pattern, $value);
    }

    /* validação CPF CNPJ */

    public function validateCpf($cpf)
    {
        // Verifica se um número foi informado
        if (empty($cpf)) {
            return false;
        }

        // Elimina possivel mascara
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        //$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 11
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' ||
            $cpf == '11111111111' ||
            $cpf == '22222222222' ||
            $cpf == '33333333333' ||
            $cpf == '44444444444' ||
            $cpf == '55555555555' ||
            $cpf == '66666666666' ||
            $cpf == '77777777777' ||
            $cpf == '88888888888' ||
            $cpf == '99999999999'
        ) {
            return false;
        }
        // Calcula os digitos verificadores para verificar se o CPF é válido
        else {
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            return true;
        }
    }

    public function validateCnpj($cnpj)
    {
        $cnpj           = trim($cnpj);
        $soma           = 0;
        $multiplicador  = 0;
        $multiplo       = 0;

        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if(empty($cnpj) || strlen($cnpj) != 14) {
            return false;
        }

        for ($i = 0; $i <= 9; $i++) {
            $repetidos = str_pad('', 14, $i);
            if($cnpj === $repetidos) {
                return false;
            }
        }

        //pega primeira parte do cnpj sem os digitos verificadores
        $parte1             = substr($cnpj, 0, 12);
        
        //inverte primeira parte do cnpj para continuar validação
        $parte1Invertida   = strrev($parte1);

        for ($i = 0; $i <= 11; $i++) {
            $multiplicador  = $i == 0 || $i == 8 ? 2 : $multiplicador;
            $multiplo       = $parte1Invertida[$i] * $multiplicador;
            $soma           += $multiplo;
            $multiplicador ++;
        }

        $rest               = $soma % 11;//obtendo primeiro digito verificador
        $dv1                = $rest == 0 || $rest == 1 ? 0 : 11 - $rest;
        $parte1             .= $dv1;//pega primeira parte do cnpj concatenando primeiro digito
        $parte1Invertida    = strrev($parte1);//mais uma vez inverte a primeira parte do cnpj para continuar validacao
        $soma               = 0;

        for ($i = 0; $i <= 12; $i++) {
            $multiplicador  = $i == 0 || $i == 8 ? 2 : $multiplicador;
            $multiplo       = $parte1Invertida[$i] * $multiplicador;
            $soma           += $multiplo;
            $multiplicador ++;
        }

        //obtem segundo digito verificador
        $rest   = $soma % 11;
        $dv2    = ($rest == 0 || $rest == 1) ? 0 : 11 - $rest;

        //compara se os digitos obtidos sao iguais aos informados ou a segunda parte do cnpj
        return $dv1 == $cnpj[12] && $dv2 == $cnpj[13] ? true : false;
    }
}