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
    /* Module identifier */
    public $id = 'socialemail';

    /** Database hashed email column name */
    public $dbHashEmailField = 'hash_email';

    /** Database hashed password column name */
    public $dbHashPasswordField = 'hash_password';

    /* Database user email field */
    public $dbConfirmField = 'email';

    /**
     * External callable register success handler
     * @var callback
     */
    public $registerHandler;

    /**
     * External callable confirm success handler
     * @var callback
     */
    public $confirmHandler;

    /**
     * External callable authorize success handler
     * @var callback
     */
    public $authorizeHandler;

    /** Module preparation */
    public function prepare()
    {
        // Create and check general database table fields configuration
        db()->createField($this, $this->dbTable, 'dbConfirmField', 'VARCHAR('.self::$hashLength.')');
        db()->createField($this, $this->dbTable, 'dbHashEmailField', 'VARCHAR('.self::$hashLength.')');
        db()->createField($this, $this->dbTable, 'dbHashPasswordField', 'VARCHAR('.self::$hashLength.')');

        return parent::prepare();
    }

    /**
     * Authorize user via email
     * @param string $hashedEmail       Hashed user email
     * @param string $hashedPassword    Hashed user password
     * @param mixed  $user              Variable to return created user object
     *
     * @return int EmailStatus value
     */
    public function authorize($hashedEmail, $hashedPassword, & $user = null)
    {
        // Check if this email is registered
        if (!dbQuery($this->dbTable)->cond($this->dbHashEmailField, $hashedEmail)->first($user)) {
            // Check if passwords match
            if ($user[$this->dbHashPasswordField] === $hashedPassword) {
                // Call external authorize handler if present
                if (is_callable($this->authorizeHandler)) {
                    // Call external handler - if it fails - return false
                    if (!call_user_func_array($this->authorizeHandler, array(&$user))) {
                        return EmailStatus::ERROR_EMAIL_AUTHORIZE_HANDLER;
                    }
                }

                return EmailStatus::SUCCESS_EMAIL_AUTHORIZE;
            }

            return EmailStatus::ERROR_EMAIL_AUTHORIZE_WRONGPWD;
        }

        return EmailStatus::ERROR_EMAIL_AUTHORIZE_NOTFOUND;
    }

    /**
     * Register new user
     *
     * @param string $email          User email address
     * @param string $hashedPassword User hashed password string
     * @param mixed  $user           Variable to return created user object
     *
     * @return int EmailStatus value
     */
    public function register($email, $hashedPassword, & $user = null)
    {
        // Check if this email is not already registered
        if (!dbQuery($this->dbTable)->cond($this->dbEmailField, $email)->first($user)) {

            // Create empty db record instance
            /**@var $user \samson\activerecord\dbRecord */
            $user = new $this->dbTable(false);
            $user[$this->dbEmailField]          = $email;
            $user[$this->dbHashEmailField]      = $this->hash($email);
            $user[$this->dbHashPasswordField]   = $hashedPassword;
            $user[$this->dbEmailField]          = $email;
            $user[$this->dbConfirmField]        = $this->hash($email.time());
            $user->save();

            // Call external register handler if present
            if (is_callable($this->registerHandler)) {
                // Call external handler - if it fails - return false
                if (!call_user_func_array($this->registerHandler, array(&$user))) {
                   return EmailStatus::ERROR_EMAIL_REGISTER_HANDLER;
                }
            }

            // Everything is OK
            return EmailStatus::SUCCESS_EMAIL_REGISTERED;

        }

        return EmailStatus::ERROR_EMAIL_REGISTER_FOUND;
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
        // Find user record by hashed email
        if(dbQuery($this->dbTable)->cond($this->dbEmailField, $hashedEmail)->first($user)) {

            // If this email is confirmed
            if($user[$this->dbConfirmField] == 1) {
                return EmailStatus::SUCCESS_EMAIL_CONFIRMED_ALREADY;
            } else if ($user[$this->dbConfirmField] === $hashedCode) {
                // If user confirmation codes matches

                // Set db data that this email is confirmed
                $user[$this->dbConfirmField] = 1;
                $user->save();

                // Call external confirm handler if present
                if (is_callable($this->confirmHandler)) {
                    // Call external handler - if it fails - return false
                    if (!call_user_func_array($this->confirmHandler, array(&$user))) {
                        return EmailStatus::ERROR_EMAIL_CONFIRM_HANDLER;
                    }
                }

                // Everything is OK
                return EmailStatus::SUCCESS_EMAIL_CONFIRMED;
            }
        }

        return EmailStatus::ERROR_EMAIL_CONFIRM_NOTFOUND;
    }
}
 