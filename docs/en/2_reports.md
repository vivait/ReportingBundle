ReportingBundle
===============

## Reports

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
