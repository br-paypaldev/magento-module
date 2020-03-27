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
$('aw-onestepcheckout-shipping-method').on('click', 'input.radio', function () {
    new Ajax.Request('/paypalbrasil/express/generateUrl/', {
        method: 'post',
        parameters: {addresschanged: 1},
        async: false,
        onSuccess: function (response) {
            $('p_method_paypal_plus').checked = false;
            awOSCPayment.savePayment();
            $('p_method_paypal_plus').checked = false;
        }
    });
});
//IF PAYMENT METHOD CHANGES, CLEAN THE COST AND DISCOUNT OF GRAND TOTAL
$('aw-onestepcheckout-payment-method').on('click', 'input.radio', function () {

    //VERIFICAR HÁ ALGUM SHIPPING MARCADO, SENÃO OBRIGAR A MARCAR.
    var check = document.querySelector('input[name = "shipping_method"]:checked');
    if(check == null){
        alert("Por Favor Selecione um Metodo de Envio!");
        if ($('p_method_checkmo')) {
            $('p_method_checkmo').checked = false;
        }
        if ($('p_method_banktransfer')) {
            $('p_method_banktransfer').checked = false;
        }
        if ($('p_method_cashondelivery')) {
            $('p_method_cashondelivery').checked = false;
        }
        $('p_method_paypal_plus').checked = false;
    }else{
        if(!$('p_method_paypal_plus').checked){
            new Ajax.Request('/paypalbrasil/express/generateUrl/', {
                method: 'post',
                parameters: {paymentchanged: 1},
                async: false,
                onSuccess: function (response) {
                    awOSCPayment.savePayment();
                }
            });
        }
    }
});

//SE ALGO MUDAR NA PARTE DO BILLING, LIMPAR OS JUROS, DESCONTOS E ATUALIZAR O OSC
$$("input[name='billing[firstname]']").invoke('observe', 'change', function (event) {
    billingChanged();
});
$$("input[name='billing[lastname]']").invoke('observe', 'change', function (event) {
    billingChanged();
});
$$("input[name='billing[taxvat]']").invoke('observe', 'change', function (event) {
    billingChanged();
});
$$("input[name='billing[email]']").invoke('observe', 'change', function (event) {
    billingChanged();
});
$$("input[name='billing[street][]']").invoke('observe', 'change', function (event) {
    billingChanged();
});
$$("input[name='billing[city]']").invoke('observe', 'change', function (event) {
    billingChanged();
});
$$("input[name='billing[postcode]']").invoke('observe', 'change', function (event) {
    billingChanged();
});
$$("input[name='billing[telephone]']").invoke('observe', 'change', function (event) {
    billingChanged();
});

$('billing:region_id').observe('click', function() {
    $('p_method_paypal_plus').checked = false;
    billingChanged();
});
$('billing:country_id').observe('click', function() {
    $('p_method_paypal_plus').checked = false;
    billingChanged();
});

function billingChanged() {
    new Ajax.Request('/paypalbrasil/express/generateUrl/', {
        method: 'post',
        parameters: {addresschanged: 1},
        async: false,
        onSuccess: function (response) {
            setTimeout(function(){
                $('p_method_paypal_plus').checked = false;
                awOSCPayment.savePayment();
            }, 4000);

        }
    });
}

// ---------------------------------------------------------------------------------



window.onload = function(){
    // clean paypal radio buttons when Dom:load
    if ($('p_method_paypal_plus').checked) {
        $('p_method_paypal_plus').checked = false;
    }
};

if(configIframe.installments == true) {
    $('checkout-payment-method-load').on('click', 'input.radio', function () {
        $('paypal_plus_instalments').setValue(0);
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
    });

    // Check if address fields are fulfill and print error message in case there is something wrong
    $$("input[name='payment[method]']").invoke('observe', 'change', function (event) {
        if(event.target.id == 'p_method_paypal_plus' ){
            $('paypal_plus_instalments').show();
            $('payment_form_paypal_plus').show();
            $('paypal_plus_instalments').removeAttribute("disabled");
        }else{
            $('payment_form_paypal_plus').hide();
            $('paypal_plus_iframe').update('').removeAttribute('style');
        }
    });



    // $('p_method_paypal_plus').on('click', function () {
    //     new Ajax.Request('/paypalbrasil/onepage/updateDropdown/', {
    //         method: 'post',
    //         async: false,
    //         onSuccess: function (response) {
    //             $("checkout-payment-method-load").remove();
    //             $("aw-onestepcheckout-payment-method").insert("<div id='checkout-payment-method-load'> </div>");
    //             $("checkout-payment-method-load").insert(response.responseJSON.html);
    //             $("p_method_paypal_plus").setAttribute( "checked","checked" );
    //             $('paypal_plus_instalments').show();
    //         }
    //     });
    // });

    // listen all click events and a condition to id element
    $$("#paypal_plus_instalments").invoke('observe', 'change', function (event) {
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;

        // Save Billing
        new Ajax.Request('\/onestepcheckout\/ajax\/saveAddress\/', {
            method: 'post',
            parameters: $('aw-onestepcheckout-general-form').serialize(true),
            async: false,
            onSuccess: function (response) {
                if (event.target.id == 'paypal_plus_instalments') {
                    try {
                        //check if all fields are ok
                        if (!awOSCForm.validate()) {
                            resetIframe();
                            EsmartPaypalBrasilPPPlus.iframe_loaded = null;
                            event.preventDefault();
                            return false;
                        } 

                        if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                            // button checkout
                            var BtnAwCheckout = $('aw-onestepcheckout-place-order').select('button').first();
        
                            BtnAwCheckout.addClassName('aw-onestepcheckout-place-order-button');
                            $$('.aw-onestepcheckout-place-order-please-wait').first().setStyle({'display':'none'});
        
                            // set element on object and get a onclick method
                            EsmartPaypalBrasilBtnContinue.setElement(BtnAwCheckout, false);

                            //copy the clne button to Aheadworks place order button
                            awOSCForm.placeOrderButton = EsmartPaypalBrasilBtnContinue.btnClonadoCheckout;
                            awOSCForm.granTotalAmount = awOSCForm.placeOrderButton.select(".aw-onestepcheckout-place-order-amount").first();
                            awOSCForm.granTotalAmountProcess = awOSCForm.placeOrderButton.select(".aw-onestepcheckout-place-order-process").first();
        
                            // active addEventListeners
                            EsmartPaypalBrasilPPPlus.init();
                        }

                        // Keep installments while load IFrame
                        $('paypal_plus_instalments').show();

                        // Remove disabled attribute from PayPal Payment
                        $("p_method_paypal_plus").removeAttribute("disabled");
                        
                        $('esmart-paypalbrasil-btn-submit').observe('click', function() {
                            $$('.aw-onestepcheckout-place-order-please-wait').first().setStyle({'display':'inline-block', 'top':'120'});
                        });

                    } catch (e) {
                        var message = "Checkout AheadWorks com problema.";
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

}else{
    // clean paypal radio buttons when Dom:load
    if($('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };

    // listen all click events and a condition to id element
    $('p_method_paypal_plus').on('click', 'input.radio', function(event, element) {
        if(event.target.id == 'p_method_paypal_plus') {

            try {
                // ask if already have a object.btnCheckout
                if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                    // button checkout
                    var BtnAwCheckout = $('aw-onestepcheckout-place-order').select('button').first();

                    BtnAwCheckout.addClassName('aw-onestepcheckout-place-order-button');
                    $$('.aw-onestepcheckout-place-order-please-wait').first().setStyle({'display':'none'});

                    // set element on object and get a onclick method
                    EsmartPaypalBrasilBtnContinue.setElement(BtnAwCheckout, false);

                    //copy the clne button to Aheadworks place order button
                    awOSCForm.placeOrderButton = EsmartPaypalBrasilBtnContinue.btnClonadoCheckout;
                    awOSCForm.granTotalAmount = awOSCForm.placeOrderButton.select(".aw-onestepcheckout-place-order-amount").first();
                    awOSCForm.granTotalAmountProcess = awOSCForm.placeOrderButton.select(".aw-onestepcheckout-place-order-process").first();

                    // active addEventListeners
                    EsmartPaypalBrasilPPPlus.init();
                }
                $('paypal_plus_loading').show();
                $('payment_form_paypal_plus').show();
                // generate a IFrame
                EsmartPaypalBrasilPPPlus.generateIframe();

            $('esmart-paypalbrasil-btn-submit').observe('click', function() {
                $$('.aw-onestepcheckout-place-order-please-wait').first().setStyle({'display':'inline-block', 'top':'120'});
            });

            } catch(e) {
                var message = "Checkout AheadWorks com problema.";
                if( $('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };
                console.log(message);
                alert(message);
            }
        }
    });

    // clean a iframe and inputs.radio of payments
    $$("div.step-title").each( function(el) {
        el.observe('click', function(){
            $('checkout-step-payment').select('input.radio').each(function(el){ el.checked = false});
            $$("ul#payment_form_paypal_plus")[0].hide();
            $('paypal_plus_iframe').update('').removeAttribute('style');

        })
    });
}