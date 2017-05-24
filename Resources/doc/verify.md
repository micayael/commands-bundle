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
                - "echo\\ \\'.*\\'"
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
>
> Characters that can be detected as part of the regular expression
> must be escaped with a single backslash. Example: single quotes,
> double quotes, parenthesis, spaces.
>
> When you want to search for single quotes, you must enclose the
> pattern with double quotation marks, escaping the characters to be
> searched with double backslash. [See this](http://symfony.com/blog/new-in-symfony-2-8-yaml-deprecations#deprecated-non-escaped-in-double-quoted-strings)

Examples
----------------------------

### Search patterns
```php
bin/console app:verify
```
