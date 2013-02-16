webhooks
========

GitHub WebHooks system with PHP and YAML.

Created by [Maxime VALETTE](http://maxime.sh) for [Beta&Cie](http://www.betacie.com).

## Setup

1. Clone the repo: `git clone git@github.com:betacie/webhooks`
2. Install packages: `composer install`
3. Copy the `config.php.dist` file to `config.php` and custom it
4. Add a virtual host pointing to `web/`

All set! You just have to add a custom WebHook in the Service Hooks of your GitHub repositories, pointing to `web/hooks.php`.

## Hooks file

The whole point of this script is that you can add custom hooks file specific to GitHub repositories.

It's a simple YAML file that looks like this:

~~~
emails:
  - john@acmewebsite.com
master:
  - /usr/local/bin/composer install
  - php ./app/console doctrine:schema:drop --force
  - php ./app/console doctrine:schema:update --force
  - php ./app/console doctrine:fixtures:load -n
  - php ./app/console assets:install web --symlink
  - php ./app/console assetic:dump --env=staging --no-debug
  - php ./app/console cache:clear --env=staging
~~~

So you can easily add or remove commands executed after every push.

## TODO

- Triggers in commit message