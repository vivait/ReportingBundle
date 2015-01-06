ReportingBundle
===============

## Orders

Orderings are a little different, although from an SQL level they alter the ORDER BY command, it is much more likely that the groupings will handle the ordering. As a non-grouped
report is very basic (i.e. just a single value and order isn't going to be too much help. However if you have none of the groups using an order this could be the 'catch-all' order that
will be performed at the very end and can be still useful.


### A simple ordering
 
Most orderings are going to be a simple ascending or descending on a value, there are two classes that take care of this with specific descriptions for a string
 based order (A-Z), numeric based order (low-high) and a date based order (oldest-newest), from an SQL level they are identical.
 
```php

  use Vivait\ReportingBundle\Report\Group\GenericGroup;

  function __construct(...)
  ...
    $this->addOrder('order.', new GenericDateOrder($this,'u.called','Date Called'));
  ...

```
 