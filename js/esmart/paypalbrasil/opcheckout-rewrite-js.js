Checkout.prototype._onSectionClick
    = Checkout.prototype._onSectionClick.wrap(function(parentMethod) {
    var section = $(Event.element(event).up('.section'));
    if (section.hasClassName('allow')) {
        if(section.readAttribute('id') == 'opc-payment'){
            console.log("READY");
            new Ajax.Request('/paypalbrasil/onepage/updateDropdown/', {
                method: 'post',
                async: false,
                onSuccess: function (response) {
                    $("checkout-payment-method-load").remove();
                    $("co-payment-form").insert("<div id='checkout-payment-method-load' class='sp-methods'> </div>");
                    $("checkout-payment-method-load").insert(response.responseJSON.html);
                }
            });
        }
        Event.stop(event);
        this.gotoSection(section.readAttribute('id').replace('opc-', ''), false);
        return false;
    }
});