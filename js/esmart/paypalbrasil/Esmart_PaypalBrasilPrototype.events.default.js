/**
 * Magento Custom by BSeller
 *
 *  :) design/frontend/base/default/layout/esmart/paypalbrasil.xml
 *
 *  AUX js/esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js
 *  
 * @package     js
 */
$('p_method_paypal_plus').stopObserving('click');
// clean all radio buttons when Dom:load
$('checkout-payment-method-load').select('input.radio').each(function(el){ el.checked = false});

// listen all click events and a condition to id element 
$('checkout-step-payment').on('click', 'input.radio', function(event, element) {
    if(event.target.id == 'p_method_paypal_plus') {

        try {
            // ask if already have a object.btnCheckout
            if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                // button checkout
                var BtnCheckout = $('payment-buttons-container').select('button').first();

                // set element on object and get a onclick method 
                EsmartPaypalBrasilBtnContinue.setElement(BtnCheckout, true);

                // active addEventListeners
                EsmartPaypalBrasilPPPlus.init();
            }

            // generate a IFrame           
            EsmartPaypalBrasilPPPlus.generateIframe();

        }catch(e) {
            var message = "Checkout Default com problema.";
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