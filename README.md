# PasswordManager

[![Build Status](https://travis-ci.org/BTCCTB/Password.svg?branch=master)](https://travis-ci.org/BTCCTB/Password)
[![Build Status](https://scrutinizer-ci.com/g/BTCCTB/Password/badges/build.png)](https://scrutinizer-ci.com/g/BTCCTB/Password/build-status/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/BTCCTB/Password/badges/quality-score.png)](https://scrutinizer-ci.com/g/BTCCTB/Password/)
[![Code Coverage](https://scrutinizer-ci.com/g/BTCCTB/Password/badges/coverage.png)](https://scrutinizer-ci.com/g/BTCCTB/Password/)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/BTCCTB/Password/badges/code-intelligence.svg)](https://scrutinizer-ci.com/code-intelligence)

A php application for Active Directory Manipulation.

## Requirement
* You need PHP 7.1 or newer, with session support.
* PasswordManager supports MySQL-compatible databases
    * MySQL 5.7 or newer
    * MariaDB 5.5 or newer
* LDAP PHP extension enabled
* and the [usual Symfony application requirements][1].

## Installation
execute this command to clone this repository and initialize the project:
```bash
$ git clone git@github.com:BTCCTB/Password.git
$ cd Password/
$ composer install
```

## Usage
* Configure your own parameters file in `./app/config/parameters.yml` based on `./app/config/parameters.yml.dist`

Start the local server and initialize the database
```bash 
$ composer serve
$ composer db-init
```

> **NOTE**
>
> If you want to use a fully-featured web server (like Nginx or Apache) to run
> PasswordManager, configure it to point at the `web/` directory of the project.
> For more details, see:
> [configure a fully-featured web server][2]

## Composer script
### Start the local server:
* Start the local database/phpmyadmin (localhost:8080) instance with docker
* Run the internal php server on localhost:8000
```bash 
$ composer serve
```

### Stop the local server:
* Stop the local database/phpmyadmin instance with docker
```bash
$ composer stop
```

### Initialize the database:
* Drop the database if exists
* Create the database
* Create the schema with doctrine migration
```bash
$ composer db-init
```

### Update the database schema
* Execute the doctrine migration
```bash
$ composer db-migrate
```

### Testings
* Execute the phpunit tests
```bash
$ composer test
```

## TODO
- [X] Latest Symfony Standard
- [X] Configure AD Auth
    - [X] ADLdap2
    - [X] Guard Auth
- [X] Import user in ldap
- [X] Password generator
- [X] Change password form
- [ ] Reset password form ?
- [X] List latest in/out
- [X] Mail notification

## Contributing

Thank you for considering contributing to the PasswordManager project! Please review an abide the [contribution guide](docs/CONTRIBUTING.md).

## Code of Conduct

In order to ensure that the PasswordManager community is welcoming to all, please review and abide by the [Code of Conduct](docs/CODE_OF_CONDUCT.md).

## License

The PasswordManager project is open-sourced software licensed under the [GPL-2.0 License](LICENSE.md).

[1]: https://symfony.com/doc/current/reference/requirements.html
[2]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html