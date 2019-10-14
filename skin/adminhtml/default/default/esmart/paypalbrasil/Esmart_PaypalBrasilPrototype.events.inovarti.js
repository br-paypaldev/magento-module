/**
 * Magento Custom by PayPalBrasil
 *
 *  :) design/frontend/base/default/layout/esmart/paypalbrasil.xml
 *
 *  AUX js/esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js
 *
 * @package     js
 */
var configIframe = JSON.parse(window.paypal);
EsmartPaypalBrasilPPPlus.osc= true;

if(configIframe.installments == true) {

    document.querySelector('input[name="shipping_method"]:checked').checked = false

    $('p_method_paypal_plus').stopObserving('change');

    // clean paypal radio buttons when Dom:load
    if ($('p_method_paypal_plus').checked) {
        $('p_method_paypal_plus').checked = false;
    }

    // listen all click events and a condition to id element
    $('p_method_paypal_plus').on('click', 'input.radio', function (event, element) {
        //Form.validate('onestepcheckout-general-form');
        $('paypal_plus_instalments').show();
    });

    $$("#paypal_plus_instalments").invoke('observe', 'change', function (event, element) {
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
        new Ajax.Request('/onestepcheckout/ajax/saveAddress/', {
            method: 'post',
            parameters: $('onestepcheckout-general-form').serialize(true),
            async: false,
            onSuccess: function (response) {
                if (event.target.id == 'paypal_plus_instalments') {
                    try {
                        // ask if already have a object.btnCheckout
                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnInovartiCheckout = $('onestepcheckout-place-order-button');

                            // set element on object and get a onclick method or clone a button
                            EsmartPaypalBrasilBtnContinue.setElement(BtnInovartiCheckout, false);

                            // active addEventListeners
                            EsmartPaypalBrasilPPPlus.init();
                        }

                        if (EsmartPaypalBrasilPPPlus.iframe_loaded === null) {
                            // generate a IFrame
                            EsmartPaypalBrasilPPPlus.generateIframe();
                            var iframe = EsmartPaypalBrasilPPPlus.generateIframe();

                            if (iframe == true) {
                              //  OSCPayment.savePayment();
                            }
                        }

                    } catch (e) {
                        var message = "Checkout Inovarti com problema.";

                        if ($('p_method_paypal_plus').checked) {
                            $('p_method_paypal_plus').checked = false;
                        }

                        alert(message);
                    }
                }
            }
        });

    });

}else{

//document.observe("dom:loaded", function() {
    // necessary to moip inovarti updates :/ clean all observing of radio button paypal
    $('p_method_paypal_plus').stopObserving('change');

    // clean paypal radio buttons when Dom:load
    //if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
    if($('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };
    //}
    // listen all click events and a condition to id element
    $('p_method_paypal_plus').on('click', 'input.radio', function(event, element) {
        //Form.validate('onestepcheckout-general-form');

        new Ajax.Request('/onestepcheckout/ajax/saveAddress/', {
            method: 'post',
            parameters: $('onestepcheckout-general-form').serialize(true),
            async: false,
            onSuccess: function (response) {
                if(event.target.id == 'p_method_paypal_plus') {
                    try{
                        // ask if already have a object.btnCheckout
                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnInovartiCheckout = $('onestepcheckout-place-order-button');

                            // set element on object and get a onclick method or clone a button
                            EsmartPaypalBrasilBtnContinue.setElement(BtnInovartiCheckout, false);

                            // active addEventListeners
                            EsmartPaypalBrasilPPPlus.init();
                        }

                        // generate a IFrame
                        EsmartPaypalBrasilPPPlus.generateIframe();

                    }catch(e) {
                        var message = "Checkout Inovarti com problema.";
                        if( $('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };
                        console.log(message);
                        alert(message);
                    }
                }
            }
        });

    });
//});
}