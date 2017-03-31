var EsmartPaypalBrasilInContext;

EsmartPaypalBrasilInContext = {
	//method after save start In-Context Checkout Experience	
	reinitDefaultMethods: function(){
		//rewrite on save method in Payment class.
		if (typeof Payment === "undefined" ){
			return;
		}
		Payment.prototype.save = Payment.prototype.save.wrap(function(save) {
			
            var validator = new Validation(this.form);
            if (this.validate() && validator.validate()) {
                
            	if (payment.currentMethod=='paypal_express'){
            		 var request = new Ajax.Request(
        		             this.saveUrl,
        		             {
        		                 method:'post',
        		                 onComplete: function(){},//remove default method like placeholder
        		                 onSuccess: function(){},//remove default method like placeholder
        		                 onFailure: checkout.ajaxFailure.bind(checkout),
        		                 parameters: Form.serialize(this.form)
        		             }
        		         );
            	}else{
            		save(); //return default method
            	}            	
            }
            
		});		
	}
};

if (typeof window.paypalCheckoutReady === "undefined"){	

	window.paypalCheckoutReady = function() {
		
		if (typeof PayPalLightboxConfig == "undefined"){
			return;
		}
		if(typeof PayPalLightboxConfig != "object"){
			PayPalLightboxConfig = JSON.parse(PayPalLightboxConfig);
		}
		if (PayPalLightboxConfig.isActive==0){
			return;
		}

		if (typeof IDS === "undefined" || IDS === null){
			IDS = new Array();
		}

		if ($('checkout-onepage-buttom')!=undefined){
			IDS.push('checkout-onepage-buttom');
		}

		paypal.checkout.setup(PayPalLightboxConfig.merchantid, {
			environment: PayPalLightboxConfig.environment,
			button: IDS,
			click: function (e) { 
				
				if(!e.target.id.match('ec_shortcut') && !$(e.target).up().id.match('ec_shortcut')){
					if (payment.currentMethod != 'paypal_express'){
						return;
					}
				}				

				e.preventDefault();				
				Event.stop(e);
				var urlConnect = PayPalLightboxConfig.setExpressCheckout

				paypal.checkout.initXO();
							    					    	
		    	new Ajax.Request(urlConnect,{              
	               	method: 'get',	                     
	                async: true,
	                crossDomain: false,
	               
	                onSuccess: function (token) {
	                	
	                	if (token.responseText.indexOf('cart') != -1  || token.responseText.indexOf('login')!= -1){
	                		paypal.checkout.closeFlow();
	                		setLocation(token.responseText);
	                		
	                	}else{
	                		var url = paypal.checkout.urlPrefix + token.responseText;               
	                        paypal.checkout.startFlow(url);
	                	}
	                    
	                },
	                onFailure: function (responseData, textStatus, errorThrown) {
	                    alert("Error in ajax post"+responseData.statusText);
	                    //Gracefully Close the minibrowser in case of AJAX errors
	                    paypal.checkout.closeFlow();
	                }
	            });
			}
		});			
	};
};


document.observe("dom:loaded", function() {

	if (typeof(PayPalLightboxConfig)!="undefined"){
		if(typeof(PayPalLightboxConfig) != "object"){
			PayPalLightboxConfig = JSON.parse(PayPalLightboxConfig);
		}
		if (PayPalLightboxConfig.isActive==0){
			return;
		}
		EsmartPaypalBrasilInContext.reinitDefaultMethods();
		
	}	
});