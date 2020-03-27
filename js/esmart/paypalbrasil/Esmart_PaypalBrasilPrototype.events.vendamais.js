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
$('checkout-shipping-method-load').stopObserving('click');
$('checkout-shipping-method-load').on('click', 'input.radio', function () {
    // setTimeout(function(){
    //     document.getElementById("p_method_paypal_plus").checked = false;
    // }, 5000);
    new Ajax.Request('/paypalbrasil/express/generateUrl/', {
        method: 'post',
        parameters: {addresschanged: 1},
        async: false,
        onSuccess: function (response) {
            uncheckPaypalPlus();
            // payment.update();
        }
    });
});

function uncheckPaypalPlus(){
    console.log("uncheck paypal plus");
    if (document.getElementById("p_method_paypal_plus").checked) {
        setTimeout(function () {
            document.getElementById("p_method_paypal_plus").checked = false;
        }, 2000);
    }
}

//IF PAYMENT METHOD CHANGES, CLEAN THE COST AND DISCOUNT OF GRAND TOTAL
$('checkout-payment-method-load').stopObserving('click');
$('checkout-payment-method-load').on('click', 'input.radio', function () {
    if(!$('p_method_paypal_plus').checked){
        new Ajax.Request('/paypalbrasil/express/generateUrl/', {
            method: 'post',
            parameters: {paymentchanged: 1},
            async: false,
            onSuccess: function (response) {
                payment.update();
            }
        });
    }
});

function checkFormValidation() {
    if (!billingValidation.validate()) {
        resetIframe();
        $$('#checkout-payment-method-load .messages').each(
            function(el) {
                el.hide();
            }
        );
        event.preventDefault();
        return false;
    }
}

// Create var to validate Ipagare form
var billingValidation = new Validation($("ide-checkout-form"));

function is_checked() {
    if ($$("#checkout-step-shipping-method").length){
        return !$$("input[name='shipping_method']:checked").length;
    }
}

window.onload = function(){
    // clean paypal radio buttons when Dom:load
    setTimeout(function(){
        if ($('p_method_paypal_plus').checked) {
            $('p_method_paypal_plus').checked = false;
        }
    }, 2000);
};

if(configIframe.installments == true) {

    // Update payment review table when the page is loaded or refreshed
    payment.update();
    
    // Check if address fields are fulfill and print error message in case there is something wrong
    $$("input[name='payment[method]']").invoke('observe', 'change', function (event) {
        var ValidationMessage = 'Prezado cliente, favor preencher e/ou validar os dados dos passos anteriores antes de selecionar a Forma de Pagamento. Caso o problema persista por favor entre em contato.';

        if(event.target.id === 'p_method_paypal_plus' ){
            if (!billingValidation.validate() || is_checked()) {
                resetIframe();
                $("payment_form_paypal_plus").hide();
                EsmartPaypalBrasilPPPlus.showAlert(ValidationMessage);
            }else{
                $('paypal_plus_instalments').setValue(0);
                $('paypal_plus_instalments').show();
                $('paypal_plus_iframe').update('').removeAttribute('style');
                EsmartPaypalBrasilPPPlus.iframe_loaded = null;
            }
        }
    });

    // listen all click events and a condition to id element
    $$("#paypal_plus_instalments").invoke('observe', 'change', function (event) {
        $$("div#paypal_plus_loading").invoke('show');
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
        $("p_method_paypal_plus").removeAttribute("disabled");
        $('paypal_plus_iframe').update('').removeAttribute('style');
        checkFormValidation();

        // Save Billing
        new Ajax.Request('/idecheckoutvm/index/saveOrder/', {
            method: 'post',
            parameters: $('ide-checkout-form').serialize(true),
            async: false,
            onSuccess: function (response) {
                if (event.target.id == 'paypal_plus_instalments') {
                    try {

                        // ask if already have a object.btnCheckout
                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnVendaMaisCheckout = $('idecheckoutvm-place-order-button');

                            // has a observing in this button that call before onclick :/
                            BtnVendaMaisCheckout.stopObserving('click');

                            // set element on object and get a onclick method
                            EsmartPaypalBrasilBtnContinue.setElement(BtnVendaMaisCheckout, true);

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
                }
            }
        });
        $$("div#paypal_plus_loading")[0].hide();
    });

}else{
    // necessary to vendamais updates :/ clean all observing of radio button paypal
    $('p_method_paypal_plus').stopObserving('change');

    // clean paypal radio buttons when Dom:load
    if( $('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };

    // Check if address fields are fulfill and print error message in case there is something wrong
    $$("input[name='payment[method]']").invoke('observe', 'change', function (event) {
        var ValidationMessage = 'Prezado cliente, favor preencher e/ou validar os dados dos passos anteriores antes de selecionar a Forma de Pagamento. Caso o problema persista por favor entre em contato.';

        if(event.target.id === 'p_method_paypal_plus' ){
            if (!billingValidation.validate() || is_checked()) {
                resetIframe();
                $("payment_form_paypal_plus").hide();
                EsmartPaypalBrasilPPPlus.showAlert(ValidationMessage);
            }
        }
    });

    // listen all click events and a condition to id element
    $('p_method_paypal_plus').on('click', 'input.radio', function(event, element) {
        if(event.target.id == 'p_method_paypal_plus') {
            try {
                // ask if already have a object.btnCheckout
                if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                    // button checkout
                    var BtnVendaMaisCheckout = $('idecheckoutvm-place-order-button');

                    // set element on object and get a onclick method
                    EsmartPaypalBrasilBtnContinue.setElement(BtnVendaMaisCheckout, false);

                    // active addEventListeners
                    EsmartPaypalBrasilPPPlus.init();
                }

                // generate a IFrame
                EsmartPaypalBrasilPPPlus.generateIframe();

            }catch(e) {
                var message = "Checkout Venda Mais com problema.";
                if( $('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };
                console.log(message);
                alert(message);
            }
        }
    });
//});
}