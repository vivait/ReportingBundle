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
    protected $label;
    private $report;

    protected $serialize_fields = ['order'];

    /**
     * @param $field string     This is the DQL field to order by
     * @param string $label     This is the label to be displayed on the order selection dialog
     */
    function __construct($field, $label)
    {
        $this->field = $field;
        $this->label = $label;
        $this->order = self::ORDER_BY_NONE;
    }

    /**
     * Return a DQL string of the ordering required
     * @return string|null
     */
    public function getOrderBy()
    {
        if ($this->order) {
            return $this->field;
        }

        return '';
    }

    /**
     * Return a DQL string of the ordering required
     * @return string
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
        return $this->getLabel();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getName() {
        return $this->label;
    }


    /**
     * @return string
     */
    public function getOptions()
    {
        foreach ($this->getAllChoices() as $key => $choice) {
            if ($this->order == $key) {
                return $choice;
            }
        }

        return 'Unknown';
    }
}