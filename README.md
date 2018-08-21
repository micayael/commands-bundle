micayael_commands:
    locale: en

### To enable app:search command

```yaml
micayael_commands:
    search_in_code: ~
```

This configuration will enable the minimum configuration

```yaml
micayael_commands:
    search_in_code:
        app:
            php:
                php: [src]
```

### Examples of possible configurations

```yaml
micayael_commands:
    search_in_code:
        app:
            php:
                php: [src, tests]
            views:
                twig: [templates]
            configs:
                yaml: [config]
                yml: [config]
                xml: [config]
            i18n:
                yml: [translations]
            styles:
                scss:
                    - assets/scss
            scripts:
                js:
                    - assets/js
            assets:
                scss:
                    - assets/scss
                js:
                    - assets/js
        vendors:
            php:
                php: [vendor/micayael/commands-bundle]
```

### 
```yaml
micayael_commands:
    search_in_code:
        default_option: php
```
