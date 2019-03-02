# Secure Remote Password (SRP-6a) Demo

A Laravel application that demonstrates how SRP-6a works using [artisansdk/srp](https://github.com/artisansdk/srp).

## Getting Started

- [Requirements](#requirements)
- [Installation](#installation)
- [Running the Tests](#running-the-tests)
- [License](#license)

## Requirements

- PHP 7.1+
- Node 8.11+
- Composer 1.6+
- NPM 6.6.0+
- Laravel 5.7+
- UI Kit 3.0+
- VueJS 2.1+

Additionally you'll need a database like MariaDB 10.3+ and a web server like
Apache or Nginx. You could use the built-in `php artisan serve` and use the
SQLite driver if you want to run with just PHP.

## Installation

The following assumes that you have `composer` and `yarn` globally installed and
that you are following the Homestead convention of putting your projects as
subdirectories of your `~/Code` folder.

```bash
git clone git@github.com:dalabarge/srp-demo ~/Code/srp-demo
composer install
yarn install
```

You will also need to install `php-bcmath` or `php-gmp` extensions for PHP to
help improve the performance of the number crunching. On a Debian/Ubuntu based
system you can run `apt-get install php-gmp`.

> **Pro Tip:** If using a virtual machine like Vagrant then often `composer` is ran
in the virtual machine while performance of NPM is improved when `npm` is ran
on the host machine (i.e: your Mac).

You'll need to then get the hosting environment setup. If a first time install,
you'll need to generate an app key. If it's an existing install then you'll
need to get a copy of the app key to put in your environment file. There is an
example environment file you can copy to get started filling in the credentials

```bash
cp .env.example .env
php artisan key:generate
```

After configuring your `.env` file and setting up system services like Nginx and
MariaDB database and database user, you are ready to install the database using
the built in migrations and seeders.

```bash
php artisan migrate --seed
```

> **Pro Tip:** If a compatible version of PHP is not available on your host machine
then you may need to run these `php artisan` commands on your virtual machine.

Finally, you'll need to build the frontend assets with Webpack. Helper commands
have been added to NPM (via `package.json`) to make running the builds easier:

```bash
npm run production
```

## Running the Tests

The test coverage goal for the project is to maintain coverage above 80% in total
and 100% for all critical services. This can be achieved by taking an outside-in
approach to TDD – defining the public API of functionality and making sure that
tests are ran against the features. As code coverage is missed via these high-level
tests, additional unit tests can be added to increase the class coverage.

All tests are written using basic PHPUnit and largely follow Laravel defaults.
PHPUnit is installed as a local dependency and tests can be ran with:

```bash
vendor/bin/phpunit
```

Code coverage reports have also been made available and so several helper commands
have been added to Composer (via `composer.json`) including:

- `composer test` – runs the entire test suite including PHP CS checker without coverage
- `composer report` – runs all tests with code coverage and reports messes, duplicates, and stats
- `composer fix` – runs the PHP CS fixer to automatically clean up PHP code

# Licensing

Copyright (c) 2019 [Artisans Collaborative](https://artisanscollaborative.com)

This package is released under the MIT license. Please see the LICENSE file
distributed with every copy of the code for commercial licensing terms.
