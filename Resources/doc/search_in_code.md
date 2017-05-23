# MicayaelCommandsBundle

Search In Code command
======================

Find exact texts or patterns within your code, allowing you to define where to look for them

Configure the Bundle
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

Examples
----------------------------

### Search a text into php files
```php
bin/console app:search text_to_find
bin/console app:search --php text_to_find
```

### Search multiple texts on php files, icase sensitive
```php
bin/console app:search -php -i text_to_find1 text_to_find2
```

### Search patterns on php files: $this->get(), dump()
```php
bin/console app:search '\$this\-\>get\('
bin/console app:search 'dump\(' --include-vendors
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

### Search a text php files in verbose mode to show search folders and files found
```php
bin/console app:search --php text_to_find1 -vvv
```
