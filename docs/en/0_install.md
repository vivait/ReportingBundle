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

Create a link to the Reporting Centre:
```twig
<a href="{{ path('vivait_reporting') }}">Reporting Centre</a>
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
