ReportingBundle
===============

Reporting framework that has to be extended to suit the application

Please note that this bundle is not an off the shelf reporting application and is more a framework that has to be built inside your application.

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

## Usage

The reporting framework allows you to create classes that interact with each other so that useful and meaningful reports can be build without the complexities of dealing with the SQL queries directly.


To create a new report you must first create a new class that extends Vivait\ReportingBundle\Report\ReportBuilder and fill in the following abstract functions. Below is an example.

```php

use Vivait\ReportingBundle\Report\ReportBuilder;
use Doctrine\ORM\QueryBuilder;

class MyReport extends ReportBuilder
{

    /**
     * @param EntityManager $entityManager
     * @param SecurityContextInterface $securityContext
     */
    function __construct(EntityManager $entityManager, SecurityContextInterface $securityContext)
    {
        $this->em = $entityManager;
        $this->securityContext = $securityContext;
    }
    
    public function getTitle()
    {
        return 'Lead Counts';
    }

    /**
     * Returns the descripion of the report, this will be displayed in the title, menu links, etc
     * @return string
     */
    public function getDescription()
    {
        return 'This report shows the total count of leads';
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
      $qb = $this->em->createQueryBuilder('l')
        ->select('COUNT(l) as total')
        ->from('VivaitMyApplicationBundle:Leads', 'l')
        ->join('l.customer', 'c')
        ->where('c.class = :class')
        ->setParameter('class', $this->securityContext->getToken()->getUser()->getClass());

        return $qb;
    }

    /**
     * Return an array that maps the output of the getQueryBuilder into columns in the report
     * @return array
     */
    public function getBaseColumnMapping()
    {
        return [
            'total' => [
                'label' => 'Total',
                'data' => true
            ]

        ];
    }
}
```

The next step is to ensure that this report is tagged as a report in the service container. In your services.yml file:

```yaml
...
    vivait_reporting.applications:
        class: Vivait\MyAppBundle\Report\MyReport
        arguments: ["@doctrine.orm.default_entity_manager", "@security.context"]
        tags:
            -  { name: vivait_reporting.report }
...
```

This basic report will select a single value from the database and display it as a single value. Obviously not of much use on it's own but the power of the framework is being able to add Filters and Groups.

## Creating a Filter

Lets say you want to filter the report based on a date that is stored in a table. As long as the getQueryBuilder gives you access to the alias which you can filter then it should be fairly straight forward.

Create a new class that extends Vivait\ReportingBundle\Filter\ReportFilter

The important elements to the class are the field that can be passed in from the constructor and the serialize_fields property which will inform the framework which fields of this class are to be persisted.

This filter can be reuseable between reports:

```php

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Form\AbstractType;
use Viva\BravoBundle\Report\Form\Type\DateRangeFilterType;
use Vivait\ReportingBundle\Filter\ReportFilter;

class DateRangeFilter extends ReportFilter
{
    /**
     * @var \DateTime
     */
    protected $from;


    /**
     * @var \DateTime
     */
    protected $to;

    protected $serialize_fields = ['from','to'];

    function __construct($field)
    {
        $this->field = $field;
        $this->from = new \DateTime();
        $this->to = new \DateTime();
    }


    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where(
            $expr->andX(
                $expr->gte($this->getField(), $this->from),
                $expr->lte($this->getField(), $this->to)
            )
        );
        return $criteria;
    }


    public function getName() {
        return 'Date Range';
    }

    public function getOptions() {
        return $this->from->format('d/m/Y') . ' - ' . $this->to->format('d/m/Y');
    }

    /**
     * @return AbstractType
     */
    public function getFormType()
    {
        return new DateRangeFilterType();
    }


    /**
     * @return \DateTime
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param \DateTime $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return \DateTime
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param \DateTime $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

```

Then create the form type that will be used by the above class for letting the user amend the date range

```php

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vivait\ReportingBundle\Form\Type\ReportFilterTypeInterface;

class DateRangeFilterType extends ReportFilterTypeInterface
{

    public function buildForm(FormBuilderInterface $form, array $options)
    {
        $form->add(
            'from',
            'date',
            array(
                'label'    => 'Date From',
                'required' => true,
                'format'   => 'dMMMyyyy',
            )
        );
        $form->add(
            'to',
            'date',
            array(
                'label'    => 'Date To',
                'required' => true,
                'format'   => 'dMMMyyyy',
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'report_filter_daterange';
    }

```

Once you have this then in the constructor of your report class simply add the filter on the field you wish to filter by:

```php
  function __construct(...)
  ...
    $this->addFilter('filter_daterange', new DateRangeFilter('l.added'));
  ...
```

This will now provide a filter on the alias.field 'l.added'.

!! Important Note, there is an issue with Doctrine [*DDC-3108*](https://github.com/doctrine/doctrine2/pull/1212) that has a pull-request open. This means that you will not be able to use aliases from anything but the base table (ie Joins) until it has been resolved. You can use a fork of Doctrine with the bug fix by modifying your composer.json 

WARNING, this is a custom fork of dev-master until the pull request is accepted, it will not receive security or bug fixes.

```yaml
        "doctrine/orm": "dev-master#da8de433b656e681354817bc6b3294a20eabf4ac as dev-master",
```

## Groups

Okay so you've got filters but it still will only return a scalar value, how about grouping things so you can have two (or more) dimensions of data, well there is another class called the ReportGroup. To use, create a new class that extends  Vivait\ReportingBundle\Group\ReportGroup:

Like before there is the actual class that fleshes out the details and another that provides the form to select the groupings. As groups can also specify an ordering it might be worth while to do this in here rather than create a ReportOrder (for ordering of the data).

By default the parent class will persist the group and order properties in the class, if you need to persist additional information then you will need to override $serialize_fields in the class

```php
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Form\AbstractType;
use Vivait\MyAppBundle\Report\Form\Type\DateGroupType;
use Vivait\ReportingBundle\Group\ReportGroup;
use Vivait\ReportingBundle\Interfaces\ReportGroupOrderableInterface;

class DateGroup extends ReportGroup implements ReportGroupOrderableInterface
{

    CONST GROUP_BY_OFF = 0;
    CONST GROUP_BY_MONTH = 1;
    CONST GROUP_BY_WEEK = 2;
    CONST GROUP_BY_DAY = 3;
    CONST GROUP_BY_HOUR = 4;
    CONST GROUP_BY_YEAR = 5;
    CONST GROUP_BY_DAY_OF_WEEK = 6;

    function __construct($field, $label)
    {
        $this->label = $label;
        $this->field = $field;
        $this->group = self::GROUP_BY_OFF;
    }

    /**
     * @return Criteria
     */
    public function getGroupBy()
    {
        if ($this->group) {
            return $this->getAlias();
        }

        return '';
    }

    public static function getAllChoices()
    {
        return [
            DateGroup::GROUP_BY_OFF   => 'Off',
            DateGroup::GROUP_BY_YEAR  => 'Year',
            DateGroup::GROUP_BY_MONTH => 'Month',
            DateGroup::GROUP_BY_WEEK  => 'Week Number of Year',
            DateGroup::GROUP_BY_DAY   => 'Date',
            DateGroup::GROUP_BY_DAY_OF_WEEK  => 'Day of Week',
            DateGroup::GROUP_BY_HOUR  => 'Hour in Day',
        ];
    }

    public function getSelect()
    {
        if ($this->group == self::GROUP_BY_MONTH) {
            $select = sprintf("SUBSTRING(%s, 1, 7)", $this->field);
        } elseif ($this->group == self::GROUP_BY_WEEK) {
            $select = sprintf("WEEK(%s)", $this->field);
        } elseif ($this->group == self::GROUP_BY_DAY) {
            $select = sprintf("SUBSTRING(%s, 1, 10)", $this->field);
        } elseif ($this->group == self::GROUP_BY_HOUR) {
            $select = sprintf("HOUR(%s)", $this->field);
        } elseif ($this->group == self::GROUP_BY_YEAR) {
            $select = sprintf("YEAR(%s)", $this->field);
        } elseif ($this->group == self::GROUP_BY_DAY_OF_WEEK) {
            $select = sprintf("DAYNAME(%s)", $this->field);
        } elseif ($this->group == self::GROUP_BY_OFF) {
            return null;
        } else {
            throw new \Exception('Unknown group type');
        }

        $select .= ' as ' . $this->getAlias();

        return $select;
    }

    public function getName()
    {
        return 'Time/Date';
    }


    /**
     * @return AbstractType
     */
    public function getFormType()
    {
        return new DateGroupType();
    }

    /**
     * Returns the column mappings used by the group by
     * @return array
     */
    public function getColumnMapping()
    {
        if ($this->group) {
            return [
                $this->getAlias() => [
                    'label'   => $this->getOptions(),
                    'grouped' => true
                ],
            ];
        }

        return [];
    }

    /**
     * @return array|null
     */
    public static function getAllOrderChoices()
    {
        return [
            self::ORDER_BY_NONE   => 'None',
            self::ORDER_BY_ASC   => 'Oldest - Newest',
            self::ORDER_BY_DESC   => 'Newest - Oldest',
        ];
    }

```

In this particular class there are some custom doctrine fields used including one included in the bundle called DayName that is included in the framework but must be activated in your config.yml

```yaml
doctrine:
  ...
  orm:
    ...
    dql:
      ...
        datetime_functions:
          ...
          month: DoctrineExtensions\Query\Mysql\Month
          year: DoctrineExtensions\Query\Mysql\Year
          week: DoctrineExtensions\Query\Mysql\Week
          date: DoctrineExtensions\Query\Mysql\Date
          day: DoctrineExtensions\Query\Mysql\Day
          hour: DoctrineExtensions\Query\Mysql\Hour
          dateformat: DoctrineExtensions\Query\Mysql\DateFormat
          dayname: Vivait\ReportingBundle\Query\Mysql\DayName
```

and create the form type to be used:

```php


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Viva\BravoBundle\Report\Group\DateGroup;
use Vivait\ReportingBundle\Form\Type\ReportFilterTypeInterface;

class DateGroupType extends ReportFilterTypeInterface
{

    public function buildForm(FormBuilderInterface $form, array $options)
    {
        $form->add(
            'group',
            'choice',
            array(
                'label'    => 'Group By',
                'required' => true,
                'choices' => DateGroup::getAllChoices(),
            )
        );
        $form->add(
            'order',
            'choice',
            array(
                'label'    => 'Ordering',
                'required' => true,
                'choices' => DateGroup::getAllOrderChoices(),
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'report_group_daterange';
    }
```

## Graphs

So now you have some multi-dimensional data it might look better on a graph than in a table.

TBC...
