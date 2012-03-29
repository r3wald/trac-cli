#!/usr/bin/env php
<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

Phar::interceptFileFuncs();

include_once __FILE__;
set_include_path('phar://' . __FILE__ . '/');

#$phar = new Phar(__FILE__);
#foreach (new RecursiveIteratorIterator($phar) as $iteration) { echo $iteration->getPathName() . PHP_EOL; }

require  'phar://' . __FILE__ . '/Application.php';

$application = new Application();
$application
  ->prepareAutoloader()
  ->parseCommandLine()
  ->run();

__HALT_COMPILER();

