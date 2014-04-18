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

    /** External callable register success handler */
    public $registerHandler;

    /** Module preparation */
    public function prepare()
    {
        // Create and check general database table fields configuration
        db()->createField($this, $this->dbTable, 'dbConfirmField', 'VARCHAR(64)');
        db()->createField($this, $this->dbTable, 'dbHashEmailField', 'VARCHAR(64)');
        db()->createField($this, $this->dbTable, 'dbHashPasswordField', 'VARCHAR(64)');

        return parent::prepare();
    }

    public function authorize()
    {

    }

    public function deauthorize()
    {

    }

    public function register($email, $hashedPassword)
    {
        // Create empty db record instance
        $user = new $this->dbTable(false);
        $user[$this->dbEmailField]          = $email;
        $user[$this->dbHashEmailField]      = hash('sha256', $email);
        $user[$this->dbHashPasswordField]   = $hashedPassword;
        $user[$this->dbEmailField]          = $email;
        $user[$this->dbConfirmField]        = hash('sha256', $email.time());
        $user->save();

        // Call external register handler if present
        if (is_callable($this->registerHandler)) {
            call_user_func_array($this->registerHandler, array(&$user));
        }
    }

    public function generatePassword()
    {

    }

    public function confirmEmail()
    {

    }
}
 