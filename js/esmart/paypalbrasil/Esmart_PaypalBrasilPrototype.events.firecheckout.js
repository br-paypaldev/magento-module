/**
 * Magento Custom by BSeller
 *
 *  :) design/frontend/base/default/layout/esmart/paypalbrasil.xml
 *
 *  AUX js/esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js
 *
 * @package     js
 */
//document.observe("dom:loaded", function() {
// necessary to fireckeout crazy updates :/ clean all observing of radio button paypal
$('p_method_paypal_plus').stopObserving('click');
$('checkout-payment-method-load').stopObserving('click');

// clean paypal radio buttons when Dom:load
if ($('p_method_paypal_plus').checked) {
    $('p_method_paypal_plus').checked = false
}
;

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
