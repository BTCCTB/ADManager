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
The Enabel IT Team Symfony Makefile 🍺

Outputs the help screen
```bash 
$ make help
```

Sleep 5 seconds
```bash 
$ make wait
```
### Composer 🧙‍♂
Download and install composer in the project (file is ignored)
```bash 
$ make get-composer
```
Install vendors according to the current composer.lock file
```bash 
$ make install
```
Update vendors according to the composer.json file
```bash 
$ make update
```
### Symfony 🎵
List all Symfony commands
```bash 
$ make sf
```
Clear the cache
```bash 
$ make cc
```
Warmump the cache
```bash 
$ make warmup
```
Fix permissions of all var files
```bash 
$ make fix-perms
```
Install the assets with symlinks in the public folder
```bash 
$ make assets
```
Purge cache and logs
```bash 
$ make purge
```
Creates a new migration based on database changes
```bash 
$ make create-miogration
```
Execute a migration to a specified version or the latest available version.
```bash 
$ make exec-migration
```
Creates a new controller class
```bash 
$ make create-controller
```
Creates or updates a Doctrine entity class
```bash 
$ make create-entity
```
Creates a new form class
```bash 
$ make create-form
```
Creates a new security voter class
```bash 
$ make create-voter
```
### Symfony binary 💻
Download and install the binary in the project (file is ignored)
```bash 
$ make bin-install
```
Install the local HTTPS certificates
```bash 
$ make cert-install
```
Serve the application with HTTPS support
```bash 
$ make serve
```
Stop the web server
```bash 
$ make unserve
```
### Docker 🐳
Start the docker hub (MySQL,redis,phpmyadmin,mailcatcher)
```bash 
$ make up
```
Stop the docker hub
```bash 
$ make down
```
List Docker containers for the project
```bash 
$ make dpsn
```
### Project 🛠
Start docker, load fixtures and start the web server
```bash 
$ make run
```
Reload fixtures
```bash 
$ make reload
```
Stop docker and the Symfony binary server
```bash 
$ make abort
```
Flush all Redis cache
```bash 
$ make cc-redis
```
Display all commands in the project namespace
```bash 
$ make commands
```
Build the db, control the schema validity, load fixtures and check the migration status
```bash 
$ make load-fixtures
```
### Tests ✅
Launch main functionnal and unit tests
```bash 
$ make test
```
Launch tests implying external resources (api, services...)
```bash 
$ make test-external
```
Launch all tests
```bash 
$ make test-all
```
### Coding standards ✨ 
Launch check style and static analysis
```bash 
$ make cs
```
Run php_codesniffer only
```bash 
$ make codesniffer
```
Run PHPStan only
```bash 
$ make stan
```
Run PHP Mess Dectector only
```bash 
$ make mess
```
Run psalm only
```bash 
$ make psalm
```
Init a new psalm config file for a given level, it must be decremented to have stricter rules
```bash 
$ make init-psalm
```
Run php-cs-fixer and fix the code.
```bash 
$ make cs-fix
```
### Deploy & Prod 🚀 
Deploy on prod, no-downtime deployment with Ansistrano
```bash
$ make depoy-prod
```
Deploy on stage no-downtime deployment with Ansistrano
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