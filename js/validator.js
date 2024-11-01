jQuery(document).ready(function($) {
	
	
	$("form[name='mailpro_shortcode_subscription']").validate({
        rules: {
            mailpro_shortcode_email: {
                required: true,
                email: true }
        },
        messages: {
        mailpro_shortcode_email: {
            required: yourchoice_validator_texts.please_enter_your_email_address,
                email: yourchoice_validator_texts.please_enter_a_valid_email_address
        }
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
          form.submit();
        }
      });

    $("form[name='mailpro_widget_subscription']").validate({
        rules: {
            mailpro_widget_email: {
                required: true,
                email: true }
        },
        messages: {
            mailpro_widget_email: {
                required: yourchoice_validator_texts.please_enter_your_email_address,
                email: yourchoice_validator_texts.please_enter_a_valid_email_address
            }
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
            form.submit();
        }
    });

});