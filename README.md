webhooks
========

Gitlab.com & GitHub WebHooks system with PHP and YAML.

Created by [Florian Beer](http://42dev.eu).
Forked from [Maxime Valette's webhooks](https://github.com/betacie/webhooks).

## Setup

1. Clone the repo: `git clone git@github.com:florianbeer/webhooks`
2. Install packages: `composer install`
3. Copy the `config.php.dist` file to `config.php` and customize it
4. Add a virtual host pointing to `web/`

All set! You just have to add a custom WebHook in the Service Hooks of your Gitlab.com or GitHub repositories, pointing to `web/hooks.php`.

## Hooks file

The whole point of this script is that you can add custom hooks file specific to GitHub repositories.

It's a simple YAML file that looks like this:

~~~
emails:
  - john@acmewebsite.com
master:
  - /usr/local/bin/composer install
  - php artisan migrate --env=production
~~~

So you can easily add or remove commands executed after every push.