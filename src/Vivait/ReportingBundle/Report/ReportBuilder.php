<?php

namespace Vivait\ReportingBundle\Report;


use Doctrine\ORM\QueryBuilder;
use Vivait\ReportingBundle\Chart\Chart;
use Vivait\ReportingBundle\Filter\ReportFilter;
use Vivait\ReportingBundle\Group\ReportGroup;
use Vivait\ReportingBundle\Order\ReportOrder;

abstract class ReportBuilder
{

    /**
     * @var ReportFilter[]
     */
    protected $filters = [];

    /**
     * @var ReportGroup[]
     */
    protected $groups = [];

    /**
     * @var ReportOrder[]
     */
    protected $orders = [];

    /**
     * @var Chart[]
     */
    protected $charts = [];

    /**
     * Returns the name of the report, this will be displayed in the title, menu links, etc
     * @return string
     */
    abstract public function getTitle();

    /**
     * Returns the description of the report, this will be displayed in the title, menu links, etc
     * @return string
     */
    abstract public function getDescription();

    /**
     * @return QueryBuilder
     */
    abstract function getQueryBuilder();

    /**
     * @return array
     */
    abstract function getBaseColumnMapping();

    /**
     * Returns the entire set of column mappings used by this report
     * @return array
     */
    public function getColumnMapping()
    {
        $mappings = [];

        foreach ($this->getGroups() as $group) {
            $mappings = array_merge($mappings, $group->getColumnMapping());
        }
        $mappings = array_merge($mappings, $this->getBaseColumnMapping());

        return $mappings;
    }

    /**
     * Get the number of dimentions the report has been grouped by
     * @return array
     */
    public function getDimensions()
    {
        $mappings = $this->getColumnMapping();
        $groups = [];
        foreach ($mappings as $key => $mapping) {
            if (isset($mapping['grouped']) && $mapping['grouped']) {
                $groups[] = $key;
            }
        }

        return $groups;
    }

    /**
     * @param ReportFilter []
     * @param $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return ReportFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $filter
     * @return null|ReportFilter
     */
    public function getFilter($filter)
    {
        if (isset($this->filters[$filter])) {
            return $this->filters[$filter];
        }

        return null;
    }

    /**
     * @return Chart[]
     */
    public function getCharts()
    {
        return $this->charts;
    }


    /**
     * @param ReportGroup []
     * @param $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return ReportGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param $group
     * @return null|ReportGroup
     */
    public function getGroup($group)
    {
        if (isset($this->groups[$group])) {
            return $this->groups[$group];
        }

        return null;
    }

    /**
     * @param $order
     * @return null|ReportOrder
     */
    public function getOrder($order)
    {
        if (isset($this->orders[$order])) {
            return $this->orders[$order];
        }

        return null;
    }

    /**
     * @return ReportOrder[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param ReportOrder[] $orders
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
    }

    /**
     * Add a chart to the report
     * @param $alias
     * @param Chart $chart
     * @return $this
     */
    protected function addChart($alias, Chart $chart)
    {
        $this->charts[$alias] = $chart;

        return $this;
    }

    /**
     * Add a filter to the report
     * @param $alias
     * @param ReportFilter $filter
     * @return $this
     */
    protected function addFilter($alias, ReportFilter $filter)
    {
        $this->filters[$alias] = $filter;
    }

    /**
     * Add an order to the report
     * @param $alias
     * @param ReportOrder $order
     * @return $this
     */
    protected function addOrder($alias, ReportOrder $order)
    {
        $this->orders[$alias] = $order;
    }

    /**
     * Add a group to the report
     * @param $alias
     * @param ReportGroup $group
     * @return $this
     */
    protected function addGroup($alias, ReportGroup $group)
    {
        $this->groups[$alias] = $group;
    }

    public function getQuery()
    {

        $qb = $this->getQueryBuilder();

        foreach ($this->filters as $filter) {
            if ($filter->getCriteria()) {
                $qb->addCriteria($filter->getCriteria());
            }
        }

        foreach ($this->groups as $group) {
            if ($group->getGroupBy()) {
                $qb->addSelect($group->getSelect());
                $qb->addGroupBy($group->getGroupBy());
                if ($group->getOrderBy()) {
                    $qb->addOrderBy($group->getOrderBy(), $group->getOrderDirection());
                }
            }
        }

        foreach ($this->orders as $order) {
            if ($order->getOrderBy()) {
                $qb->addOrderBy($order->getOrderBy(), $order->getOrderDirection());
            }
        }

        $query = $qb->getQuery();

        return $this->postProcessData($query->getResult());
    }

    /**
     * Post process the data from the database results (ie might need to convert numeric dates to a different format)
     * This function will place an entire column into the callback so no need to constantly load/unload classes
     * @param $data
     * @return mixed
     */
    public function postProcessData($data)
    {
        $mappings = $this->getColumnMapping();

        foreach ($mappings as $key => $mapping) {
            if (isset($mapping['post_process_callback'])) {
                $column_data = array_column($data, $key);
                $column_data = call_user_func($mapping['post_process_callback'], $column_data);

                #reintegrate the column data into the array
                array_walk(
                    $data,
                    function (&$row, $index) use ($key, $column_data) {
                        $row[$key] = $column_data[$index];
                    }
                );

            }
        }

        return $data;
    }

    public function __clone()
    {
        foreach ($this->filters as $key => $row) {
            $this->filters[$key] = clone $row;
        }
        foreach ($this->groups as $key => $row) {
            $this->groups[$key] = clone $row;
        }
        foreach ($this->orders as $key => $row) {
            $this->orders[$key] = clone $row;
        }
    }


}