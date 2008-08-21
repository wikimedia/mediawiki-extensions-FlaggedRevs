<?php
error_reporting(0);

include 'svgGraph.php';
include 'svgGraph1.php';

$graph = new svgGraph1;
$graph->graphicWidth      = 400;
$graph->graphicHeight     = 300;
$graph->plotWidth         = 300;
$graph->plotHeight        = 200;
$graph->plotOffsetX       = 70;
$graph->plotOffsetY       = 50;
$graph->numGridlinesY     = 6;
$graph->numTicksY         = 6;

$graph->innerPaddingX     = 4;
$graph->innerPaddingY     = 4;
$graph->outerPadding      = 10;

$graph->offsetGridlinesX  = 0;

$graph->decimalPlacesY    = 2;

$graph->title             = 'Sample Line Graph';
$graph->styleTitle        = 'font-family: sans-serif; font-size: 12pt;';

$graph->labelX            = 'Day of the Week';
$graph->styleLabelX       = 'font-family: sans-serif; font-size: 10pt;';
$graph->labelY            = 'Some Parameters';
$graph->styleLabelY       = 'font-family: sans-serif; font-size: 10pt;';

$graph->dataX             = array('Fri', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri');
$graph->dataY['alpha']    = array(8.610, 7.940, 3.670, 3.670, 6.940, 8.650);
$graph->dataY['beta']     = array(1.456, 3.001, 5.145, 2.050, 1.998, 1.678);
$graph->dataY['gamma']    = array(4.896, 4.500, 4.190, 3.050, 2.888, 3.678);

$graph->styleTagsX        = 'font-family: monospace; font-size: 8pt;';
$graph->styleTagsY        = 'font-family: monospace; font-size: 8pt;';

$graph->format['alpha']   = array(
  'style' => 'stroke:#F00; stroke-width:4;');

$graph->format['beta']   = array(
  'style' => 'stroke:#0F0; stroke-width:4;');

$graph->format['gamma']   = array(
  'style' => 'stroke:#00F; stroke-width:4;');


$graph->init()        or die($graph->error);
$graph->drawGraph();
$graph->line('gamma')  or die($graph->error);
$graph->line('beta')   or die($graph->error);
$graph->line('alpha')  or die($graph->error);

$graph->outputSVG();
?>