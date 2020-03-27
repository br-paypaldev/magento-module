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

if (configIframe.installments == true) {
//document.observe("dom:loaded", function() {
// necessary to fireckeout crazy updates :/ clean all observing of radio button paypal
    $('p_method_paypal_plus').stopObserving('click');

// clean paypal radio buttons when Dom:load
    document.addEventListener("DOMContentLoaded", function () {
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

        if (event.target.id === 'p_method_paypal_plus') {
            if (!checkout.validate()) {
                resetIframe();
                $("payment_form_paypal_plus").hide();
                EsmartPaypalBrasilPPPlus.showAlert(ValidationMessage);
            }
        }
    });

    // $('p_method_paypal_plus').on('click', function () {
    //     new Ajax.Request('/paypalbrasil/onepage/updateDropdown/', {
    //         method: 'post',
    //         async: false,
    //         onSuccess: function (response) {
    //             $("checkout-payment-method-load").remove();
    //             $("payment-method").insert("<div id='checkout-payment-method-load'> </div>");
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
                                function (el) {
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
                    setTimeout(function () {
                            checkout.updateSections('review');
                        }
                        , 100);
                }
            }
        });

    });
//});

} else {
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

        if (event.target.id === 'p_method_paypal_plus') {
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
                                function (el) {
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

FireCheckout.prototype.setResponse = function (response) {

    if (!response.status || typeof (response.responseText) === 'undefined') {
        // Fix for Firefox: client goes away from the page, while ajax-request
        // is still processing.
        return console.log('Firecheckout: response is not received');
    }

    var serverResponse = response;
    var responseUrl = response.request.url || response.transport.responseURL;
    try {
        response = response.responseText.evalJSON();

        // Fix for IE < EDGE
        if (!responseUrl && response.responseUrl) {
            responseUrl = response.responseUrl;
        }

        document.fire('firecheckout:setResponseBefore', {
            serverResponse: serverResponse,
            response: response,
            url: responseUrl
        });
    } catch (err) {
        alert('An error has occured during request processing. Try again please');
        $(this.form).select('.updating').invoke('removeClassName', 'updating');
        checkout.setLoadWaiting(false);
        checkout.setLoadingButton($$('button.btn-checkout')[0], false);
        return false;
    }

    if (response.redirect) {

        if (response.redirect.match('/paypal/express/')) {

            let urlConnect = response.redirect;

            paypal.checkout.initXO();

            new Ajax.Request(urlConnect, {
                method: 'get',
                async: true,
                crossDomain: false,

                onSuccess: function (token) {
                    let url = token.request.url;
                    paypal.checkout.startFlow(url);
                },
                onFailure: function (responseData, textStatus, errorThrown) {
                    alert("Error in ajax post" + responseData.statusText);
                    //Gracefully Close the minibrowser in case of AJAX errors
                    paypal.checkout.closeFlow();
                }
            });

        } else {
            location.href = response.redirect;
        }

        return true;
    }

    if (response.success) {
        window.location = this.urls.success;
        return;
    } else {
        if (response.captcha) {
            this.updateCaptcha(response.captcha);
        }
        var messages = '';
        if ((messages = this.parseMessages(response))) {
            alert(messages);
        }
    }

    if (response.update_section) {
        if (response.update_section.name && response.update_section.html) {
            // standard magento response
            response.update_section[response.update_section.name] = response.update_section.html;
            delete response.update_section.name;
            delete response.update_section.html;
        }
        for (var i in response.update_section) {
            var el = $('checkout-' + i + '-load');
            if (!el) {
                el = $$('.checkout-' + i + '-load').first();
            }
            if (el) {
                // Collect entered data to restore it after html update
                var data = {},
                    iterators = {};
                el.select('input, select').each(function (input) {
                    if (input.up('#location_table_shqpickup')) {
                        return;
                    }
                    if (input.up('#gls-droppoint-form')) {
                        return;
                    }

                    if (input.type === 'hidden') {
                        // tnt_infostrates fix for hidden inputs
                        if (input.up('#tnt_cp')) {
                            return;
                        }
                        // subscribepro fix for hidden inputs
                        if (input.up('#payment_form_subscribe_pro')) {
                            return;
                        }
                        // shipperhq pickup
                        if (input.id === 'transaction_id') {
                            return;
                        }
                    }

                    var key;
                    if (input.id && input.id.length && input.id.indexOf(' ') === -1) {
                        key = '#' + input.id;
                    } else if (input.name && input.name.length) {
                        if (input.hasClassName('qty')) {
                            // fix to prevent cart qty restore in case of some validation error
                            return;
                        }
                        key = '[name="' + input.name + '"]';
                    } else {
                        return;
                    }

                    if (typeof iterators[key] === 'undefined') {
                        iterators[key] = 0;
                    }
                    iterators[key]++;

                    if ($$(key).length > 1) {
                        key += ':nth-child(' + iterators[key] + ')';
                    }

                    if ('radio' == input.type || 'checkbox' == input.type) {
                        data[key] = input.checked;
                    } else {
                        data[key] = input.getValue();
                    }
                });

                el.update(response.update_section[i]).removeClassName('updating');

                if (i == 'coupon-discount' || i == 'giftcard') {
                    continue;
                }

                for (var j in data) {
                    if (!j) {
                        continue;
                    }
                    var input = el.down(j);
                    if (input) {
                        if ('radio' == input.type || 'checkbox' == input.type) {
                            input.checked = data[j];
                        } else {
                            input.setValue(data[j]);
                        }
                    }
                }
            }

            if (i === 'shipping-method' && typeof shippingMethod !== 'undefined') {
                shippingMethod.addObservers();
            } else if (i === 'review') {
                this.addCartObservers();
            }
        }
    }

    if (response.method) {
        if ('centinel' == response.method) {
            this.showCentinel();
        } else if (0 === response.method.indexOf('billsafe')) {
            lpg.open();
            var form = $('firecheckout-form');
            form.action = BILLSAFE_FORM_ACTION;
            form.submit();
        }

        // SagePay Server Integration
        // else if ('sagepayserver' === response.method) {
        //     var revertStyles = function(el) {
        //         el.setStyle({
        //             height: '500px'
        //         });
        //     };
        //     $('sage-pay-server-iframe').observe('load', function() {
        //         $$('.d-sh-tl, .d-sh-tr').each(function(el) {
        //             el.setStyle({
        //                 height: 'auto'
        //             });
        //             revertStyles.delay(0.03, el);
        //         });
        //     });
        //     sgps_placeOrder();
        // }
        // End of SagePay Server Integration
    }

    if (response.popup) {
        this.showPopup(response.popup);
    } else if (response.afterform) {
        $('firecheckout-form').insert({
            after: response.afterform.content
        });
    } else if (response.body) {
        $(document.body).insert({
            'bottom': response.body.content
        });
    }

    $(this.form).select('.updating').invoke('removeClassName', 'updating');
    checkout.setLoadWaiting(false);
    checkout.setLoadingButton($$('button.btn-checkout')[0], false);

    document.fire('firecheckout:setResponseAfter', {
        serverResponse: serverResponse,
        response: response,
        url: responseUrl
    });

    return false;
}