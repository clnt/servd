# ServD
ServD is a Docker PHP development environment heavily inspired by Laravel Valet and Laradock, it supports multiple projects
within a working directory and primarily runs on [Alpine Linux](https://alpinelinux.org/).

In its current form, it installs the majority of PHP Extensions available, including xdebug which currently adds
slower performance than expected. This will change and these extensions will be able to be toggled in a future release.

It offers PHP, Node.js, Database Software and PHP Composer version selection making it easy to change the environment
to suit your needs.

## Installation

Install via composer globally by running `composer global require clntdev/servd` - once this has finished type `servd` into the CLI and ensure it returns a list of commands.

- Run the `servd install` command to setup required services and generate the `.servd` folder which will be located within the home directory.
- Run the `servd start` command after completing the installation steps, this will pull/build images and then start up the configured services.

## Commands

List of planned commands:

`servd install` ✅

- Creates sqlite database
- Prompt for preferred options such as PHP version/Node Version and Composer Version
- Prompt for confirmation of working directory, choose current directory or full path can be specified

`servd start` ✅

- Start docker containers

`servd stop` ✅

- Stop docker containers

`servd restart` ✅

- Restart docker containers
- Rebuild docker images with optional `--rebuild` flag.

`servd rebuild` ✅

- Rebuild docker containers
- Update images with optional `--update` flag.
- Rebuild/Update a specific container by specifying name as an argument: `servd rebuild mysql`.

`servd configure` ✅

- Regenerates configuration files and directory structures.

`servd use x.x` ✅

- Stops containers
- Switch PHP version to specified (7.4, 8.0, 8.1 supported)
- Rebuilds configuration and docker containers
- Starts docker containers

`servd secure` ✅

- Generates certificates and configures the current project for HTTPS, rebuilding config files and restarting services.
- Note: You will need to ensure the created `servdCA.crt` CA is trusted by your machine.

`servd unsecure` ✅

- Removes existing certificate files for a project and marks it as non secure, rebuilding config files and restarting services.

`servd secure:trust`

- **Requires Sudo (macOS & Linux Only)** - this command will attempt to add the CA certificate generated to the system.
- **Windows Users** - you will need to add the `servdCA.crt` file to

`servd park` ✅

- Adds/Changes the project directory to the current directory
- If projects exist then update existing paths in sqlite db (could prompt for this)

`servd run "{command}"` ✅

- Run given command in docker container using current directory name as project directory name, remember to wrap command in quotes if more than one word i.e. `servd run "php artisan cache:clear"`

`servd cli {container(optional)}` ✅

- Open an interactive shell into the given container or the `servd_core` container by default if none specified.

`servd new {optional db name}`

- Adds the project to the project list in sqlite db
- Creates a database for the project if specified

`servd composer {command}`

- Run composer command in docker container for current project

`servd npm {command}`

- Run npm command in docker container for current project

## Supported Drivers

- Laravel
- Wordpress
- Drupal
- GenericHtml (basic config)
- GenericPhp (basic config with php-fpm)


## Other

Built with [Laravel Zero](https://laravel-zero.com/).
