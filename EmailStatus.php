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

    /**
     * Convert status code to string
     *
     * @param int $status Email status code
     * @return string Status string description
     */
    public static function toString($status)
    {
        switch ($status) {
            case self::SUCCESS_EMAIL_REGISTERED: return ''; break;
            case self::SUCCESS_EMAIL_CONFIRMED: return ''; break;
            case self::SUCCESS_EMAIL_CONFIRMED_ALREADY: return ''; break;
            case self::SUCCESS_EMAIL_AUTHORIZE: return ''; break;
            case self::ERROR_EMAIL_REGISTER_FOUND: return ''; break;
            case self::ERROR_EMAIL_REGISTER_HANDLER: return ''; break;
            case self::ERROR_EMAIL_CONFIRM_NOTFOUND: return ''; break;
            case self::ERROR_EMAIL_CONFIRM_HANDLER: return ''; break;
            case self::ERROR_EMAIL_AUTHORIZE_NOTFOUND: return ''; break;
            case self::ERROR_EMAIL_AUTHORIZE_WRONGPWD: return ''; break;
            case self::ERROR_EMAIL_AUTHORIZE_HANDLER: return ''; break;
            default: return 'Status not found';
        }
    }
}

