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
//document.querySelector('input[name="shipping_method"]:checked').checked = false;

window.onload = function(){
    // clean paypal radio buttons when Dom:load
    if ($('p_method_paypal_plus').checked) {
        $('p_method_paypal_plus').checked = false;
    }
};

//IF SHIPPING METHOD CHANGES, CLEAN THE COST AND DISCOUNT OF GRAND TOTAL
$('onestepcheckout-shipping-method').on('click', 'input.radio', function () {
    console.log("SHIPPING METHOD MUDOU");
    new Ajax.Request('/paypalbrasil/express/generateUrl/', {
        method: 'post',
        parameters: {addresschanged: 1},
        async: false,
        onSuccess: function (response) {
            console.log("SHIPPING METHOD CLEAN SUCEESS");
            $('p_method_paypal_plus').checked = false;
            OSCPayment.forcesavePayment();
            OSCShipment.switchToMethod(OSCShipment.currentMethod, true);
            $('p_method_paypal_plus').checked = false;
        }
    });
});

//IF PAYMENT METHOD CHANGES, CLEAN THE COST AND DISCOUNT OF GRAND TOTAL
$('onestepcheckout-payment-method').on('click', 'input.radio', function () {
    if(!$('p_method_paypal_plus').checked){
        console.log("PAYMENT METHOD MUDOU");
        $('payment_form_paypal_plus').hide();
        new Ajax.Request('/paypalbrasil/express/generateUrl/', {
            method: 'post',
            parameters: {paymentchanged: 1},
            async: false,
            onSuccess: function (response) {
                console.log("PAYMENT METHOD CLEAN SUCEESS");
                OSCPayment.forcesavePayment();
                OSCPayment.switchToMethod(OSCPayment.currentMethod, true);
            }
        });
    }
});

if(configIframe.installments == true) {
    // Form.validate('onestepcheckout-general-form');
    $('p_method_paypal_plus').stopObserving('change');

    // listen all click events and a condition to id element
    $('p_method_paypal_plus').on('click', 'input.radio', function (event, element) {
        Form.validate('onestepcheckout-general-form');
        $('paypal_plus_instalments').setValue(0);
        $('paypal_plus_instalments').show();
        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
    });

    $('p_method_paypal_plus').on('click', function () {

        console.log("PAYMENT METHOD MUDOU");
        new Ajax.Request('/paypalbrasil/express/generateUrl/', {
            method: 'post',
            parameters: {paymentchanged: 1},
            async: false,
            onSuccess: function (response) {
                console.log("PAYMENT METHOD CLEAN SUCEESS");
                OSCPayment.forcesavePayment();
                OSCPayment.switchToMethod(OSCPayment.currentMethod, true);
            }
        });

        new Ajax.Request('/paypalbrasil/onepage/updateDropdown/', {
            method: 'post',
            async: false,
            onSuccess: function (response) {
                $("onestepcheckout-payment-method").remove();
                $("onestepcheckout-payment-method-wrapper").insert("<div id='onestepcheckout-payment-method'> </div>");
                $("onestepcheckout-payment-method").insert(response.responseJSON.html);
                $("p_method_paypal_plus").setAttribute( "checked","checked" );
                $('payment_form_paypal_plus').show();
                $('paypal_plus_instalments').show();
            }
        });
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

                        }

                    } catch (e) {
                        var message = "Checkout Inovarti com problema.";

                        if ($('p_method_paypal_plus').checked) {
                            $('p_method_paypal_plus').checked = false;

                        }
                        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
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