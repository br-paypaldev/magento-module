// Override nextStep method
IdeCheckoutvm.prototype.nextStep = function(transport) {
    console.log(transport);
    var response = null;
    if (transport && transport.responseText) {
        try {
            response = eval("(" + transport.responseText + ")")
        } catch (e) {
            response = {}
        }
    }
    if (response.redirectUrl) {
        this._beforeRedirectClient();
        this.isSuccess = true;

        if (response.redirectUrl.match('/paypal/express/')) {

            let urlConnect = response.redirectUrl;

            paypal.checkout.initXO();

            new Ajax.Request(urlConnect,{
                method: 'get',
                async: true,
                crossDomain: false,
                onSuccess: function (token) {
                    let url = token.request.url;
                    paypal.checkout.startFlow(url);
                },
                onFailure: function (responseData, textStatus, errorThrown) {
                    alert("Error in ajax post"+responseData.statusText);
                    //Gracefully Close the minibrowser in case of AJAX errors
                    paypal.checkout.closeFlow();
                }
            });
        }else {
            document.location.href = response.redirectUrl;
        }
    }
    if (response.error) {
        this.buttonCheckoutvm.enable();
        if (response.errorMessage.message) {
            this._showErrorMessage(response.errorMessage.message)
        } else {
            this._clearMessage()
        }
        if (response.errorMessage.errorFields) {
            var ideCheckoutValidationInst = new IdeCheckoutvmValidation(this.form);
            ideCheckoutValidationInst.fillFormErrors(response.errorMessage.errorFields)
        }
    }
}