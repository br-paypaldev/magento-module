/**
 * Magento Custom by BSeller
 *
 *  :) design/frontend/base/default/layout/esmart/paypalbrasil.xml
 *
 *  AUX js/esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js
 *
 * @package     js
 */

$('paypal_plus_instalments').show();

EsmartPaypalBrasilPPPlus.osc = true;

if (!EsmartPaypalBrasilBtnContinue.btnCheckout) {
    // button checkout
    // var BtnCheckoutMoip = $('onestepcheckout_place_order_button').select('button').first();
    var BtnCheckoutMoip = document.getElementById('checkout-onepage-buttom');

    // set element on object and get a onclick method
    EsmartPaypalBrasilBtnContinue.setElement(BtnCheckoutMoip, true);

    // active addEventListeners
    EsmartPaypalBrasilPPPlus.init();
}

if(EsmartPaypalBrasilPPPlus.iframe_loaded === null){
    // generate a IFrame
    EsmartPaypalBrasilPPPlus.generateIframe();
}

// clean a iframe and inputs.radio of payments
$$("div.step-title").each( function(el) {
    el.observe('click', function(){
        $('checkout-step-payment').select('input.radio').each(function(el){ el.checked = false});
        $$("ul#payment_form_paypal_plus")[0].hide();
        $('paypal_plus_iframe').update('').removeAttribute('style');

    })
});

$$("#paypal_plus_instalments").invoke('observe', 'change', function (event) {
    setTimeout(function(){
        savePaymentMethod();
    }, 5000);
});

updateOrderMethod = function() {
    EsmartPaypalBrasilBtnContinue.executeOriginalEvents(); //This code set Payer ID on Payment Additional Information before place the order
    event.preventDefault();
    if($('p_method_paypal_plus').checked) {
        prePurchase();
    }else if($('p_method_paypal_express').checked){
        new Ajax.Request('/paypal/express/checkPaypalInContext/',{
            method: 'get',
            async: false,
            onSuccess: function (response) {
                //IN-CONTEXT
                if (response.responseJSON == "1") {
                    let urlConnect = '/paypal/express/start/';
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
                } else { //REDIRECT
                    let urlConnect = '/paypal/express/start/';
                    new Ajax.Request(urlConnect, {
                        method: 'POST',
                        async: true,
                        crossDomain: false,
                        onSuccess: function (token) {
                            let url = token.request.url;
                            location.href = encodeURI(url);
                        },
                        onFailure: function (responseData, textStatus, errorThrown) {
                            alert("Error in ajax post" + responseData.statusText);
                        },
                    });
                }
            }
        });
    }else{
        purchase();
    }

};

function prePurchase(){
    setTimeout(function(){
        if(EsmartPaypalBrasilPayerIdOk){ //Se o PayerId ja foi setado e esta tudo ok, entao pode finalizar o pedido
            purchase();
            EsmartPaypalBrasilPayerIdOk = 0;
        }else{
            prePurchase();
        }

    }, 2000);
}

function purchase(){
    jQuery.ajax({
        type: "POST",
        url: updateordermethodurl,
        data: jQuery("#onestep_form").serialize(),
        beforeSend: function(){
            loadingInButton('start');
        },
        success: function(result) {
            loadingInButton('stop');
            if(result.error == 1){
                jQuery(".erros_cadastro_valores").append('<li> - '+result.error_messages+'</li>');
                visibilyloading('end');
                jQuery('#ErrosFinalizacao').modal();
                return this;
            }
            if (result.redirect) {
                location.href = encodeURI(result.redirect);
                return this;
            } else{
                window.location.href = encodeURI(checkoutsuccess);
            }
        },
        fail:function() {
            location.reload();
        }

    });
}

saveShippingMethod = function(){
    jQuery.ajax('/paypalbrasil/express/generateUrl/', {
        method: 'post',
        data: {addresschanged: 1},
        async: true,
        beforeSend: function() {
            jQuery("#payment-progress").removeClass('hidden-it');
            jQuery("#co-payment-form").addClass('hidden-it');
            loadingInButton('start');
        },
        success: function (response) {
            jQuery.ajax({
                type: "POST",
                url: url_save_shipping_method,
                async: true,
                evalScripts:true,
                data: jQuery("#onestep_form").serialize(),
                beforeSend: function() {
                    jQuery("#payment-progress").removeClass('hidden-it');
                    jQuery("#co-payment-form").addClass('hidden-it');
                    loadingInButton('start');
                },
                success: function(result){
                    jQuery("#payment-progress").addClass('hidden-it');
                    jQuery("#co-payment-form").removeClass('hidden-it');
                    loadingInButton('stop');
                    if(result){
                        if(result.success){
                            jQuery("#payment-method-available").html(result.html);
                            jQuery('html, body').animate({
                                scrollTop: jQuery("#meio-de-pagamento").offset().top
                            }, 2000);
                        }
                        if(result.totals){
                            jQuery('#totals').html(result.totals);
                        }
                    }
                },
                complete: function() {
                    jQuery("#payment-progress").addClass('hidden-it');
                    jQuery("#co-payment-form").removeClass('hidden-it');
                    loadingInButton('stop');
                }
            });
            return this;
        }
    });
};

savePaymentMethod = function() {
    if($('p_method_paypal_plus').checked) {
        jQuery.ajax({
            url: url_save_payment_metthod,
            evalScripts: true,
            type: "POST",
            data: jQuery("#onestep_form").serialize(),
            beforeSend: function () {
                loadingInButton('start');
            },
            success: function (result) {
                loadingInButton('stop');
                if (result) {
                    if (result.success) {
                        jQuery('#totals').html(result.html);
                    }
                }
            },
            complete: function () {
                loadingInButton('stop');
            },
        });
        return this;
    } else {
        jQuery.ajax('/paypalbrasil/express/generateUrl/', {
            method: 'post',
            data: {paymentchanged: 1},
            async: true,
            beforeSend: function () {
                loadingInButton('start');
            },
            success: function (response) {
                jQuery.ajax({
                    url: url_save_payment_metthod,
                    evalScripts: true,
                    type: "POST",
                    data: jQuery("#onestep_form").serialize(),
                    beforeSend: function () {
                        loadingInButton('start');
                    },
                    success: function (result) {
                        loadingInButton('stop');
                        if (result) {
                            if (result.success) {
                                jQuery('#totals').html(result.html);
                            }
                        }
                    },
                    complete: function () {
                        loadingInButton('stop');
                    },
                });
                return this;
            }
        });
    }
};
