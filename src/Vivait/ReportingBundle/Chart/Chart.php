<?php

namespace Vivait\ReportingBundle\Chart;


use Vivait\ReportingBundle\Report\ReportBuilder;

abstract class Chart
{

    /** @var ReportBuilder */
    private $report;
    protected $field;

    /**
     * Construct the chart object with the mappings to use
     * @param ReportBuilder $report
     * @param $field int        The field is the numerical value to use for the graphs
     */
    function __construct(ReportBuilder $report, $field)
    {
        #we need a link to the report so we can pull mappings from it at a late stage
        $this->report = $report;
        $this->field = $field;
    }

    /**
     * Return the twig template to use
     * @return string
     */
    abstract function getTemplate();

    /**
     * Return the options to be used in the graph
     * @param array $columnmapping
     * @return array
     */
    abstract function getOptions(array $columnmapping);

    /**
     * Get the column mappings of the report
     * @return array
     */
    public function getColumnMapping()
    {
        return $this->getReport()->getColumnMapping();
    }

    /**
     * @return ReportBuilder
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Get the number of dimentions the report has been grouped by
     * @param $mappings
     * @return array
     */
    public function getDimensions($mappings)
    {
        $groups = [];

        foreach ($mappings as $key => $mapping) {
            if (isset($mapping['grouped']) && $mapping['grouped']) {
                $groups[] = $key;
            }
        }

        return $groups;
    }

    /**
     * Converts general purpose tabluar data into something that can be used by the graphs
     * @param array $data
     * @param array $columnmapping
     * @return array
     */
    public abstract function getGraphData(array $data, array $columnmapping);


}