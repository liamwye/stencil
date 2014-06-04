<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require 'Exceptions/StencilNotFoundException.php';
require 'Observer/ObservableInterface.php';
require 'Observer/Observable.php';
require 'Filters/FilterInterface.php';
require 'Filters/DebugTemplateFilter.php';
require 'TemplateInterface.php';
require 'Template.php';

$stencil = new Stencil\Template('Example', array(
    'path' => 'example.stencil.php',
));

$stencil->addListener(new Stencil\Filters\DebugTemplateFilter(), 'Template_PostProcess');

$stencil->set('stencil', 'Stencil');

echo $stencil->render();
