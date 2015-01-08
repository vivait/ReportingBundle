<?php

namespace Vivait\ReportingBundle\Interfaces;

use Symfony\Component\Form\FormTypeInterface;

interface ReportOptionInterface
{
    /**
     * @return FormTypeInterface
     */
    public function getFormType();

    /**
     * @return string
     */
    public function getOptions();

    /**
     * @return string
     */
    public function __toString();

}