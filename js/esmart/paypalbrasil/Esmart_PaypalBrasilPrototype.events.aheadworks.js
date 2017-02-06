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
// clean paypal radio buttons when Dom:load
if($('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };

// listen all click events and a condition to id element
$('p_method_paypal_plus').on('click', 'input.radio', function(event, element) {
    if(event.target.id == 'p_method_paypal_plus') {

        try {
            // ask if already have a object.btnCheckout
            if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                // button checkout
                var BtnAwCheckout = $('aw-onestepcheckout-place-order-button');

                BtnAwCheckout.addClassName('aw-onestepcheckout-place-order-button');
                $$('.aw-onestepcheckout-place-order-please-wait').first().setStyle({'display':'none'});

                // set element on object and get a onclick method
                EsmartPaypalBrasilBtnContinue.setElement(BtnAwCheckout, false);

                // active addEventListeners
                EsmartPaypalBrasilPPPlus.init();
            }

            // generate a IFrame
            EsmartPaypalBrasilPPPlus.generateIframe();

        $('esmart-paypalbrasil-btn-submit').observe('click', function() {
            $('esmart-paypalbrasil-btn-submit').disable();
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