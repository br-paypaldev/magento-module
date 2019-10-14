document.observe("dom:loaded", function() {

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

			paypal.checkout.setup(PayPalLightboxConfig.merchantid, {
				environment: PayPalLightboxConfig.environment,
				button: IDS,
				click: function (e) { 
					e.preventDefault();	

					/* to incontext Works needs a product on cart */
					if ($('product_addtocart_form')) {
						new Ajax.Request($('product_addtocart_form').action,{              
			               	method: 'post',	                     
			                async: false			               
			            });
					}

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
});