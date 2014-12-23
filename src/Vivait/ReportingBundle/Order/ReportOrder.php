<?php

namespace Vivait\ReportingBundle\Order;


use Serializable;
use Vivait\ReportingBundle\Interfaces\ReportOptionInterface;
use Vivait\ReportingBundle\Report\ReportBuilder;

abstract class ReportOrder implements Serializable, ReportOptionInterface
{

    CONST ORDER_BY_NONE = 0;
    CONST ORDER_BY_ASC = 1;
    CONST ORDER_BY_DESC = 2;

    protected $field;
    protected $order;
    private $report;

    protected $serialize_fields = ['order'];


    /**
     * Return a DQL string of the ordering required
     * @return string|null
     */
    abstract public function getOrderBy();

    /**
     * Return a DQL string of the ordering required
     * @return string|null
     */
    public function getOrderDirection()
    {
        return $this->order == self::ORDER_BY_ASC ? 'ASC' : 'DESC';
    }

    /**
     * @param ReportBuilder $report
     */
    public function injectReport(ReportBuilder $report)
    {
        $this->report = $report;
    }

    /**
     * @return ReportBuilder
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        $serialize = [];
        foreach ($this->serialize_fields as $field) {
            $serialize[$field] = $this->$field;
        }

        return serialize($serialize);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        foreach ($this->serialize_fields as $field) {
            if (property_exists($this, $field) && isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }


    /**
     * @return string
     */
    function __toString()
    {
        return $this->getName();
    }


}