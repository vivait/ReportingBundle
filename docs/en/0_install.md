ReportingBundle
===============

## Install

Add `"vivait/reporting-bundle": "~0.1"` to your composer.json and run `composer update`.

[*Check latest releases*](https://github.com/vivait/ReportingBundle/releases)

Update your `AppKernel`:
```php
public function registerBundles()
{
    $bundles = array(
        ...
        new Vivait\ReportingBundle\VivaitReportingBundle(),
}
```

Update your `app/config/routing.yml`:
```yaml
...
vivait_reporting:
    resource: "@VivaitReportingBundle/Resources/config/routing.yml"
    prefix:   /
...
```

Create a link to the Reporting Framework:
```twig
<a href="{{ path('vivait_reporting') }}">Reporting</a>
```

Ensure your user entity implements `Vivait\ReportingBundle\Model\ReportingUserInterface`:

```php
class User extends BaseUser implements ReportingUserInterface
{
  ...
}
```

And let this bundle know what your User entity is:

```yaml
vivait_reporting:
    user_class: Vivait\MyAppBundle\Entity\User
```

This bundle requires an update to your database schema so perform that in the correct way for your application:

```shell
php app/console doctrine:schema:update
```

or better using migrations:

```shell
php app/console doctrine:migrations:diff
php app/console doctrine:migrations:migrate
```

Finally as we are installing new front-end files perform an assetic:dump

```shell
php app/console assetic:dump
```