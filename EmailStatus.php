<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 18.04.14 at 14:59
 */
 namespace samson\social;

/**
 * Class that describes all email statuses
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 * @version 0.0.1
 */
class EmailStatus 
{
    /** Email successfully registered */
    const SUCCESS_EMAIL_REGISTERED = 20001;
    /** Email successfully confirmed */
    const SUCCESS_EMAIL_CONFIRMED = 20002;
    /** Email was successfully confirmed before */
    const SUCCESS_EMAIL_CONFIRMED_ALREADY = 20003;
    /** User successfully authorized */
    const SUCCESS_EMAIL_AUTHORIZE = 20004;

    /** Email is already registered */
    const ERROR_EMAIL_REGISTER_FOUND = 10001;
    /** Email register external handler error */
    const ERROR_EMAIL_REGISTER_HANDLER = 10002;
    /** Email confirm not found error */
    const ERROR_EMAIL_CONFIRM_NOTFOUND = 10003;
    /** Email confirm external handler error */
    const ERROR_EMAIL_CONFIRM_HANDLER = 10004;
    /** Email is not registered */
    const ERROR_EMAIL_AUTHORIZE_NOTFOUND = 10005;
    /** Password is wrong */
    const ERROR_EMAIL_AUTHORIZE_WRONGPWD = 10006;
    /** Password is wrong */
    const ERROR_EMAIL_AUTHORIZE_HANDLER = 10007;

    /** Status code */
    public $code;

    /** Status text description */
    public $text;

    /** External response */
    public $response = array();

    /**
     * Create status object
     *
     * @param int $status Email status code
     */
    public function __construct($status)
    {
        $this->code = $status;

        switch ($status) {
            case self::SUCCESS_EMAIL_REGISTERED: $this->text = 'Email has been successfully registered'; break;
            case self::SUCCESS_EMAIL_CONFIRMED: $this->text = 'Email has been successfully confirmed'; break;
            case self::SUCCESS_EMAIL_CONFIRMED_ALREADY: $this->text = 'Email has already been confirmed before'; break;
            case self::SUCCESS_EMAIL_AUTHORIZE: $this->text = 'User successfully authorized'; break;
            case self::ERROR_EMAIL_REGISTER_FOUND: $this->text = 'Email is already registered'; break;
            case self::ERROR_EMAIL_REGISTER_HANDLER: $this->text = 'Email register external handler failure'; break;
            case self::ERROR_EMAIL_CONFIRM_NOTFOUND: $this->text = 'Email address for confirmation not found'; break;
            case self::ERROR_EMAIL_CONFIRM_HANDLER: $this->text = 'Email confirmation external handler failure'; break;
            case self::ERROR_EMAIL_AUTHORIZE_NOTFOUND: $this->text = 'User email for authorization not found'; break;
            case self::ERROR_EMAIL_AUTHORIZE_WRONGPWD: $this->text = 'User password for authorization not found'; break;
            case self::ERROR_EMAIL_AUTHORIZE_HANDLER: $this->text = 'User authorization external handler failure'; break;
            default: $this->text = 'Status not found';
        }
    }
}

