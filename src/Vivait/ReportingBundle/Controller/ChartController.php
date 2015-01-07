<?php

namespace Vivait\ReportingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Vivait\ReportingBundle\Entity\Report;

class ChartController extends Controller
{
    public function getChartAction(Report $report, $chart_alias, $js = true)
    {
        try {
            $report_obj = $this->get('vivait_reporting')->getReport($report->getReportService(), $report);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        if ($report_obj->hasChart($chart_alias)) {
            $chart = $report_obj->getChart($chart_alias);
        } else {
            throw $this->createNotFoundException(sprintf('Chart "%s" not be found for report', $chart_alias));
        }

        return $this->render('VivaitReportingBundle:Chart:single_chart.html.twig', [
            'alias' => $chart_alias,
            'chart' => $chart,
            'report' => $report_obj,
            'js' => $js,
        ]);
    }
}
