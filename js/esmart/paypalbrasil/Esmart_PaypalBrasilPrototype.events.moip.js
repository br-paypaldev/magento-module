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
    // necessary to moip crazy updates :/ clean all observing of radio button paypal
    $('p_method_paypal_plus').stopObserving('click');

    // clean paypal radio buttons when Dom:load
    if( $('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };

    // listen all click events and a condition to id element 
    $('checkout-payment-method-load').on('click', 'input.radio', function(event, element) {
        if(event.target.id == 'p_method_paypal_plus') {

            try {    
                // ask if already have a object.btnCheckout
                if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
                    // button checkout
                    var BtnMoipCheckout = $('checkout-onepage-buttom');

                    // has a observing in this button that call before onclick :/
                    BtnMoipCheckout.stopObserving('click');

                    // set element on object and get a onclick method or clone a button
                    EsmartPaypalBrasilBtnContinue.setElement(BtnMoipCheckout, false);

                    // active addEventListeners
                    EsmartPaypalBrasilPPPlus.init();
                }

                // generate a IFrame           
                EsmartPaypalBrasilPPPlus.generateIframe();

            }catch(e) {
                var message = "Checkout Moip com problema.";
                if( $('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };
                console.log(message);
                alert(message);
            }
        }
    });
//});