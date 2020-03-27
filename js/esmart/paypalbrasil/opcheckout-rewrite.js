Payment.prototype.save
    = Payment.prototype.save.wrap(function(parentMethod){
    if (checkout.loadWaiting!=false) return;
    var validator = new Validation(this.form);
    if (this.validate() && validator.validate()) {
        checkout.setLoadWaiting('payment');
        let b = this.saveUrl;
        let met = this.currentMethod;
        if(met === "paypal_express") {
            let urlConnect = "/paypal/express/start/";

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
        } else {
            new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    }
});