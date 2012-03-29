<?php

class Application_Controller_Help extends Application_Controller_Abstract
{
  public function helpAction()
  {
    $cc2d = new Zend_Filter_Word_CamelCaseToDash();
    foreach (glob(__DIR__ . '/*.php') as $file) {
      if (!preg_match('/.*\/(\w+).php$/', $file, $matches) || $matches[1] == 'Abstract') {
        continue;
      }
      print $_SERVER['argv'][0] . ' ' . strtolower($cc2d->filter($matches[1])) . PHP_EOL;
    }
  }
}
