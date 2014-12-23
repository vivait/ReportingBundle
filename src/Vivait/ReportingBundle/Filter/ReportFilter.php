<?php

namespace Vivait\ReportingBundle\Filter;


use Doctrine\Common\Collections\Criteria;
use Serializable;
use Vivait\ReportingBundle\Interfaces\ReportOptionInterface;
use Vivait\ReportingBundle\Report\ReportBuilder;

abstract class ReportFilter implements Serializable, ReportOptionInterface
{

    protected $field;

    /** @var ReportBuilder $report */
    private $report;

    protected $serialize_fields = [];

    protected $linked;

    /**
     * @return Criteria
     */
    abstract public function getCriteria();


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
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
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
        $serialize['linked'] = $this->linked;

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
        $this->linked = (isset($data['linked']) && $data['linked']);
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getLinked()
    {
        return $this->linked;
    }

    /**
     * @param mixed $linked
     */
    public function setLinked($linked)
    {
        $this->linked = $linked;
    }

}