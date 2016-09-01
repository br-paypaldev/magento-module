var EsmartPaypalBrasilPPPlus, EsmartPaypalBrasilBtnContinue;

if (typeof EsmartPaypalBrasilPPPlus !== 'object') {
    EsmartPaypalBrasilPPPlus = {
        ppp             : null,
        base_url        : null,
        iframe_data_saved : null, // It ensures that the savePaypalInformation ajax was executed

        generateIframe : function () {
            $$("div#paypal_plus_loading").invoke('show');

            EsmartPaypalBrasilBtnContinue.disable();

            $('paypal_plus_iframe').update('').removeAttribute('style');
            
            var url = this.base_url + 'paypalbrasil/express/generateUrl';

            new Ajax.Request(url ,{
                    method: 'post',
                    parameters: $("payment_form_paypal_plus").up('form').serialize(true),
                    async: true,
                    onSuccess: function (response) {

                    var responseContent = response.responseText.evalJSON();

                    if (typeof responseContent.error !== 'undefined') {
                        if (responseContent.error) {
                            EsmartPaypalBrasilPPPlus.showAlert(responseContent.message);
                        }

                        $$("div#paypal_plus_loading")[0].hide();
                        $$("input[type=radio][name='payment[method]']").each(function(el){ el.checked = false});
                        return false;
                    }

                    if (responseContent.success.approvalUrl === null) {
                        $$("div#paypal_plus_loading")[0].hide();
                        EsmartPaypalBrasilPPPlus.showAlert();
                        $$("input[type=radio][name='payment[method]']").each(function(el){ el.checked = false});
                        return false;
                    }

                    if (typeof responseContent.success !== 'undefined') {
                        EsmartPaypalBrasilPPPlus.ppp = PAYPAL.apps.PPP({
                            placeholder        : "paypal_plus_iframe",
                            buttonLocation     : "outside",
                            language           : "pt_BR",
                            country            : "BR",
                            approvalUrl        : responseContent.success.approvalUrl,
                            mode               : responseContent.success.mode,
                            payerFirstName     : responseContent.success.payerFirstName,
                            payerLastName      : responseContent.success.payerLastName,
                            payerEmail         : responseContent.success.payerEmail,
                            payerTaxId         : responseContent.success.payerTaxId,
                            payerPhone         : responseContent.success.payerPhone,
                            payerTaxIdType     : responseContent.success.payerTaxIdType,
                            rememberedCards    : responseContent.success.rememberedCards
                        });
                        
                        EsmartPaypalBrasilBtnContinue.enable();
                        $$("div#paypal_plus_loading")[0].hide();
                        return true;
                    }
                }
                });

        },

        init : function () {
            if (window.addEventListener) {
                window.removeEventListener('message', esmartPaypalBrasilHandler);
                window.addEventListener('message', esmartPaypalBrasilHandler, false);
                return true;
            }

            if (window.attachEvent) {
                window.detachEvent("message", esmartPaypalBrasilHandler);
                window.attachEvent("message", esmartPaypalBrasilHandler);
                return true;
            }

            return false;
        },

        handler : function (event) {

            var data = event.data;

            if (data.isJSON()) {
               data = data.evalJSON();
            }

            switch (data.action) {

                case 'checkout':

                    if (data.result.payer.funding_option.funding_sources[0].funding_instrument_type != "PAYMENT_CARD") {
                        break;
                    }
                    var dataPost = {
                        rememberedCards : data.result.rememberedCards,
                        payerId         : data.result.payer.payer_info.payer_id,
                        payerStatus     : data.result.payer.status,
                        checkoutId      : data.result.id,
                        checkoutState   : data.result. state,
                        cards           : []
                    };
                    
                    if (undefined != data.result.payer.funding_option) {
                        for (key in data.result.payer.funding_option.funding_sources) {
                            if (Number(key) == key) {
                                var cardData = {
                                    termQty     : 1,
                                    termValue   : data.result.payer.funding_option.funding_sources[key].amount.value,
                                    total       : data.result.payer.funding_option.funding_sources[key].amount.value
                                };

                                if (typeof data.result.term !== 'undefined') {
                                    cardData.termQty    = data.result.term.term;
                                    cardData.termValue  = data.result.term.monthly_payment.value;
                                }

                                dataPost.cards.push(JSON.stringify(cardData));
                            }
                        }
                    }

                    var url = this.base_url + 'paypalbrasil/express/savePaypalInformation';
                    new Ajax.Request(url,{
                        method: 'post',
                        parameters  : dataPost,
                        async       : false,
                        onSuccess : function (response) {

                            var responseContent = response.responseText.evalJSON();
                            if (responseContent.error) {
                                EsmartPaypalBrasilPPPlus.iframe_data_saved = false;
                                EsmartPaypalBrasilPPPlus.showAlert(responseContent.message);
                            }else{
                                EsmartPaypalBrasilPPPlus.iframe_data_saved = true;
                                EsmartPaypalBrasilBtnContinue.executeOriginalEvents();
                            }
                        }
                    });

                    break;
            }
        },

        showAlert : function(message) {
            alert(message);
            EsmartPaypalBrasilBtnContinue.enable();
        }
    };
}

if (typeof EsmartPaypalBrasilBtnContinue !== 'object') {
     EsmartPaypalBrasilBtnContinue = {
        clickEventMethod: null,
        btnCheckout: null,
        CloneOrOnclick: null,
        btnClonadoCheckout: null,

        disable: function () {
            if (!this.CloneOrOnclick) {
                this.btnClonadoCheckout.writeAttribute('disabled');
            }else{
                this.btnCheckout.writeAttribute('disabled');
            }
            $("p_method_paypal_plus").writeAttribute('disabled');
        },

        enable: function () {
            if (!this.CloneOrOnclick) {
                this.btnClonadoCheckout.removeAttribute("disabled");
            }else{
                this.btnCheckout.removeAttribute("disabled");
            }
            $("p_method_paypal_plus").removeAttribute("disabled");
        },

        executeOriginalEvents: function () {
             if ($("p_method_paypal_plus").checked) {
                if (EsmartPaypalBrasilPPPlus.iframe_data_saved) {
                    if (!this.CloneOrOnclick) {
                        this.btnClonadoCheckout.up().insert(this.btnCheckout).click();
                        this.btnCheckout.hide();
                        this.btnCheckout.click();
                    }else{eval(this.clickEventMethod); }
                    
                }else{  EsmartPaypalBrasilPPPlus.ppp.doContinue(); }
             }else{
                if (!this.CloneOrOnclick) {
                        this.btnClonadoCheckout.up().insert(this.btnCheckout).click();
                        this.btnCheckout.hide();
                        this.btnCheckout.click();
                    }else{eval(this.clickEventMethod); }
             }
            
        },

        setElement: function (originalBtnElement, CloneOrOnclick) {
            if (typeof CloneOrOnclick === 'undefined') {
                CloneOrOnclick = false;
            }              

            if (CloneOrOnclick && !this.clickEventMethod) {
                this.btnCheckout = originalBtnElement;
                this.clickEventMethod = this.btnCheckout.getAttribute('onclick');
                this.btnCheckout.removeAttribute('onclick');
                this.CloneOrOnclick = CloneOrOnclick;

                this.btnCheckout.observe('click', function(event){                    
                    EsmartPaypalBrasilBtnContinue.executeOriginalEvents();
                    event.preventDefault();                   
                });

            }else{

                this.btnCheckout = originalBtnElement;
                this.btnClonadoCheckout = originalBtnElement.clone(true);
                this.btnClonadoCheckout.writeAttribute('id', 'esmart-paypalbrasil-btn-submit');
                originalBtnElement.up().insert(this.btnClonadoCheckout);
                originalBtnElement.remove();
                this.CloneOrOnclick = CloneOrOnclick;

                this.btnClonadoCheckout.observe('click', function(event){                    
                    EsmartPaypalBrasilBtnContinue.executeOriginalEvents();
                    event.preventDefault();                   
                });
            }
        }
     };
}

function esmartPaypalBrasilHandler(event) {
    EsmartPaypalBrasilPPPlus.handler(event);
}
