// Override onComplete method
OnestepcheckoutForm.prototype.onComplete = function (transport) {
    if (transport && transport.responseText) {
        try {
            response = eval('(' + transport.responseText + ')');
        } catch (e) {
            response = {};
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
                setLocation(response.redirect);
            }
            return;
        }
        if (response.success) {
            setLocation(this.successUrl);
        } else if ("is_hosted_pro" in response && response.is_hosted_pro) {
            this.popup.showPopupWithDescription(response.update_section.html);
            var iframe = this.popup.contentContainer.select('#hss-iframe').first();
            iframe.observe('load', function () {
                $('hss-iframe').show();
                $('iframe-warning').show();
            });
        } else {
            var msg = response.messages || response.message;
            if (typeof (msg) == 'object') {
                msg = msg.join("\n");
            }
            if (msg) {
                alert(msg);
            }
            this.enablePlaceOrderButton();
            this.hidePleaseWaitNotice();
            this.hideOverlay();
        }
    }
}