<?php

namespace Vivait\ReportingBundle\Interfaces;

use Symfony\Component\Form\FormTypeInterface;

interface ReportOptionInterface
{
    /**
     * @return FormTypeInterface
     */
    function getFormType();

    /**
     * @return string
     */
    function getName();

    /**
     * @return string
     */
    function getOptions();

    /**
     * @return string
     */
    function __toString();

}