# Secure Remote Password (SRP-6a) Demo

A Laravel application that demonstrates how SRP-6a works using [artisansdk/srp](https://github.com/artisansdk/srp).

## Getting Started

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
    - [Registration](#registration-srp-enrollment)
    - [Identification](#identification-srp-challenge)
    - [Authentication](#authentication-srp-verification)
    - [Vue Components](#vue-components)
    - [Client-Side](#srp-client)
    - [Server-Side](#srp-server)
    - [SRP-6a Package](#secure-remote-password-package)
- [Running the Tests](#running-the-tests)
- [License](#license)

# Requirements

**Important:** This application is for demonstration purposes only. Laravel has
*been stripped of many boilerplate Laravel features and slightly reorganized to
*make it clearer how to use SRP authentication. There are many aspects of this
*project that are not production-ready. Get inspired from here but take extra
*measures to harden your application when implementing the SRP protocol.

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

# Installation

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

The default users that are seeded all have the password of "secret" set.

> **Pro Tip:** If a compatible version of PHP is not available on your host machine
then you may need to run these `php artisan` commands on your virtual machine.

Finally, you'll need to build the frontend assets with Webpack. Helper commands
have been added to NPM (via `package.json`) to make running the builds easier:

```bash
npm run production
```

# Usage Guide

For a quick demonstration, launch the application and go to the home page at
`/`. You should be greeted with a small card that talks a bit about the benefits
of SRP and has two links to Register Account at `/register` and Go to Sign In at
`/login`. You can also go to `/logout` to de-authenticate and start over again.

## Registration: SRP Enrollment

Register yourself an account at `/register` and record using your browser's
Network monitoring tab the data over the wire. You'll note that while the `name`
and `email` are transmitted, the `password` is never transmitted. Instead a
`verifier` and `salt` are sent. These two parameters are derivatives of the
password and can be used by the server to verify the user again at
authentication. This `verifier` and `salt` are generated client side and then
everything about the transmission is a normal `POST` form request and redirect
on success or error. This process is called _enrollment_ and is how the
`verifier` and `salt` get generated from the `password` client side and then
encrypted when stored in the `users` database table.

All the code associated with this process can be found by tracing the routes in
`routes/web.php` through to the `App\User\Register\Controller` which first
passes through the self-validating request objects in
`App\User\Register\Requests` namespace. The `Controller@show` method shows the
registration form and handles session-based error handling and query-based
hydration. The `Controller@store` method handles the actual storage of the
`name`, `email`, `salt` and `verifier` for the user and redirects to the login
screen to authenticate. The `email` is pre-filled via a flash message.

## Identification: SRP Challenge

Authenticate as your newly registered user at `/login`. You'll first notice that
the form has one step for entering an `email`, and then a second for `password`.
The reason for the transition is that when you tap the Submit button the first
time, the `email` is fired off to the server using Axios (AJAX) to initiate a
challenge request from the server. Using only the `email` as the identity of the
unauthenticated user, the server will look up that user in the database and
prepare a one-time challenge token based on the user's previously stored
`email`, `salt`, and `verifier`. This transmitted back to the client as `salt`
and `key` parameters. Together they form the challenge by which the SRP client
can derive a `proof` by using the `password` field. In this first step, the
`password` is never transmitted and capturing any either the client request or
server response (or even both really) is not enough to break the protocol.

All the code associated with this process can be found by tracing the routes in
`routes/web.php` through to the `App\User\Auth\Controller` which takes as
constructor argument `App\User\Auth\SRP` caching wrapper for the
`ArtisanSdk\SRP\Server` class. Each request passes through a corresponding form
validator. The `Controller@show` method shows the login form and handles
session-based error handling and query-based hydration. The `Controller@store`
method handles the identity lookup based on `email` and then generates the
challenge response with the server's `key` and the user's `salt`.

## Authentication: SRP Verification

Next, the `password` field is shown. When the Submit button is tapped again, the
SRP client uses the previous challenge information to generate a response to the
server including the client's `key` and the `proof` of password. Once generated,
it is then sent to the server along with the `email` so that the server can
verify the `proof`. If the `password` was incorrect then the `proof` would be
incorrect and the server will reset the protocol. The client side is smart
enough to fast forward to the current step again so the user can try again. If
the `proof` can be verified by the server, then the server responds in kind with
a `proof` that the server has the original `verifier`. The client side then
confirms the client `proof` and server `proof` matches before redirecting back
to the home page at `/`.

The `App\User\Auth\Controller@update` method then handles the actual
authentication where the client-side `key` and `proof` are verified and the
server-side mutual `proof` is provided. The `Controller@delete` handles the
session destruction for `/logout`.

## Vue Components

While Vue.js is used to make the Register and Login screens fancy, it's actually
needed. In fact, neither is AJAX, but the process feels more modern when you
incorporate that. What's important is to maintain the server-side and
client-side states separate and to never transmit the password. Furthermore you
have to clean up both sides so there's no sensitive data like passwords and
private keys left in memory. While the Vue components in
`resources/scripts/components/Register.vue` and `Login.vue` do clean up after
themselves, registration forces a full page refresh with each submission of the
form and the login forces a redirect upon successful login.

## SRP Client

The SRP client is Javascript port of the `artisansdk/srp` package's `Client`
class and associated traits. It uses all the same underlying encryption
algorithms and procedures but uses Javascript libraries instead of PHP. The two
classes are `SRP.Client` and `SRP.Config` which can be found in
`resources/scripts/SRP` directory. These are used mainly by `Login.vue` but the
`SRP.Client.enroll()` and `SRP.Client.salt()` methods is used by `Register.vue`
to generate the user's `salt` and `verifier` values during enrollment. Both
`Register.vue` and `Login.vue` components share the `config/srp.php` via Vue
props.

## SRP Server

The SRP server is PHP based and is loaded via Composer from `artisansdk/srp`
package's `Server` class and associated traits. It is dynamically bound into
Laravel's service container using `app/Providers/Auth.php` which is an `Auth`
service provider. Contextual binding is used such that when the controller for
the login screen needs to interact with the SRP server, a cache wrapped instance
based on `App\User\Auth\SRP` is given. The `config/srp.php` config is provided
to the `Server` and the TTL value is given to the `SRP` cache wrapper.

## Secure Remote Password Package

For more information on Secure Remote Password (SRP-6a) protocol and the
`artisansdk/srp` package please visit the [repository README](https://github.com/artisansdk/srp)
for implementation instructions in your own projects.

# Running the Tests

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

Special thanks goes to [simon_massey/thinbus-php-srp](https://bitbucket.org/simon_massey/thinbus-php/src/)
for initial inspiration for both the PHP and JavaScript libraries. This demo
would not be possible without his explanation of the mechanics of Secure Remote
Password protocol.
