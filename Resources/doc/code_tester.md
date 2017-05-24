# MicayaelCommandsBundle

Code Tester
===========

Execute unit tests using phpunit

> **Note:**
>
> [phpunit](https://github.com/sebastianbergmann/phpunit) is required to be installed

Configure the Bundle
--------------------

### Minimal Default configuration

```yaml
micayael_commands:
    code_tester:
        phpunit_bin: %phpunit.binary%
```

Examples
--------

### Search patterns
```php
bin/console app:test
```
