<?php

namespace Vivait\ReportingBundle\Twig;


class VivaitTwigReportingExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('md5', array($this, 'md5Filter')),
            new \Twig_SimpleFilter('sha1', array($this, 'sha1Filter')),
        );
    }

    public function md5Filter($data)
    {
        return md5($data);
    }

    public function sha1Filter($data)
    {
        return sha1($data);
    }

    public function getName()
    {
        return 'vivait_twig_reporting_extension';
    }
}