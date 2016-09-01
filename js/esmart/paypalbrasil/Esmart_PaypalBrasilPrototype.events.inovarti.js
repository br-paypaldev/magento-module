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
    // necessary to moip inovarti updates :/ clean all observing of radio button paypal
    $('p_method_paypal_plus').stopObserving('change');

    // clean paypal radio buttons when Dom:load
    //if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
        if($('p_method_paypal_plus').checked){ $('p_method_paypal_plus').checked = false };
    //}
    // listen all click events and a condition to id element 
    $('p_method_paypal_plus').on('click', 'input.radio', function(event, element) {
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
    });
//});