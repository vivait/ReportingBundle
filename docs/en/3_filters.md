ReportingBundle
===============

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
use Vivait\ReportingBundle\Form\Type\ReportFilterType;

class DateRangeFilterType extends ReportFilterType
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