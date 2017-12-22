# PasswordManager Contribution Guide

Thank you for considering contributing to the PasswordManager project!

## Bug reports

To encourage active collaboration, BTC-CTB strongly encourages pull requests, not just bug reports. "Bug reports" may also be sent in the form of a pull request containing a failing test.

However, if you file a bug report, your issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

Remember, bug reports are created in the hope that others with the same problem will be able to collaborate with you on solving it. Do not expect that the bug report will automatically see any activity or that others will jump to fix it. Creating a bug report serves to help yourself and others start on the path of fixing the problem.

The PasswordManager source code is managed on GitHub

## Security Vulnerabilities
   
If you discover a security vulnerability within PasswordManager, please send an email to BTC-CTB at ict.helpdesk@btcctb.org. All security vulnerabilities will be promptly addressed.

## Coding Style
   
PasswordManager follows the PSR-2 coding standard and the PSR-4 autoloading standard. 

### PHPDoc

Below is an example of a valid PasswordManager documentation block. Note that the @param attribute is followed by two spaces, the argument type, two more spaces, and finally the variable name:

```php
/**
 * Register a binding with the container.
 *
 * @param  string|array  $abstract
 * @param  \Closure|string|null  $concrete
 * @param  bool  $shared
 * @return void
 */
public function bind($abstract, $concrete = null, $shared = false)
{
    //
}
```
### PHP Code Sniffer

Before committing your PR, check your code with our [coding standard](https://github.com/BTCCTB/PasswordManager/blob/master/phpcs.xml)

### PHP Mess Detector

Again before committing your PR, check your code with our [coding rules](https://github.com/BTCCTB/PasswordManager/blob/master/codesize.xml)
