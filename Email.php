<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 18.04.14 at 14:06
 */
namespace samson\social;

/**
 * Generic class for user registration and authorization via Email
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 * @version 0.0.2
 */
class Email extends Core
{
    const RESPONSE_ERROR_FIELD = 'email_error';
    const RESPONSE_STATUS_FIELD = 'email_status';
    const RESPONSE_STATUS_TEXTFIELD = 'email_status_text';

    /* Module identifier */
    public $id = 'socialemail';

    /** Database hashed email column name */
    public $dbHashEmailField = 'hash_email';

    /** Database hashed password column name */
    public $dbHashPasswordField = 'hash_password';

    /* Database user email field */
    public $dbConfirmField = 'hash_confirm';

    /**
     * External callable register handler
     * @var callback
     */
    public $registerHandler;

    /**
     * External callable confirm handler
     * @var callback
     */
    public $confirmHandler;

    /** Module preparation */
    public function prepare()
    {
        // Create and check general database table fields configuration
        db()->createField($this, $this->dbTable, 'dbConfirmField', 'VARCHAR('.self::$hashLength.')');
        db()->createField($this, $this->dbTable, 'dbHashEmailField', 'VARCHAR('.self::$hashLength.')');
        db()->createField($this, $this->dbTable, 'dbHashPasswordField', 'VARCHAR('.self::$hashLength.')');

        return parent::prepare();
    }

    /** User authorization handler */
    public function authorize(\samson\activerecord\dbRecord & $user, $remember = false)
    {
        // Call default authorize behaviour
        if(parent::authorize($user, $remember)) {
            // If remember flag is passed - save it
            if ($remember) {
                // Get site url base
                $url_base = url()->base();

                // Set cookies with auth data
                setcookie( $url_base.'_cookie_md5Email', $user[$this->dbHashEmailField], time()+(24*3600),'/');
                setcookie( $url_base.'_cookie_md5Password', $user[$this->dbHashPasswordField], time()+(24*3600),'/' );
            }
        }
    }

    /**
     * Authorize user via email
     * @param string $hashedEmail       Hashed user email
     * @param string $hashedPassword    Hashed user password
     * @param mixed  $user              Variable to return created user object
     *
     * @return int EmailStatus value
     */
    public function authorizeWithEmail($hashedEmail, $hashedPassword, & $user = null)
    {
        // Status code
        $result = new EmailStatus(0);

        // Check if this email is registered
        if (dbQuery($this->dbTable)->cond($this->dbHashEmailField, $hashedEmail)->first($user)) {
            // Check if passwords match
            if ($user[$this->dbHashPasswordField] === $hashedPassword) {
                $result = new EmailStatus(EmailStatus::SUCCESS_EMAIL_AUTHORIZE);

                // Login with current user
                $this->authorize($user);

            } else { // Wrong password
                $result = new EmailStatus(EmailStatus::ERROR_EMAIL_AUTHORIZE_WRONGPWD);
            }
        } else { // Email not found
            $result = new EmailStatus(EmailStatus::ERROR_EMAIL_AUTHORIZE_NOTFOUND);
        }

        // Call external authorize handler if present
        if (is_callable($this->authorizeHandler)) {
            // Call external handler - if it fails - return false
            if (!call_user_func_array($this->authorizeHandler, array(&$user, &$result))) {
                $result = new EmailStatus(EmailStatus::ERROR_EMAIL_AUTHORIZE_HANDLER);
            }
        }

        return $result;
    }

    /**
     * Register new user
     *
     * @param string $email          User email address
     * @param string $hashedPassword User hashed password string
     * @param mixed  $user           Variable to return created user object
     * @param bool   $valid          Flag that email is already confirmed
     *
     * @return int EmailStatus value
     */
    public function register($email, $hashedPassword = null, & $user = null, $valid = false)
    {
        // Status code
        $result = new EmailStatus(0);

        // Check if this email is not already registered
        if (!dbQuery($this->dbTable)->cond($this->dbEmailField, $email)->first($user)) {
            /**@var $user \samson\activerecord\dbRecord */

            // If user object is NOT passed
            if (!isset($user) ) {
                // Create empty db record instance
                $user = new $this->dbTable(false);
            }

            $user[$this->dbEmailField]          = $email;
            $user[$this->dbHashEmailField]      = $this->hash($email);

            // If password is passed
            if (isset($hashedPassword)) {
                $user[$this->dbHashPasswordField] = $hashedPassword;
            } else { // Generate random password
                $user[$this->dbHashPasswordField] = $this->generatePassword();
            }

            // If this email is not valid or confirmed
            if (!$valid) {
                $user[$this->dbConfirmField] = $this->hash($email.time());
            } else { // Email is already confirmed
                $user[$this->dbConfirmField] = 1;
            }

            // Save object to database
            $user->save();

            // Class default authorization
            $this->authorize($user);

            // Everything is OK
            $result = new EmailStatus(EmailStatus::SUCCESS_EMAIL_REGISTERED);

        } else { // Email not found
            $result = new EmailStatus(EmailStatus::ERROR_EMAIL_REGISTER_FOUND);
        }

        // Call external register handler if present
        if (is_callable($this->registerHandler)) {
            // Call external handler - if it fails - return false
            if (!call_user_func_array($this->registerHandler, array(&$user, &$result))) {
                $result = new EmailStatus(EmailStatus::ERROR_EMAIL_REGISTER_HANDLER);
            }
        }

        return $result;
    }

    /**
     * Generic email confirmation handler
     * @param string $hashedEmail   Hashed user email
     * @param string $hashedCode    Hashed user email confirmation code
     * @param mixed $user           Variable to return created user object
     *
     * @return int EmailStatus value
     */
    public function confirm($hashedEmail, $hashedCode, & $user = null)
    {
        // Status code
        $status = false;

        // Find user record by hashed email
        if(dbQuery($this->dbTable)->cond($this->dbEmailField, $hashedEmail)->first($user)) {

            // If this email is confirmed
            if($user[$this->dbConfirmField] == 1) {
                $status = EmailStatus::SUCCESS_EMAIL_CONFIRMED_ALREADY;
            } else if ($user[$this->dbConfirmField] === $hashedCode) {
                // If user confirmation codes matches

                // Set db data that this email is confirmed
                $user[$this->dbConfirmField] = 1;
                $user->save();

                // Everything is OK
                $status = EmailStatus::SUCCESS_EMAIL_CONFIRMED;
            }
        } else {
            $status = EmailStatus::ERROR_EMAIL_CONFIRM_NOTFOUND;
        }

        // Call external confirm handler if present
        if (is_callable($this->confirmHandler)) {
            // Call external handler - if it fails - return false
            if (!call_user_func_array($this->confirmHandler, array(&$user, $status))) {
                $status = EmailStatus::ERROR_EMAIL_CONFIRM_HANDLER;
            }
        }

        return $status;
    }

    /**
     * Generic universal asynchronous registration controller
     * method expects that all necessary registration data(email, hashed password)
     * would be passed via $_POST.
     *
     * @return array Asynchronous response array
     */
    public function __async_register()
    {
        $result = array('status' => '0');

        // Check if email field is passed
        if (!isset($_POST[$this->dbEmailField])) {
            $result[self::RESPONSE_ERROR_FIELD] = "\n".'['.$this->dbEmailField.'] field is not passed';
        }

        // Check if hashed password field is passed
        if (!isset($_POST[$this->dbHashPasswordField])) {
            $result[self::RESPONSE_ERROR_FIELD] = "\n".'['.$this->dbHashPasswordField.'] field is not passed';
        } else { // Rehash password
            $_POST[$this->dbHashPasswordField] = $this->hash($_POST[$this->dbHashPasswordField]);
        }

        // If we have all data needed
        if (isset($_POST[$this->dbHashPasswordField]) && isset($_POST[$this->dbEmailField])) {

            /**@var EmailStatus $registerResult Perform generic registration*/
            $registerResult = $this->register($_POST[$this->dbEmailField], $_POST[$this->dbHashPasswordField]);

            // Check if it was successfull
            if ($registerResult->code == EmailStatus::SUCCESS_EMAIL_REGISTERED) {
                $result['status'] = '1';
            }

            // Save email register status
            $result[self::RESPONSE_STATUS_TEXTFIELD] = $registerResult->text;
            $result[self::RESPONSE_STATUS_FIELD] = $registerResult->code;
            $result = array_merge($result, $registerResult->response);
        }

        return $result;
    }

    /**
     * Generic universal asynchronous authorization controller
     *
     * @param string $hashEmail    User hashed email for authorization
     * @param string $hashPassword User hashed password for authorization
     *
     * @return array Asynchronous response array
     */
    public function __async_authorize($hashEmail = null, $hashPassword = null)
    {
        $result = array('status' => '0');

        // Get hashed email field by all possible methods
        if (!isset($hashEmail)) {
            if (isset($_POST) && isset($_POST[$this->dbHashEmailField])) {
                $hashEmail = $_POST[$this->dbHashEmailField];
            } else if (isset($_GET) && isset($_GET[$this->dbHashEmailField])) {
                $hashEmail = $_GET[$this->dbHashEmailField];
            } else {
                $result['email_error'] = "\n".'['.$this->dbHashEmailField.'] field is not passed';
            }
        }

        // Get hashed password field by all possible methods
        if (!isset($hashPassword)) {
            if (isset($_POST) && isset($_POST[$this->dbHashPasswordField])) {
                $hashPassword = $_POST[$this->dbHashPasswordField];
            } else if (isset($_GET) && isset($_GET[$this->dbHashPasswordField])) {
                $hashPassword = $_GET[$this->dbHashPasswordField];
            } else {
                $result['email_error'] = "\n".'['.$this->dbHashPasswordField.'] field is not passed';
            }
        }

        // If we have authorization data
        if(isset($hashEmail) && isset($hashPassword)) {

            $hashEmail = $this->hash($hashEmail);
            $hashPassword = $this->hash($hashPassword);

            /**@var EmailStatus $authorizeResult Perform generic registration*/
            $authorizeResult = $this->authorizeWithEmail($hashEmail, $hashPassword);

            // Check if it was successfull
            if ($authorizeResult->code == EmailStatus::SUCCESS_EMAIL_AUTHORIZE) {
                $result['status'] = '1';
            }

            // Save email register status
            $result[self::RESPONSE_STATUS_TEXTFIELD] = $authorizeResult->text;
            $result[self::RESPONSE_STATUS_FIELD] = $authorizeResult->code;
            $result = array_merge($result, $authorizeResult->response);
        }

        return $result;
    }

    /**
     * Generic universal synchronous authorization controller
     *
     * @param string $hashEmail    User hashed email for authorization
     * @param string $hashPassword User hashed password for authorization
     */
    public function __authorize($hashEmail = null, $hashPassword = null)
    {
        // Perform asynchronous authorization
        $asyncResult = $this->__async_authorize($hashEmail, $hashPassword);

        if ($asyncResult) {

        }
    }

    /**
     * Generic universal synchronous registration controller
     */
    public function __register()
    {
        // Perform asynchronous authorization
        $asyncResult = $this->__async_register();

        if ($asyncResult) {

        }
    }
}
 