<?php

namespace Vivait\ReportingBundle\Chart;


class DonutChart extends Chart
{

    /**
     * @inheritdoc
     */
    public function getTemplate()
    {
        return 'VivaitReportingBundle:Chart:donutchart.html.twig';
    }

    public function getOptions(array $columnmapping)
    {
        $x_axis = '';

        if (count($this->getDimensions($columnmapping))) {
            $x_axis = array_shift($this->getDimensions($columnmapping));
        }

        return ['x_axis' => $x_axis];
    }

    /**
     * Converts general purpose tabluar data into something that can be used by the graphs
     * @param array $data
     * @param array $columnmapping
     * @return array
     */
    public function getGraphData(array $data, array $columnmapping)
    {
        $dimension_count = count($this->getDimensions($columnmapping));

        $graph_data = [];
        $x_axis = '';

        if ($dimension_count) {
            $x_axis = $this->getDimensions($columnmapping)[0];
        }
        $graph_data['x'] = array_values(array_unique(array_column($data, $x_axis)));

        if ($dimension_count == 2) {
            #bar charts only support 2 dimensions, so pop the next one and use that for clustered columns if applicable
            $x2_axis = $this->getDimensions($columnmapping)[1];

            #foreach dimension label
            foreach (array_values(array_unique(array_column($data, $x2_axis))) as $dimension) {
                #perform intersection of the dimension onto the data array

                $filtered_data = [];
                foreach ($graph_data['x'] as $x) {
                    $found = false;
                    foreach ($data as $row) {
                        if ($row[$x_axis] == $x && $row[$x2_axis] == $dimension) {
                            $filtered_data[] = $row[$this->field];
                            $found = true;
                            break;
                        }
                    }
                    #interpolate
                    if (!$found) {
                        $filtered_data[] = 0;
                    }

                }

                $graph_data['datasets'][] = [
                    'label' => $dimension,
                    'color' => substr(sha1($dimension), 0, 6),
                    'data'  => $filtered_data
                ];
            }
        } elseif ($dimension_count == 1) {
            $graph_data['datasets'][] = [
                'label' => $this->field,
                'data'  => array_values(array_column($data, $this->field))
            ];

        }

        return $graph_data;
    }
}