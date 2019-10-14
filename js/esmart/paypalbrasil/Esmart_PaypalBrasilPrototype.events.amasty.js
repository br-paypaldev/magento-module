/**
 * Magento Custom by BSeller
 *
 *  :) design/frontend/base/default/layout/esmart/paypalbrasil.xml
 *
 *  AUX js/esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js
 *  
 * @package     js
 */
var configIframe = JSON.parse(window.paypalPlusBr);
EsmartPaypalBrasilPPPlus.osc = true;

$('checkout-shipping-method-load').on('click', 'input.radio', function () {
    console.log("SHIPPING METHOD MUDOU");
    new Ajax.Request('/paypalbrasil/express/generateUrl/', {
        method: 'post',
        parameters: {addresschanged: 1},
        async: false,
        onSuccess: function (response) {
            console.log("SHIPPING METHOD CLEAN SUCEESS");
            $('p_method_paypal_plus').checked = false;
            updateCheckout('review');
            $('p_method_paypal_plus').checked = false;
        }
    });
});

function checkFormValidation() {
    if (!amscheckoutForm.validator.validate()) {
        resetIframe();
        $$('#co-payment-form .messages').each(
            function(el) {
                el.hide();
            }
        );
        event.preventDefault();
        return false;
    }
}

//Force review section to update grand total when the user reloads the page
window.onload = function(){
    updateCheckout('payment_method');

    // clean paypal radio buttons when Dom:load
    if ($('p_method_paypal_plus').checked) {
        $('p_method_paypal_plus').checked = false;
    }
};

if(configIframe.installments == true) {
    $('p_method_paypal_plus').stopObserving('click');
    $('co-payment-form-update').stopObserving('click');

    $('co-payment-form-update').on('click', 'input.radio', function () {
        $('paypal_plus_instalments').setValue(0);
        $('paypal_plus_instalments').show();
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;

        if(!$('p_method_paypal_plus').checked){
            new Ajax.Request('/paypalbrasil/express/generateUrl/', {
                method: 'post',
                parameters: {paymentchanged: 1},
                async: false,
                onSuccess: function (response) {
                    console.log("PAYMENT METHOD CLEAN SUCEESS");
                    updateCheckout('payment_method');
                }
            });
        }
    });

    // Check if address fields are fulfill and print error message in case there is something wrong
    $$("input[name='payment[method]']").invoke('observe', 'change', function (event) {
        var ValidationMessage = 'Prezado cliente, favor preencher e/ou validar os dados dos passos anteriores antes de selecionar a Forma de Pagamento. Caso o problema persista por favor entre em contato.';

        if(event.target.id === 'p_method_paypal_plus' ){
            if (!amscheckoutForm.validator.validate()) {
                resetIframe();
                $("payment_form_paypal_plus").hide();
                EsmartPaypalBrasilPPPlus.showAlert(ValidationMessage);
            }
        }
    });

    $('p_method_paypal_plus').on('click', function () {
        new Ajax.Request('/paypalbrasil/onepage/updateDropdown/', {
            method: 'post',
            async: false,
            onSuccess: function (response) {
                $("co-payment-form-update").remove();
                $("co-payment-form").insert("<div id='co-payment-form-update'> </div>");
                $("co-payment-form-update").insert(response.responseJSON.html);
                $("p_method_paypal_plus").setAttribute( "checked","checked" );
                $('paypal_plus_instalments').show();
            }
        });
    });

    // listen all click events and a condition to id element
    $$("#paypal_plus_instalments").invoke('observe', 'change', function (event) {
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
        checkFormValidation();

        // Save Billing
        new Ajax.Request('/amscheckoutfront/onepage/update/', {
            method: 'post',
            parameters: $('amscheckout-onepage').serialize(true),
            async: false,
            onSuccess: function (response) {
                if (event.target.id == 'paypal_plus_instalments') {
                    try {
                        // check if all fields are ok
                        if (!amscheckoutForm.validator.validate()) {
                            resetIframe();
                            $$('#co-payment-form .messages').each(
                                function(el) {
                                    el.hide();
                                }
                            );
                            EsmartPaypalBrasilPPPlus.iframe_loaded = null;
                            event.preventDefault();
                            return false;
                        }

                        // ask if already have a object.btnCheckout
                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnAmastyCheckout = $('amscheckout-submit');

                            // has a observing in this button that call before onclick :/
                            BtnAmastyCheckout.stopObserving('click');

                            // set element on object and get a onclick method
                            EsmartPaypalBrasilBtnContinue.setElement(BtnAmastyCheckout, true);

                            EsmartPaypalBrasilBtnContinue.clickEventMethod = EsmartPaypalBrasilBtnContinue.clickEventMethod.replace("return false;", "");

                            // active addEventListeners
                            EsmartPaypalBrasilPPPlus.init();
                        }

                        // generate a IFrame
                        EsmartPaypalBrasilPPPlus.generateIframe();

                        // Keep installments while load IFrame
                        $('paypal_plus_instalments').show();
                    } catch (e) {
                        var message = "Checkout Amasty com problema.";
                        if ($('p_method_paypal_plus').checked) {
                            $('p_method_paypal_plus').checked = false
                        }
                        ;
                        console.log(message);
                        alert(message);
                    }
                    setTimeout(function(){
                        updateCheckout('payment_method');
                    }, 5000);
                }
            }
        });
    });
}else{
    $('p_method_paypal_plus').stopObserving('change');

    // clean paypal radio buttons when Dom:load
    //if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
    if($('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };
    //}
    // listen all click events and a condition to id element
    $('p_method_paypal_plus').on('click', 'input.radio', function(event, element) {
        //Form.validate('onestepcheckout-general-form');

        new Ajax.Request('/checkout/onepage/index/saveBilling/', {
            method: 'post',
            parameters: $('amscheckout-onepage').serialize(true),
            async: false,
            onSuccess: function (response) {
                if(event.target.id == 'p_method_paypal_plus') {
                    try{
                        // check if all fields are ok
                        checkFormValidation();
                        // ask if already have a object.btnCheckout
                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnAmastyCheckout = $('amscheckout-submit');

                            // has a observing in this button that call before onclick :/
                            BtnAmastyCheckout.stopObserving('click');

                            // set element on object and get a onclick method or clone a button
                            EsmartPaypalBrasilBtnContinue.setElement(BtnAmastyCheckout, true);

                            EsmartPaypalBrasilBtnContinue.clickEventMethod = EsmartPaypalBrasilBtnContinue.clickEventMethod.replace("return false;", "");

                            // active addEventListeners
                            EsmartPaypalBrasilPPPlus.init();
                        }

                        // generate a IFrame
                        EsmartPaypalBrasilPPPlus.generateIframe();

                    }catch(e) {
                        var message = "Checkout Amasty com problema.";
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
