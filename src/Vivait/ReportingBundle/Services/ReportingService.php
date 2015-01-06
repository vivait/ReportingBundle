<?php

namespace Vivait\ReportingBundle\Services;


use Vivait\ReportingBundle\Report\ReportBuilder;

class ReportingService
{

    /** @var ReportBuilder[] */
    private $reports;


    /**
     * @param $name
     * @param ReportBuilder $report
     */
    public function addReport($name, $report)
    {
        $this->reports[$name] = $report;
    }


    /**
     * @return ReportBuilder[]
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param $report_data
     * @param $report_mapping
     * @param $comparison_data
     * @param $comparison_mapping
     * @return array
     */
    public function compareData($report_data, $report_mapping, $comparison_data, $comparison_mapping)
    {
        $status = false;

        $report_dimensions = count($this->getDimensions($report_mapping));
        $comparison_dimensions = count($this->getDimensions($comparison_mapping));
        $shared_columns = $this->getSharedGroupedColumns($report_mapping, $comparison_mapping);

        if (($report_dimensions >= $comparison_dimensions) && (count($shared_columns) == $comparison_dimensions)) {

            $report_data = $this->generateGroupHash($report_data, $shared_columns);
            $comparison_data = $this->generateGroupHash($comparison_data, $shared_columns);
            #this report can be compared!

            $comparison_hashes = $this->getComparisonValues($comparison_data, $comparison_mapping);

            $data_column = $this->getDataColumn($report_mapping);

            array_walk(
                $report_data,
                function (&$row, $index) use ($data_column, $comparison_hashes) {
                    if (isset($comparison_hashes[$row['hash']])) {
                        if (!isset($comparison_hashes[$row['hash']])) {
                            $row[$data_column] = '-';
                        } elseif($comparison_hashes[$row['hash']] == 0) {
                            $row[$data_column] = '∞';
                        } else {
                            $row[$data_column] = round($row[$data_column] * 100 / $comparison_hashes[$row['hash']], 2);
                        }
                    }
                }
            );

            $status = true;
        }


        return [
            'values'            => $report_data,
            'mappings'          => $report_mapping,
            'comparison_status' => $status
        ];

    }

    /**
     * Returns the data column in a mapping
     * @param $mappings
     * @return int|null|string
     */
    private function getDataColumn($mappings)
    {
        foreach ($mappings as $key => $heading) {
            if (isset($heading['data']) && $heading['data']) {
                return $key;
            }
        }

        return null;
    }

    private function getComparisonValues($comparison_data, $comparison_headings)
    {

        $hash_data = [];
        $data_key = $this->getDataColumn($comparison_headings);

        if ($data_key) {
            foreach ($comparison_data as $key => $data) {
                $hash_data[$data['hash']] = $data[$data_key];
            }
        }

        return $hash_data;
    }

    /**
     * Generates the hash of the grouped data for further comparison
     * @param $data
     * @param $shared_columns
     * @return mixed
     */
    private function generateGroupHash($data, $shared_columns)
    {
        foreach ($data as &$row) {
            $hash = '';
            foreach ($shared_columns as $key => $column) {
                $hash .= $row[$column] . ',';
            }
            $hash = sha1($hash);
            $row['hash'] = $hash;
        }

        return $data;
    }

    /**
     * Evaluates both mappings and identifies the shared grouped columns
     * @param array $report_mapping
     * @param array $comparison_mapping
     * @return array
     */
    private function getSharedGroupedColumns($report_mapping, $comparison_mapping)
    {
        $shared_columns = [];
        foreach ($report_mapping as $key => $report_column) {
            if (isset($report_column['grouped']) && $report_column['grouped'] && isset($comparison_mapping[$key]['grouped']) && $comparison_mapping[$key]['grouped']) {
                $shared_columns[] = $key;
            }
        }

        return $shared_columns;
    }

    /**
     * Get the number of dimentions the report has been grouped by
     * @param $mappings
     * @return array
     */
    public function getDimensions($mappings)
    {
        $groups = [];

        foreach ($mappings as $key => $mapping) {
            if (isset($mapping['grouped']) && $mapping['grouped']) {
                $groups[] = $key;
            }
        }

        return $groups;
    }


}