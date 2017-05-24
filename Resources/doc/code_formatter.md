# MicayaelCommandsBundle

Code Formatter
==============

Format de code using php-cs-fixer

> **Note:**
>
> [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) is required to be installed

Configure the Bundle
--------------------

### Minimal Default configuration

```yaml
micayael_commands:
    code_formatter:
        phpcsfixer_bin: %php_cs_fixer.binary%
```

### Add php-cs-fixer configuration (optional)

Create a file ".php_cs" in the root of the project with the 
following content indicating the directories that contain 
code to format and applying Symfony standards

[More Info](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

```
<?php

$finder = PhpCsFixer\Finder::create()
    ->in('app')
    ->in('src')
    ->in('tests')
    ->in('web')
;

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;
```

Examples
--------

### Search patterns
```php
bin/console app:phpcs
```
