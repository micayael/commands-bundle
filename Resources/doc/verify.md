# MicayaelCommandsBundle

Verify the Code
===============

Look for common errors in the code within the project

Configure the Bundle
----------------------------

### Minimal Default configuration

```yaml
micayael_commands:
    verify:
        patterns:
            php:
                - 'dump\(.*\)'
                - 'die\(.*\)'
                - 'exit\(.*\)'
                - 'echo\(.*\)'
                - 'echo\ \(.*\)'
                - "echo\\ \\'.*\\'" #Double backslashed because deprecation: http://symfony.com/blog/new-in-symfony-2-8-yaml-deprecations#deprecated-non-escaped-in-double-quoted-strings
                - 'echo\ \".*\"'
                - 'print_r\(.*\)'
                - 'var_dump\(.*\)'
                - '->debug\(.*\)'
            views:
                - 'dump\(.*\)'
            scripts:
                - 'console.log\(.*\)'
                - 'console.dir\(.*\)'
                - 'console.info\(.*\)'
                - 'console.debug\(.*\)'
                - 'console.error\(.*\)'
```

> **Note:**
>
> This command use the [configuration](https://github.com/micayael/commands-bundle/blob/master/Resources/doc/search_in_code.md) 
of the **app:search** command to know where to search theese patterns

Examples
----------------------------

### Search patterns
```php
bin/console app:verify
```
