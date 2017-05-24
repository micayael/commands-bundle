# MicayaelCommandsBundle

This bundle add useful commands to your project.

The bundle includes:

  - **app:search:** Find exact texts or patterns within your code, allowing you to define where to look for them

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require micayael/commands-bundle
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

Full Documentation and examples
-------------------------------

- [Search in Code - app:search](https://github.com/micayael/commands-bundle/blob/master/Resources/doc/search_in_code.md) 
- [Verify Code - app:verify](https://github.com/micayael/commands-bundle/blob/master/Resources/doc/verify.md) 
