# PasswordManager

![Full CI process for Symfony](https://github.com/BTCCTB/Password/workflows/Full%20CI%20process%20for%20Symfony/badge.svg)

A php application for Active Directory Manipulation.

## Requirement
* You need PHP 7.2 or newer, with session support.
* PasswordManager supports MySQL-compatible databases
    * MySQL 5.7 or newer
    * MariaDB 10.1 or newer
* LDAP PHP extension enabled
* and the [usual Symfony application requirements][1].

## Installation
execute this command to clone this repository and initialize the project:
```bash
$ git clone git@github.com:BTCCTB/Password.git
$ cd Password/
$ make install
```

## Usage
* Configure your own parameters file in `.env.local` based on `.env`

Start the local server and initialize the database
```bash 
$ make run
```

> **NOTE**
>
> If you want to use a fully-featured web server (like Nginx or Apache) to run
> PasswordManager, configure it to point at the `web/` directory of the project.
> For more details, see:
> [configure a fully-featured web server][2]

## Makefile 
### Help:
```bash 
$ make help
```

### Deploy:
To deploy on prod:
```bash
$ make depoy-prod
```
To deploy on stage:
```bash
$ make depoy-stage
```

## Contributing

Thank you for considering contributing to the PasswordManager project! Please review an abide the [contribution guide](docs/CONTRIBUTING.md).

## Code of Conduct

In order to ensure that the PasswordManager community is welcoming to all, please review and abide by the [Code of Conduct](docs/CODE_OF_CONDUCT.md).

## License

The PasswordManager project is open-sourced software licensed under the [GPL-2.0 License](LICENSE.md).

[1]: https://symfony.com/doc/current/reference/requirements.html
[2]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html