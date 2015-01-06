<?php

namespace Vivait\ReportingBundle\Group;

use Serializable;
use Vivait\ReportingBundle\Interfaces\ReportGroupOrderableInterface;
use Vivait\ReportingBundle\Interfaces\ReportOptionInterface;
use Vivait\ReportingBundle\Report\ReportBuilder;

abstract class ReportGroup implements Serializable, ReportOptionInterface
{

    CONST ORDER_BY_NONE = 0;
    CONST ORDER_BY_ASC = 1;
    CONST ORDER_BY_DESC = 2;

    protected $field;
    protected $label;
    protected $group;
    protected $order;
    private $report;

    protected $serialize_fields = ['group', 'order'];

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
     * Returns the DQL group by field
     * @return mixed|string
     */
    public function getGroupBy()
    {
        if ($this->group) {
            return $this->getAlias();
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
     * Returns a DQL string of the additional selects that are needed to run the reports with the group by
     * @return string|null
     */
    abstract public function getSelect();

    /**
     * Returns the column mappings used by the group by
     * @return array
     */
    abstract public function getColumnMapping();


    /**
     * Returns a unique alias to be used for mapping DQL to Labels
     */
    public function getAlias()
    {
        return preg_replace("/[^A-Za-z0-9]/", '', $this->field . $this->label);
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
     * @return string
     */
    public function getOptions()
    {
        $options = 'Unknown';
        foreach ($this->getAllChoices() as $key => $choice) {
            if ($this->group == $key) {
                $options = $choice;
            }
        }

        if ($this->group && $this->order && $this instanceof ReportGroupOrderableInterface) {
            foreach ($this->getAllOrderChoices() as $key => $choice) {
                if ($this->getOrder() == $key) {
                    $options .= ' (' . $choice . ')';
                }
            }
        }

        return $options;
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
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
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
    public function getName()
    {
        return $this->label;
    }



}