/**
 * Magento Custom by PayPalBrasil
 *
 *  :) design/frontend/base/default/layout/esmart/paypalbrasil.xml
 *
 *  AUX js/esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js
 *
 * @package     js
 */

var configIframe = JSON.parse(window.paypalPlusBr);
EsmartPaypalBrasilPPPlus.osc = true;

//IF SHIPPING METHOD CHANGES, CLEAN THE COST AND DISCOUNT OF GRAND TOTAL
$('checkout-shipping-method-load').on('click', 'input.radio', function () {
    if ($('p_method_paypal_plus').checked) {
        $('p_method_paypal_plus').checked = false;
    }
    new Ajax.Request('/paypalbrasil/express/generateUrl/', {
        method: 'post',
        parameters: {addresschanged: 1},
        async: false,
        onSuccess: function (response) {
            checkout.updateSections(['payment-method']);
        }
    });

});

$('checkout-payment-method-load').on('click', 'input.radio', function () {
    checkout.updateSections('review');
});

//SE ALGO MUDAR NA PARTE DO BILLING, LIMPAR OS JUROS, DESCONTOS E ATUALIZAR O OSC
// $$("input[name='billing[firstname]']").invoke('observe', 'change', function (event) {
//     billingChanged();
// });
//
// function billingChanged() {
//     new Ajax.Request('/paypalbrasil/express/generateUrl/', {
//         method: 'post',
//         parameters: {addresschanged: 1},
//         async: false,
//         onSuccess: function (response) {
//             $('p_method_paypal_plus').checked = false;
//             checkout.updateSections(['payment-method']);
//             checkout.updateSections('review');
//         }
//     });
// }

// ---------------------------------------------------------------------------------

if(configIframe.installments == true) {
//document.observe("dom:loaded", function() {
// necessary to fireckeout crazy updates :/ clean all observing of radio button paypal
    $('p_method_paypal_plus').stopObserving('click');
    
// clean paypal radio buttons when Dom:load
    document.addEventListener("DOMContentLoaded", function() {
        if ($('p_method_paypal_plus').checked) {
            $('p_method_paypal_plus').checked = false;
        }
    });

    $('checkout-payment-method-load').on('click', 'input.radio', function () {
        $('paypal_plus_instalments').setValue(0);
        $('paypal_plus_instalments').show();
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
    });

    // Check if address fields are fulfill and print error message in case there is something wrong
    $$("input[name='payment[method]']").invoke('observe', 'change', function (event) {
        var ValidationMessage = 'Prezado cliente, favor preencher e/ou validar os dados dos passos anteriores antes de selecionar a Forma de Pagamento. Caso o problema persista por favor entre em contato.';

        if(event.target.id === 'p_method_paypal_plus' ){
            if (!checkout.validate()) {
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
                $("checkout-payment-method-load").remove();
                $("payment-method").insert("<div id='checkout-payment-method-load'> </div>");
                $("checkout-payment-method-load").insert(response.responseJSON.html);
                $("p_method_paypal_plus").setAttribute( "checked","checked" );
                $('paypal_plus_instalments').show();
            }
        });
    });

// listen all click events and a condition to id element
    $$("#paypal_plus_instalments").invoke('observe', 'change', function (event) {
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
        // Save Billing
        new Ajax.Request('/firecheckout/index/saveBilling/', {
            method: 'post',
            parameters: $('firecheckout-form').serialize(true),
            async: false,
            onSuccess: function (response) {
                if (event.target.id == 'paypal_plus_instalments') {

                    try {
                        // check if all fields are ok
                        if (!checkout.validate()) {
                            resetIframe();
                            $$('#checkout-payment-method-load .messages').each(
                                function(el) {
                                    el.hide();
                                }
                            );
                            event.preventDefault();
                            return false;
                        }

                        // ask if already have a object.btnCheckout
                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnFireCheckout = $('review-buttons-container').select('button').first();

                            // has a observing in this button that call before onclick :/
                            BtnFireCheckout.stopObserving('click');

                            // set element on object and get a onclick method
                            EsmartPaypalBrasilBtnContinue.setElement(BtnFireCheckout, true);

                            EsmartPaypalBrasilBtnContinue.clickEventMethod = EsmartPaypalBrasilBtnContinue.clickEventMethod.replace("return false;", "");

                            // active addEventListeners
                            EsmartPaypalBrasilPPPlus.init();
                        }

                        // generate a IFrame
                        if (EsmartPaypalBrasilPPPlus.iframe_loaded === null) {
                            // generate a IFrame
                            EsmartPaypalBrasilPPPlus.generateIframe();
                        }

                        // Keep installments while load IFrame
                        $('paypal_plus_instalments').show();
                    } catch (e) {
                        var message = "Checkout Firecheckout com problema.";
                        if ($('p_method_paypal_plus').checked) {
                            $('p_method_paypal_plus').checked = false
                        }
                        ;
                        console.log(message);
                        alert(message);
                    }
                    setTimeout(function(){
                            checkout.updateSections('review');
                        }
                        ,100);
                }
            }
        });

    });
//});

}else{
//document.observe("dom:loaded", function() {
// necessary to fireckeout crazy updates :/ clean all observing of radio button paypal
    $('p_method_paypal_plus').stopObserving('click');
    $('checkout-payment-method-load').stopObserving('click');

// clean paypal radio buttons when Dom:load
    if ($('p_method_paypal_plus').checked) {
        $('p_method_paypal_plus').checked = false;
    }

// Check if address fields are fulfill and print error message in case there is something wrong
    $$("input[name='payment[method]']").invoke('observe', 'change', function (event) {
        var ValidationMessage = 'Prezado cliente, favor preencher e/ou validar os dados dos passos anteriores antes de selecionar a Forma de Pagamento. Caso o problema persista por favor entre em contato.';

        if(event.target.id === 'p_method_paypal_plus' ){
            if (!checkout.validate()) {
                resetIframe();
                $("payment_form_paypal_plus").hide();
                EsmartPaypalBrasilPPPlus.showAlert(ValidationMessage);
            }
        }
    });

// listen all click events and a condition to id element
    $('checkout-payment-method-load').on('click', 'input.radio', function (event, element) {
        // Save Billing
        new Ajax.Request('/firecheckout/index/saveBilling/', {
            method: 'post',
            parameters: $('firecheckout-form').serialize(true),
            async: false,
            onSuccess: function (response) {
                if (event.target.id == 'p_method_paypal_plus') {

                    try {
                        // check if all fields are ok
                        if (!checkout.validate()) {
                            resetIframe();
                            $$('#checkout-payment-method-load .messages').each(
                                function(el) {
                                    el.hide();
                                }
                            );
                            event.preventDefault();
                            return false;
                        }

                        // ask if already have a object.btnCheckout
                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnFireCheckout = $('review-buttons-container').select('button').first();

                            // has a observing in this button that call before onclick :/
                            BtnFireCheckout.stopObserving('click');

                            // set element on object and get a onclick method
                            EsmartPaypalBrasilBtnContinue.setElement(BtnFireCheckout, true);

                            EsmartPaypalBrasilBtnContinue.clickEventMethod = EsmartPaypalBrasilBtnContinue.clickEventMethod.replace("return false;", "");

                            // active addEventListeners
                            EsmartPaypalBrasilPPPlus.init();
                        }

                        // generate a IFrame
                        EsmartPaypalBrasilPPPlus.generateIframe();

                    } catch (e) {
                        var message = "Checkout Firecheckout com problema.";
                        if ($('p_method_paypal_plus').checked) {
                            $('p_method_paypal_plus').checked = false
                        }
                        ;
                        console.log(message);
                        alert(message);
                    }
                }
            }
        });

    });
//});
}