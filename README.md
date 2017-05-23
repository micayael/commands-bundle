Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require micayael/commands-bundle "master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Micayael\CommandsBundle\MicayaelCommandsBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundle
----------------------------

### Minimal configuration

```yaml
micayael_commands:
    options:
        project:
            php:
                php: [src]
```

### Full configuration

```yaml
micayael_commands:
    options:
        project:
            php:
                php: [src]
            views:
                twig:
                    - app/Resources/views
                    - src/AppBundle/Resources/views
            config: ~ # To not search on settings
            styles:
                scss:
                    - app/Resources/assets/src/sass
            scripts:
                js:
                    - app/Resources/assets/src/js
        vendors: 
            php:
                php:
                    - vendor/micayael/commands-bundle/DependencyInjection
            config:
                yml:
                    - vendor/micayael/commands-bundle/Resources
```

Step 4: Use the command
----------------------------

### Search a text into php files
```php
bin/console app:search text_to_find
```

### Search multiple texts on php files
```php
bin/console app:search text_to_find1 text_to_find2
```

### Search patterns on php files
```php
bin/console app:search '\-\>indexAction\('
```

### Search a text into configs en javascripts
```php
bin/console app:search --config --scripts text_to_find1
```

### Search a text into assets (scripts & styles)
```php
bin/console app:search --scripts --styles text_to_find1
bin/console app:search --assets text_to_find1
```

### Search a text into all files defined in config.yml
```php
bin/console app:search --all text_to_find1
```

### Search a text into all files defined in config.yml, included vendors
```php
bin/console app:search --all --include-vendors text_to_find1
```

### Search a text php files, included vendors
```php
bin/console app:search --php --include-vendors text_to_find1
```
