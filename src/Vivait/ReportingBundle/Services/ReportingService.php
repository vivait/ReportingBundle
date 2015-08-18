<?php

namespace Vivait\ReportingBundle\Services;


use Vivait\ReportingBundle\Entity\Report;
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
     * Private function used to get a report from the service and load in entity properties if necessary
     * Also inject any dependancies into the filters/groups
     * @param $report_service_name
     * @param Report $report
     * @return ReportBuilder
     * @throws \Exception
     */
    public function getReport($report_service_name, Report $report = null)
    {
        if (!array_key_exists($report_service_name, $this->reports) || !$report_obj = clone $this->reports[$report_service_name]) {
            throw new \Exception("The report does not exist");
        }

        if ($report) {
            /**
             * Pull the filters from the DB and apply them to the reportbuilder
             *
             * at this stage we have a list of filter/group objects in the reportbuilder and we
             * need to overwrite certain properties of those objects with ones that are attached
             * to the report entity in the database.
             *
             * We can't just overwrite the entire list of objects because we would lose critical
             * information as only a subset of this is stored when the object is serialised.
             */
            #replace this with a registry of 'requires'
            foreach ($report_obj->getFilters() as $key => $row) {
                $row->injectReport(clone $report_obj);

                if ($report->getFilter($key)) {
                    $row->unserialize($report->getFilter($key)->serialize());
                }

                if ($report->getParent() && $row->getLinked()) {
                    $row->unserialize($report->getParent()->getFilter($key)->serialize());
                    $row->setLinked(true);
                }
            }

            foreach ($report_obj->getGroups() as $key => $row) {
                $row->injectReport($report_obj);
                if ($report->getGroup($key)) {
                    $row->unserialize($report->getGroup($key)->serialize());
                }
            }

            foreach ($report_obj->getOrders() as $key => $row) {
                $row->injectReport($report_obj);
                if ($report->getOrder($key)) {
                    $row->unserialize($report->getOrder($key)->serialize());
                }
            }

        }

        return $report_obj;
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
     * Gets data from a reports comparisons. If there are no comparisons, the given data is returned in an associative
     * array
     *
     * @param Report $report
     * @param $values
     * @param $mappings
     * @return array
     * @throws \Exception
     */
    public function getComparisonData(Report $report, $values, $mappings)
    {
        $data['values'] = $values;
        $data['mappings'] = $mappings;
        $data['comparison_status'] = false;

        foreach ($report->getComparisons() as $comparison) {
            $comparison_obj = $this->getReport(
                $comparison->getReportService(),
                $comparison
            );
            $data = $this->compareData(
                $data['values'],
                $data['mappings'],
                $comparison_obj->getQuery(),
                $comparison_obj->getColumnMapping()
            );
        }

        return $data;
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
