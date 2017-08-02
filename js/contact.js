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

            /*  Email was *not* sent. Response from gm_email_send is a JSON object.
             *  - In normal circumstances the JS should not make it this far since
             *  JS validation is performed on the form before submitting.
             */

            var errors = JSON.parse(resp);

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

    /**
     * Checks the form inputs for errors.
     *
     * @param form_data
     * @returns {boolean}
     */
    function validate_form(form_data) {

        var form_input_obj = create_form_input_obj(form_data);

        var error_obj = create_error_obj(form_input_obj);

        if (error_obj === true)
        {
            return true;
        }

        set_error_messages(error_obj);
    }

    /**
     * Creates a JS object from the serialized form input data. Used to easily
     * evaluate the input values.
     *
     * @param form_data
     * @returns {{name: string, email: string, company: string, message: string}}
     */
    function create_form_input_obj(form_data) {
        var form_input_obj = {
            'name'    : '',
            'email'   : '',
            'company' : '',
            'message' : '' };

        var form_array = form_data.split('&');

        for (var i = 0 ; i < form_array.length ; ++i)
        {
            var current_array_elm = form_array[i];
            var split_current = current_array_elm.split('=');

            if (split_current.length === 2)
            {
                form_input_obj[split_current[0]] = split_current[1];
            }
        }

        return form_input_obj
    }

    /**
     * Validates each form_input_obj property and builds a new object with properties representing
     * specific error messages. If no errors were found, the function returns true. Else, returns the
     * error_obj object.
     *
     * @param   {Object}    form_input_obj  The object created by create_form_input_obj()
     * @returns {bool|Object}   True if no errors are found. Else returns error_obj object.
     */
    function create_error_obj(form_input_obj) {
        var error_obj = {};

        var error_flag = false;

        if (!validate_text(form_input_obj.name)) {
            error_flag = true;
            error_obj.name = 'Name cannot be blank';
        }
        if (!validate_email(form_input_obj.email)) {
            error_flag = true;
            error_obj.email = 'Please make sure the email field is in valid format'
        }
        if (!validate_text(form_input_obj.email)) {
            error_flag = true;
            error_obj.email = 'Email cannot be blank';
        }
        if (!validate_text(form_input_obj.message)) {
            error_flag = true;
            error_obj.message = 'Message cannot be blank';
        }

        if (error_flag === false)
        {
            return true;
        } else {
            return error_obj;
        }
    }

    /**
     * Checks to see if var string is whitespace.
     *
     * @param   string      The text from the input data being evaluated.
     * @returns {boolean}   True if var string is not whitespace. Else, returns false.
     */
    function validate_text(string)
    {
        return string.trim() !== '';
    }

    /**
     * Checks to make sure var email_string is in valid format for an email address.
     *
     * @param   email_string
     * @returns {boolean}   Returns true if var email_string is a valid email address. Else false.
     */
    function validate_email(email_string)
    {
        email_string = decodeURIComponent(email_string);
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(email_string);
    }

    // Submitting the form.
    $('#contact').find('input[type="submit"]').on('click', function(e){

        e.preventDefault();

        remove_all_errors();

        var form = $(this).closest('form');

        var form_data = form.serialize();

        var form_is_valid = validate_form(form_data);

        if (form_is_valid === true)
        {
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
        }
        return false;
    });
})(jQuery);
