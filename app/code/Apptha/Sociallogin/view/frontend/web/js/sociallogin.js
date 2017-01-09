var jQ = jQuery.noConflict();
document.addEventListener("DOMContentLoaded", function(event) {
	
	var i; 
    try {
    	
    	/**
		 * Get all href links
		 */
        var links = document.links;

        for (i = 0; i < links.length; i++) {
        	/**
    		 * login links
    		 */
        	if (links[i].href.search('/customer/account/login/') != -1 && links[i].href.indexOf("#") == -1 ) {
                links[i].href = 'javascript:apptha_sociallogin();';
            }
           
            /**
    		 * wishlist link 
    		 */
            if (links[i].href.search('/wishlist/') != -1 && links[i].href.indexOf("#") == -1 ) {
                links[i].href = 'javascript:apptha_sociallogin();';
            }
            
            
            /**
    		 * sign up
    		 */
            if (links[i].href.search('/customer/account/create') != -1 && links[i].href.indexOf("#") == -1  ) {
                links[i].href = 'javascript:apptha_sociallogin(1);';
            }

 	if (links[i].href.search('/marketplace/seller/create') != -1 && links[i].href.indexOf("#") == -1  ) {
                links[i].href = 'javascript:apptha_sociallogin(2);';
            }
	if (links[i].href.search('/marketplace/seller/login') != -1 && links[i].href.indexOf("#") == -1  ) {
                links[i].href = 'javascript:apptha_sociallogin(2);';
            }
            /**
    		 * background fade element.
    		 */
           
        }
    	jQ('.bg_fade').hide();


        if( window.location.href.search('/customer/account/login/') != -1 ||  window.location.href.search('/customer/account/create/') != -1 )
        	{
        	apptha_sociallogin();
        	}
        /**
		 * bind in checkout field.
		 */
    }

    catch (exception)
    {
        alert(exception);
    }

});


var sociallogin = angular.module('sociallogin', []);
sociallogin.controller('signInvalidateCtrl', function($scope) {
	$scope.reset = function() {
		$scope.myForm.$setPristine();
		$scope.social_tiw_login.$setPristine();
		};
});

function doSociallogin(action,form,formSuccess,progress_image) {

		
	    var exceptionDiv = jQ('#'+formSuccess);
	    var query = jQ(form).serialize();
	    var url = action;
	    jQ('#'+progress_image).show();  
	    jQ.post(action,query , function (response) {
		    jQ('#'+progress_image).hide();  
	    	if(typeof response =='object')
	    	{
	    		var responseEmail = response.email;
	    		response = response.message;
	    	}
	    	 var pattern_url = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	         var url_value = pattern_url.test(response);
	         if (url_value == true && response.indexOf("already") == -1)
	         {
	             window.location.href = response;
	         } 
	         else if (response.indexOf("already") >= 0) 
	         {
		      
	         	exceptionDiv.text(response);
	        	/*jQ('#forget_password').val(responseEmail);
	         	jQ('.form-create-account').hide();
	         	jQ('#forget_password_div').show();*/
	         }
	         else {
	        	 
	        	
	           //form.reset();
	        	 exceptionDiv.css("color","red");
	           exceptionDiv.text(response);
	           
	         }
	    });


	
}
function apptha_sociallogin(register)
{
	jQ('.bg_fade').show();
	jQ('#header_logo_Div').show();
	jQ('.signin-form').show();
	jQ('.form-create-account').hide();
	jQ('.new_account_create').show();
	jQ('#forget_password_div').hide();
	jQ('.already_have_account').hide();
	/**
	 * Background fade is visible
    /
   
    /**
	 * Setting the window width and height
	 */
   
    if(register==1){
    	jQ('.already_have_account').show();
    	jQ('.form-create-account' ).css( 'position:absolute');
    	jQ('.new_account_create').hide();
    	jQ('.form-create-account').show();
    	jQ('.signin-form').hide();
      }

    if(register==2){
    	jQ('#isseller').val(1);
    	jQ('#is-seller').val(1);
    	jQ('#twitter-seller').val(1);
    	jQ('#facebook-seller').val(1);
    	jQ('#google-seller').val(1);
    }
    
}

function apptha_socialloginclose (){
	jQ('#forget_password_error').text('');
	jQ('#errors').text('');
	jQ('#forget_password_form')[0].reset();
	jQ('.form-create-account')[0].reset();
	jQ('.signin-form')[0].reset();
	jQ('#forget_password_form')[0].reset();
	jQ('#forget_password_div').hide();
	jQ('#header_logo_Div').hide();
	jQ('#formSuccess').hide();
	jQ('.bg_fade').hide();
	jQ('#twitter_block').hide();

}
/**
 * Show / hide forms as user clicks
 */
function show_hide_socialforms(frmid) {
	if(frmid == "1") {
    	jQ('#twitter_block').hide();
		jQ('.already_have_account').hide();
		jQ('.new_account_create').show();
    	jQ('.form-create-account').hide();
    	jQ('.signin-form').show();
    	jQ('#forget_password_div').hide();
        return false;
	  } else if (frmid == "2") {         
    	/**
		 * Create Account form
		 */
    	jQ('#twitter_block').hide();
		jQ('.already_have_account').show();
    	jQ('.new_account_create').hide();
    	jQ('.form-create-account').show();
    	jQ('.signin-form').hide();
    	jQ('#forget_password_div').hide();
        return false;
    } else if (frmid == "3") {                
    	/**
		 * Forget password form
		 */
    	jQ('#forget_password_div').slideToggle('slow');
    	jQ('#twitter_block').hide();
        return false;
    } else if (frmid == "4") {
    	/**
    	 * Twitter block
    	 */
		jQ('.already_have_account').show();
    	jQ('.new_account_create').hide();
    	jQ('#twitter_block').show();
    	jQ('.form-create-account').hide();
    	jQ('.signin-form').hide();
    	jQ('#forget_password_div').hide();
        return false;
    }
}