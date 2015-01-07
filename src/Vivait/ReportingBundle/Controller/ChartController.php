<?php

namespace Vivait\ReportingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Vivait\ReportingBundle\Entity\Report;

class ChartController extends Controller
{
    public function getChartAction(Report $report, $chart_alias, $js = true)
    {
        $reporting = $this->get('vivait_reporting');
        try {
            $report_obj = $reporting->getReport($report->getReportService(), $report);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        if (!$report_obj->hasChart($chart_alias)) {
            throw $this->createNotFoundException(sprintf('Chart "%s" not be found for report', $chart_alias));
        }

        $data = $reporting->getComparisonData($report, $report_obj->getQuery(), $report_obj->getColumnMapping());

        return $this->render('VivaitReportingBundle:Chart:single_chart.html.twig', [
            'alias' => $chart_alias,
            'chart' => $report_obj->getChart($chart_alias),
            'data'              => $data['values'],
            'columnmapping'     => $data['mappings'],
            'comparison_status' => $data['comparison_status'],
            'js' => $js,
        ]);
    }
}
