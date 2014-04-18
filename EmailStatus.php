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

    /** Email is already registered */
    const ERROR_EMAIL_REGISTER_FOUND = 10001;
    /** Email register external handler error */
    const ERROR_EMAIL_REGISTER_HANDLER = 10002;
    /** Email confirm not found error */
    const ERROR_EMAIL_CONFIRM_NOTFOUND = 10003;
    /** Email confirm external handler error */
    const ERROR_EMAIL_CONFIRM_HANDLER = 10004;
}
 