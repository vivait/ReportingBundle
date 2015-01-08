ReportingBundle
===============

## Groups

Okay so you've got filters but it still will only return a scalar value, how about grouping things so you can have two (or more) dimensions of data, well there is another class called the ReportGroup. To use, create a new class that extends  Vivait\ReportingBundle\Group\ReportGroup:

Like before there is the actual class that fleshes out the details and another that provides the form to select the groupings. As groups can also specify an ordering it might be worth while to do this in here rather than create a ReportOrder (for ordering of the data).

By default the parent class will persist the group and order properties in the class, if you need to persist additional information then you will need to override $serialize_fields in the class.

### A simple grouping
 
Most groupings are a simple off/on with an ordering, in most cases you can use the pre-supplied class below to create a group and pass the grouping field and label in via the constructor
 
```php

  use Vivait\ReportingBundle\Report\Group\GenericGroup;

  function __construct(...)
  ...
    $this->addGroup('group_called', new GenericGroup('u.called', 'Called'));
  ...

```
 
 
### A not so simple grouping
 
 Although most groups are going to be on or off, the date grouping can be used to group by dates, days, months, weeks, etc. This requires a little extra configuration as the $this->group property will need to store more than just a boolean off/on.

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
use Vivait\ReportingBundle\Form\Type\ReportFilterType;

class DateGroupType extends ReportFilterType
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

Don't forget to add the group into the report:

```php

  use Vivait\MyAppBundle\Report\Group\DateGroup;

  function __construct(...)
  ...
    $this->addGroup('group_called', new DateGroup('u.date', 'Date Range'));
  ...

```
