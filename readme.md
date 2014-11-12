# Email registration and authorization module [SamsonPHP](http://samsonphp.com) framework

> Generic Email registration and authorization module for Social [SamsonPHP](http://samsonphp.com) framework

Social_email module allows you to pass registration and enter the site via email. You need to configure the module in a proper way to work correctly. There are many options (parameters) for this.


* **dbAccessToken** – database field, that stores the token for security.
* **cookieTime** – cookie existence time(e.g. 60*60*24 = 24 hours).

* **function authorizeWithEmail** - check the entered email. If the data is correct, there is an authorization.
* **function authorize** - authorizes the user. If you pass a parameter ```rememeber```, function creates a token and write it to the database and cookie variable.
* **function cookieVerification** - authorizes the user if existing cookies match token in the database.


Developed by [SamsonOS](http://samsonos.com/)