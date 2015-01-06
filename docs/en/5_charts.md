ReportingBundle
===============

## Graphs

So now you have some multi-dimensional data it might look better on a graph than in a table.

There are a number of pre-set charts already included within the bundle, these include a bar, line and radar chart. Charts by default will appear above the table and a report can include multiple graphs.

The graphs themselves are generated through Chart.js

To add a graph to your report, open your report class and add a chart to the constructor

Once you have this then in the constructor of your report class simply add the filter on the field you wish to filter by:

```php
  function __construct(...)
  ...
    $this->addChart('barchart_leads', new BarChart($this,'total'));
  ...
```

The first parameter is simply an alias for the graph and should be unique in the report, the second parameter accepts the graph class you wish to use.

Currently there are a number available including

 * BarChart
 * LineChart
 * RadarChart
 * DonutChart (to be implemented)

Each graph has two parameters to pass into the constructor

 * ReportBuilder $report is a reference to your report class, in most cases you can pass in $this.
 * $field is the data value you wish to display in the graph, in the above example it would be 'total'.

The graphs will use your groupings and filters, if your groupings result in a multidimentionsal result set then the graphs will adjust to suit. It should be noted that some graph types only support a limited number of dimensions:

## BarChart 
This graph supports 1-3 dimensions in the following format:
 * 1 Dimension: Simple value display
 * 2 Dimensions: Group along the x axis, value along the y axis
 * 3 Dimensions: First group along the x axis, second group as a clustered column (again on the x axis) and value along the y axis
  
## LineChart
This graph supports 1-3 dimensions in the following format:
 * 1 Dimension: Simple value display
 * 2 Dimensions: Group along the x axis, value along the y axis
 * 3 Dimensions: First group along the x axis, second group again on the x axis but with transparancy (technically along the z axys) and value along the y axis

## RadarChart
This graph supports 1-3 dimensions in the following format:
 * 1 Dimension: Simple value display
 * 2 Dimensions: Group along the radial x axis, value along the y axis (radius)
 * 3 Dimensions: First group along the x axis, second group again on the x axis but with transparancy (technically along the z axys) and value along the y axis

## DonutChart
This graph has not been implemented yet, but will support 1-2 dimensions. A possible third dimension could be created by displaying multiple graphs back to back.
  
If you try to display a graph with an incompatible number of dimensions the default response is to not display a graph.
