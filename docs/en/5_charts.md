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

All graph classes need the reportbuilder injected to pull out essential data such as column mapping information.

It is important that the charts are added as the final element to the constructor to ensure that the column mapping information is complete.

Currently there are a number available including

 * BarChart
 * LineChart
 * RadarChart
 * DonutChart (to be implemented)

Each graph has two parameters to pass into the constructor

 * ReportBuilder $report is a reference to your report class, in most cases you can pass in $this.
 * $field is the data value you wish to display in the graph, in the above example it would be 'total'.

The graphs will use your groupings and filters, if your groupings result in a multidimentionsal result set then the graphs will adjust to suit. It should be noted that some graph types only support a limited number of dimensions:

### BarChart
This graph supports 1-3 dimensions in the following format:
 * 1 Dimension: Simple value display
 * 2 Dimensions: Group along the x axis, value along the y axis
 * 3 Dimensions: First group along the x axis, second group as a clustered column (again on the x axis) and value along the y axis

### LineChart
This graph supports 1-3 dimensions in the following format:
 * 1 Dimension: Simple value display
 * 2 Dimensions: Group along the x axis, value along the y axis
 * 3 Dimensions: First group along the x axis, second group again on the x axis but with transparancy (technically along the z axys) and value along the y axis

### RadarChart
This graph supports 1-3 dimensions in the following format:
 * 1 Dimension: Simple value display
 * 2 Dimensions: Group along the radial x axis, value along the y axis (radius)
 * 3 Dimensions: First group along the x axis, second group again on the x axis but with transparancy (technically along the z axys) and value along the y axis

### DonutChart
This graph has not been implemented yet, but will support 1-2 dimensions. A possible third dimension could be created by displaying multiple graphs back to back.

If you try to display a graph with an incompatible number of dimensions the default response is to not display a graph.

## Embedding
For those who just like to look at charts, it's possible to embed a single chart, into a twig template for example, using the Symfony sub-request framework, or simply via a url.

To embed or view a single graph, two parameters are required, the `report` and a `chart_alias`.

```php
{{ path('vivait_reporting_chart', { 'report' : id, 'chart_alias' : 'chart_day'} ) }}
```

```php
{{ render(controller('VivaitReportingBundle:Chart:getChart', {'report' : id, 'chart_alias': 'chart_day' })) }}
```

Additionally, the size of the chart can be customised by setting the `width` and `height` parameters. By default the width and height are set to `1000` and `500` respectively.

```php
{{ path('vivait_reporting_chart', { 'report' : id, 'chart_alias' : 'chart_day', 'width' : 200, 'height': 200 } ) }}
```

```php
{{ render(controller('VivaitReportingBundle:Chart:getChart', {'report' : id, 'chart_alias': 'chart_day', 'width' : 200, 'height': 200  })) }}
```

Optionally, you can disable loading Chart.js, if for example, you have already loaded the file in your template. This stops the file being loaded multiple times.

```php
{{ path('vivait_reporting_chart', { 'report' : id, 'chart_alias' : 'chart_day', 'js' : false } ) }}
```

```php
{{ render(controller('VivaitReportingBundle:Chart:getChart', {'report' : id, 'chart_alias': 'chart_day', 'js' : false })) }}
```
