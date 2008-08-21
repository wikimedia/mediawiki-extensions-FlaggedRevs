<?php
error_reporting(0);

include 'svgGraph.php';
include 'svgGraph2.php';

$graph = new svgGraph2;
$graph->graphicWidth      = 400;
$graph->graphicHeight     = 300;
$graph->plotWidth         = 300;
$graph->plotHeight        = 180;
$graph->plotOffsetX       = 70;
$graph->plotOffsetY       = 50;
$graph->numGridlinesY     = 6;
$graph->numTicksY         = 6;

$graph->innerPaddingX     = 10;
$graph->innerPaddingY     = 6;
$graph->outerPadding      = 10;

$graph->offsetGridlinesX  = 0.2;

$graph->decimalPlacesY    = 2;

$graph->rotTagsX          = -30;
$graph->rotTagsY          = 0;
  

$graph->title             = 'Lines with Markers and Filters';
$graph->styleTitle        = 'font-family: sans-serif; font-size: 18pt;';

$graph->labelX            = 'Day of the Week';
$graph->styleLabelX       = 'font-family: sans-serif; font-size: 10pt;';
$graph->labelY            = 'Some Parameters';
$graph->styleLabelY       = 'font-family: sans-serif; font-size: 10pt;';

$graph->dataX             = array('Friday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
$graph->dataY['alpha']    = array(8.610, 7.940, 3.670, 3.670, 6.940, 8.650);
$graph->dataY['beta']     = array(1.456, 3.001, 5.145, 2.050, 1.998, 1.678);
$graph->dataY['gamma']    = array(4.896, 4.500, 4.190, 3.050, 2.888, 3.678);

$graph->styleTagsX        = 'font-family: sans-serif; font-size: 8pt;';
$graph->styleTagsY        = 'font-family: sans-serif; font-size: 8pt;';

$graph->format['alpha']   = array(
  'style' => 'stroke:#F00; stroke-width:2; filter:url(#dropShadow); ', 
             'attributes' => "marker-end='url(#square)'");

$graph->format['beta']    = array(
  'style' => 'stroke:#0F0; stroke-width:2; filter:url(#dropShadow); ', 
             'attributes' => "marker-end='url(#circle)'");

$graph->format['gamma']   = array(
  'style' => 'stroke:#00F; stroke-width:2; filter:url(#dropShadow); ', 
             'attributes' => "marker-end='url(#triangle)'");

// extra code for markers
$graph->extraSVG = '
<defs>
  <marker id="square" style="stroke:#000; stroke-width:0; fill:#F00; "
    viewBox="0 0 10 10" refX="5" refY="5" orient="0"
    markerUnits="strokeWidth" markerWidth="4" markerHeight="4">
    <rect x="0" y="0" width="10" height="10"/>
  </marker>
  <marker id="circle" style="stroke:#000; stroke-width:0; fill:#0F0; "
    viewBox="0 0 10 10" refX="5" refY="5" orient="0"
    markerUnits="strokeWidth" markerWidth="5" markerHeight="5">
    <circle cx="5" cy="5" r="4"/>
  </marker>
  <marker id="triangle" style="stroke:#000; stroke-width:0; fill:#00F; "
    viewBox="0 0 10 10" refX="5" refY="5" orient="-90"
    markerUnits="strokeWidth" markerWidth="5" markerHeight="5">
    <path d="M 2 0 L 10 5 L 2 10 z" />
  </marker>
</defs>
<filter id="dropShadow" filterUnits="objectBoundingBox" x="-10%" y="-10%" width="130%" height="130%">
  <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur"/>
  <feOffset in="blur" dx="2" dy="2" result="offsetBlur"/>
  <feMerge>
    <feMergeNode in="offsetBlur"/>
    <feMergeNode in="SourceGraphic"/>
  </feMerge>
</filter>
';

$graph->init()         or die($graph->error);
$graph->drawGraph();
$graph->line('gamma')  or die($graph->error);
$graph->line('beta')   or die($graph->error);
$graph->line('alpha')  or die($graph->error);

$graph->outputSVG();
?>