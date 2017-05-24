# MicayaelCommandsBundle

Pre Commit
==========

Execute "app:verify", "app:phpcs", "app:test" commands before commit changes
to a repository (git, svn)

> **Note:**
>
> This command use the following commands
> - cache:clear
> - app:verify
> - app:phpcs
> - app:test
>
> So they must be configured in advance

Examples
--------

### Search patterns
```php
bin/console app:precommit
```
