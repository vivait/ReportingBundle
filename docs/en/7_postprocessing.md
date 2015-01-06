ReportingBundle
===============

## Post Processing Data

In some cases it may be neccesary to post process data returned from the query.

The common use case would be for statuses that are stored in the database as an integer and displaying them as a descriptive string

By inserting the 'post_process_callback' key into the column mappings array the framework will pass the data as an array into your 
chosen function and expect an array back (with the same count in the same order) with the translated values.

For example, if you were grouping by a status column, in your extension of the ReportGroup class you would perform the following:

```php`

    /**
     * Returns the column mappings used by the group by
     * @return array
     */
    public function getColumnMapping()
    {
        if ($this->group) {
            return [
                $this->getAlias() => [
                    'label'   => $this->label,
                    'grouped' => true,
                    'post_process_callback'=>[get_class($this),'postProcessValues']
                ],
            ];
        }

        return [];
```

and then in the same class a function that accepts the array (if can be in a different class if you wish). Please note this function will
be called interactively with the render so database lookups are strongly discouraged.

```php

    /**
     * In some cases it might be necessary to post process an ID field into a human readable format.
     * Override this class if you need that functionality. Strongly advise against a database lookup here!
     * @param array $values
     * @return array
     */
    public static function postProcessValues($values) {
        array_walk($values,function(&$row) {
            $row = Status::getStaticStatusName($row);
        });
        return $values;
    }


```