/**
 * This JS file is part of the gm-contact-email plugin.
 */

(function($) {

    /**
     * If response from gm-contact-email.php is 1, display thank you message. Else, parse JSON object
     * into different error messages and display them.
     *
     * @param   {int|string}    resp    Response from gm_contact_ajax() in gm-contact-email.php
     * @param   {HTMLElement}   form    The HTML form element being submitted.
     */
    function set_response_message(resp, form)
    {
        var success_elm = "<div class='response'>Thank you! Your message has been sent.</div>";

        if (resp === '1')
        {
            // Email was sent, response from gm_contact_email_send === 1
            form.fadeOut(500, function () {
                $(this).remove();
            });

            $(document).find('.entry-content').append(success_elm);
        } else {
            // Email was *not* sent. Response from gm_email_send is a JSON object.

            var errors = JSON.parse(resp);

            remove_all_errors();
            set_error_messages(errors);

        }
    }

    /**
     * Sets error messages from the JSON encoded array when the form submitted fails validation at the gm_contact_email_send
     * class. Maybe unnecessary since JS will have its own form validation.
     *
     * @param   {Object}    error_obj   Object created from parsing the JSON error message from set_response_message
     */
    function set_error_messages(error_obj)
    {
        for (var key in error_obj)
        {
            if (error_obj.hasOwnProperty(key))
            {
                var error_msg = error_obj[key];
                var error_elm = '<div class="error">' + error_msg + '</div>';

                var label_elm = $('label[for='+ key +']');

                label_elm.append(error_elm);
            }
        }
    }

    /**
     * Remove errors if present before a new form submit
     */
    function remove_all_errors() {
        $('.error').each(function () {
            $(this).remove();
        })
    }

    // Submitting the form.
    $('#contact').find('input[type="submit"]').on('click', function(e){

        e.preventDefault();

        var form = $(this).closest('form');

        var form_data = form.serialize();

        var data = {
            action: 'gm_contact_ajax',
            form_data: form_data
        };

        $.ajax({
            type: 'POST',
            url: gm_contact.ajaxurl,
            data: data,
            success: function(resp)
            {
                set_response_message(resp, form);
            },
            error: function(resp)
            {
                console.log(resp)
            }

        }); // end ajax

        return false;
    });
})(jQuery);
