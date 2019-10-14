window.addEventListener("load",function() {
    EsmartPaypalBrasilPPPlus.cleanIframe();
});

var EsmartPaypalBrasilPPPlus, EsmartPaypalBrasilBtnContinue;

if (typeof EsmartPaypalBrasilPPPlus !== 'object') {

    EsmartPaypalBrasilPPPlus = {
        ppp               : null,
        base_url          : null,
        iframe_data_saved : null, // It ensures that the savePaypalInformation ajax was executed
        iframe_loaded     : null,
        osc               :  null, // check if have OSC

        cleanIframe:function() {
            var bns = document.getElementsByTagName("button");
            for (var i = 0; i < bns.length; i++) {
                bns[i].addEventListener("click", function() {
                    EsmartPaypalBrasilPPPlus.iframe_loaded = null;
                });
            }
        },

        generateIframe : function () {
                var installment = $("paypal_plus_instalments").getValue();
                obj = JSON.parse(window.paypal);

                if (obj.installments == false) {
                    this.iframe_loaded = true;
                    loadIFrame('noInstalments', this.base_url, installment);
                    return true;
                } else {
                    $('paypal_plus_instalments').show();
                    if (installment >= 1) {
                        if( EsmartPaypalBrasilPPPlus.iframe_loaded == null){
                            this.iframe_loaded = true;
                            loadIFrame('instalments', this.base_url, installment);
                            return true;
                        }
                    }
                }

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

        switchCase: function (err) {
            switch (err) {
                case "INTERNAL_SERVICE_ERROR": //javascript fallthrough
                case "SOCKET_HANG_UP": //javascript fallthrough
                case "socket hang up": //javascript fallthrough
                case "connect ECONNREFUSED": //javascript fallthrough
                case "connect ETIMEDOUT": //javascript fallthrough
                case "UNKNOWN_INTERNAL_ERROR": //javascript fallthrough
                case "fiWalletLifecycle_unknown_error": //javascript fallthrough
                case  "Failed to decrypt term info": //javascript fallthrough
                case "RESOURCE_NOT_FOUND": //javascript fallthrough
                case "INTERNAL_SERVER_ERROR":
                    EsmartPaypalBrasilPPPlus.showAlert("Ocorreu um erro inesperado, por favor tente novamente.");
                    this.generateIframe();
                    break;
                case "RISK_N_DECLINE": //javascript fallthrough
                case "NO_VALID_FUNDING_SOURCE_OR_RISK_REFUSED": //javascript fallthrough
                case "TRY_ANOTHER_CARD": //javascript fallthrough
                case "NO_VALID_FUNDING_INSTRUMENT":
                    EsmartPaypalBrasilPPPlus.showAlert ("Seu pagamento não foi aprovado. Por favor utilize outro cartão, caso o problema persista entre em contato com o PayPal (0800-047-4482)."); //pt_BR
                    this.generateIframe();
                    break;
                case "CARD_ATTEMPT_INVALID":
                    EsmartPaypalBrasilPPPlus.showAlert ("Ocorreu um erro inesperado, por favor tente novamente."); //pt_BR
                    this.generateIframe();
                    break;
                case "INVALID_OR_EXPIRED_TOKEN":
                    EsmartPaypalBrasilPPPlus.showAlert ("A sua sessão expirou, por favor tente novamente."); //pt_BR
                    this.generateIframe();
                    break;
                case "CHECK_ENTRY":
                    EsmartPaypalBrasilPPPlus.showAlert ("Por favor revise os dados de Cartão de Crédito inseridos."); //pt_BR
                    this.generateIframe();
                    break;
                default: //unknown error & reload payment flow
                    EsmartPaypalBrasilPPPlus.showAlert ("Ocorreu um erro inesperado, por favor tente novamente."); //pt_BR
                    this.generateIframe();
            }
        },

        handler : function (event) {

            var data = event.data;

            if (data.isJSON()) {
                data = data.evalJSON();
            }

            if (typeof data.cause !== 'undefined') {
                var err = ppplusError = data.cause.replace (/['"]+/g,"");
                this.switchCase(err);
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
                if(this.btnCheckout !=  'undefined'){
                    this.btnClonadoCheckout.writeAttribute('disabled');
                }
            }else{
                this.btnCheckout.writeAttribute('disabled');
            }
            $("p_method_paypal_plus").writeAttribute('disabled');
        },

        enable: function () {
            if (!this.CloneOrOnclick) {
                if(this.CloneOrOnclick != 'undefined') {
                    this.btnClonadoCheckout.removeAttribute("disabled");
                }
            }else{
                this.btnCheckout.removeAttribute("disabled");
            }
            $("p_method_paypal_plus").removeAttribute("disabled");
        },

        executeOriginalEvents: function () {
            if ($("p_method_paypal_plus").checked) {
                EsmartPaypalBrasilPPPlus.iframe_loaded = null;

                if(EsmartPaypalBrasilPPPlus.osc == false) {
                    obj = JSON.parse(window.paypal);
                    if (obj.installments == true) {
                        $('paypal_plus_instalments').setValue(0);
                    }
                }

                if(EsmartPaypalBrasilPPPlus.osc == true){
                    if (EsmartPaypalBrasilPPPlus.iframe_data_saved) {
                        if (!this.CloneOrOnclick) {
                            this.btnClonadoCheckout.up().insert(this.btnCheckout).click();
                            this.btnCheckout.hide();
                            this.btnCheckout.click();
                        }else{
                            eval(this.clickEventMethod);
                        }
                    }else{
                        EsmartPaypalBrasilPPPlus.ppp.doContinue();
                        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
                    }
                }else{
                    if (EsmartPaypalBrasilPPPlus.iframe_data_saved) {
                        if (!this.CloneOrOnclick) {
                            this.btnClonadoCheckout.up().insert(this.btnCheckout).click();
                            this.btnCheckout.hide();
                            this.btnCheckout.click();
                        }else{
                            eval(this.clickEventMethod);
                        }
                    }else{
                        EsmartPaypalBrasilPPPlus.ppp.doContinue();
                        EsmartPaypalBrasilPPPlus.iframe_loaded = null;
                    }
                }

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

function loadIFrame(type,base_url, installment) {

    EsmartPaypalBrasilPPPlus.iframe_data_saved = false;

    $$("div#paypal_plus_loading").invoke('show');
    EsmartPaypalBrasilBtnContinue.disable();

    $('paypal_plus_iframe').update('').removeAttribute('style');

    var url = base_url + 'paypalbrasil/express/generateUrl';
    var dataForm = $("payment_form_paypal_plus").up('form').serialize() + '&installment=' + installment;

    new Ajax.Request(url ,{
        method: 'post',
        parameters: dataForm,
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
                if(type == 'noInstalments'){
                    EsmartPaypalBrasilPPPlus.ppp = PAYPAL.apps.PPP({
                        placeholder: "paypal_plus_iframe",
                        buttonLocation: "outside",
                        language: "pt_BR",
                        country: "BR",
                        approvalUrl: responseContent.success.approvalUrl,
                        mode: responseContent.success.mode,
                        payerFirstName: responseContent.success.payerFirstName,
                        payerLastName: responseContent.success.payerLastName,
                        payerEmail: responseContent.success.payerEmail,
                        payerTaxId: responseContent.success.payerTaxId,
                        payerPhone: responseContent.success.payerPhone,
                        payerTaxIdType: responseContent.success.payerTaxIdType,
                        rememberedCards: responseContent.success.rememberedCards
                    });
                }else {
                    EsmartPaypalBrasilPPPlus.ppp = PAYPAL.apps.PPP({
                        placeholder: "paypal_plus_iframe",
                        buttonLocation: "outside",
                        language: "pt_BR",
                        country: "BR",
                        approvalUrl: responseContent.success.approvalUrl,
                        mode: responseContent.success.mode,
                        payerFirstName: responseContent.success.payerFirstName,
                        payerLastName: responseContent.success.payerLastName,
                        payerEmail: responseContent.success.payerEmail,
                        payerTaxId: responseContent.success.payerTaxId,
                        payerPhone: responseContent.success.payerPhone,
                        payerTaxIdType: responseContent.success.payerTaxIdType,
                        rememberedCards: responseContent.success.rememberedCards,
                        merchantInstallmentSelectionOptional: responseContent.success.merchantInstallmentSelectionOptional,
                        merchantInstallmentSelection:responseContent.success.merchantInstallmentSelection
                    });
                }
                EsmartPaypalBrasilBtnContinue.enable();
                $$("div#paypal_plus_loading")[0].hide();

                if(obj.oscCheckout == 'inovarti'){
                    OSCPayment.savePayment();
                }
            }
        }
    });
}

function callIframe() {
    return false;
    EsmartPaypalBrasilPPPlus.generateIframe();
}
