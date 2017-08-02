<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-includes/class-phpmailer.php');

class gm_contact_email_send {

    protected $honey_pot_is_empty = null;
    protected $is_ajax            = null;

    protected $input_data   = array();
    protected $errors       = array();
    protected $error_msgs   = array();

    protected $email_sent   = false;

    public function __construct()
    {
        $this->honey_pot_is_empty    = isset($_POST['covfefe']) ? false : true;
        $this->is_ajax               = isset($_POST['is_ajax']) ? true : false;

        $this->input_data['name']    = $this->check_text('name', $this->errors);
        $this->input_data['email']   = $this->check_email('email', $this->errors);
        $this->input_data['company'] = $this->check_text('company');
        $this->input_data['message'] = $this->check_text('message', $this->errors);

        $this->error_msgs = $this->build_error_msgs($this->errors);

        $this->email_sent = $this->try_send_email($this->error_msgs, $this->input_data);

        $this->non_ajax_processing($this->is_ajax, $this->error_msgs);
    }

    /**
     * Checks $_POST values. If the input is required, the error array can be set as an argument. If the $error_array
     * is an array and the input value is blank, $error_array[$post_index] is set to 0.
     *
     * @param   $post_index     string      The index name for the $_POST value being checked.
     * @param   $error_array    null|array  Default is null. If an array is provided, input value is required.
     * @return  string          string      Either whitespace or sanitized value of $_POST[$post_index]
     */
    protected function check_text($post_index, &$error_array = null)
    {
        $input = isset($_POST[$post_index]) ? trim($_POST[$post_index]) : '';

        if ($input === '' && is_array($error_array))
        {
            $error_array[$post_index] = 0;
        }

        return strip_tags($input);
    }

    /**
     * Checks if email is either blank or invalid. If email is blank, $error_array[$email_index] is 0. If email is
     * invalid, $error_array[$email_index] is -1.
     *
     * @param   $email_index        string  The index name for the email input.
     * @param   array $error_array          The error array that will be passed error data by reference.
     * @return  string              string  Either whitespace or sanitized value of $_POST[$email_index]
     */
    protected function check_email($email_index, array &$error_array)
    {
        $email_input = $this->check_text($email_index, $error_array);

        if ($email_input === '')
        {
            return '';
        }

        /* Clean the email input */
        $email = filter_var($email_input, FILTER_SANITIZE_EMAIL);

        /* Validate email */
        $validate = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($validate === false)
        {
//            $error_array[$email_index] = false;
            $error_array[$email_index] = -1;
        }

        return $email;
    }

    /**
     * Builds an array of validation error messages that can be displayed to the user submitting the email.
     *
     * @param   array   $error_array    The array containing results from $_POST input checks.
     * @return  array                   An array with validation messages for the person submitting the email.
     */
    protected function build_error_msgs(array $error_array)
    {
        $error_msgs = array();

        foreach ($error_array as $key=>$value)
        {
            switch ($value)
            {
                case 0:
                    // The input was blank.
                    $msg = ucfirst("$key cannot be blank");
                    break;
                case -1:
                    // The input was invalid.
                    $msg = "Please make sure the $key field is in valid format";
                    break;
                default:
                    // Something is amiss
                    $msg = ucfirst("$key cannot be awesome blank");
                    break;
            }
            $error_msgs[$key] = $msg;
        }

        return $error_msgs;
    }

    /**
     * If no errors are present, send the email.
     *
     * @param   array   $error_msgs     Array of error messages.
     * @param   array   $input_data     Values from the email submit form.
     * @return  bool    True if email was sent. Else false.
     */
    protected function try_send_email(array &$error_msgs, array $input_data)
    {
        // If there are errors, just return false.
        if (!empty($error_msgs))
        {
            return false;
        }

        $name    = $input_data['name'];
        $email   = $input_data['email'];
        $company = $input_data['company'];
        $message = $input_data['message'];

        $content  = "Name: $name \n";
        $content .= "Email: $email \n";
        $content .= "Company: $company \n\n";
        $content .= "Message: \n\n";
        $content .= $message;

        $mail = new PHPMailer;

        $mail->setFrom('contact@example.com', 'Contact Form'); // Needs real address
        $mail->addAddress('person@example.com', 'Name');    // Needs real address
        $mail->Subject  = 'Contact From website.com';       // Needs webpage
        $mail->Body     = $content;

        if(!$mail->Send())
        {
            error_log("PHPMailer: " . $mail->ErrorInfo);
            $error_msgs['generic'] = 'There was a problem sending your email. Please try again later.';

            return false;
        }
        else {
            return true;
        }
    }

    /**
     * If this isn't an Ajax call, then do the following:
     * - 1. Unset previous $_SESSION messages.
     * - 2. If no errors (all inputs are valid and the email has been sent), set $_SESSION success message
     * - 3. If there were errors, set $_SESSION messages.
     * - 4. Redirect to referer.
     *
     * @param $is_ajax
     * @param array $error_msgs
     */
    protected function non_ajax_processing($is_ajax, array $error_msgs)
    {
        if ($is_ajax === false)
        {
            $this->unset_session_msgs();

            if (empty($error_msgs))
            {
                $_SESSION['success'] = 1;
            } else {
                $this->set_error_sessions($error_msgs);
            }

            $this->do_redirect();
        }
    }

    /**
     * Unset all the $_SESSION messages.
     *
     * @return void
     */
    protected function unset_session_msgs()
    {
        unset($_SESSION['success']);
        unset($_SESSION['error_generic']);
        unset($_SESSION['error_name']);
        unset($_SESSION['error_email']);
        unset($_SESSION['error_company']);
        unset($_SESSION['error_message']);
    }

    protected function set_error_sessions(array $error_msgs)
    {
        foreach ($error_msgs as $key=> $value)
        {
            $session_index = 'error_' . $key;
            $_SESSION[$session_index] = $value;
        }
    }

    /**
     * Sends user back to the page from which the submit page was submitted.
     */
    protected function do_redirect()
    {
        $referrer = strtok($_SERVER["HTTP_REFERER"],'?');

        header('Location: ' . $referrer);
    }

    /**
     * Returns a response for Ajax requests.
     *
     * @return  int|string  returns 1 if no errors are present. Else returns JSON encoded string with error message data.
     */
    public function return_ajax_msg()
    {
        $error_msgs = $this->error_msgs;
        if (empty($error_msgs))
        {
            return 1;
        }

        return json_encode($error_msgs, true);
    }

}