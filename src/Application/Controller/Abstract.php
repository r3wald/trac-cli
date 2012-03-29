<?php

abstract class Application_Controller_Abstract
{
  /**
   * @var Application
   */
  private $_application;

  /**
   * @var array
   */
  private $_arguments;

  /**
   * @param Application $application
   * @return Application_Controller_Abstract
   */
  public function setApplication(Application $application)
  {
    $this->_application = $application;
    return $this;
  }

  /**
   * @param array $arguments
   * @return Application_Controller_Abstract
   */
  public function setArguments(array $arguments)
  {
    $this->_arguments = $arguments;
    return $this;
  }

  public function helpAction()
  {
    $cc2d = new Zend_Filter_Word_CamelCaseToDash();
    foreach (get_class_methods($this) as $method) {
      if (!preg_match('/^(\w+)Action$/', $method, $matches) || $matches[1] == 'Abstract') {
        continue;
      }
      print $_SERVER['argv'][0] . ' ' . $_SERVER['argv'][1] . ' ' . strtolower($cc2d->filter($matches[1])) . PHP_EOL;
    }
  }

  /**
   * @return Application
   */
  protected function getApplication()
  {
    return $this->_application;
  }

  /**
   * @param bool $optional
   * @return string|null
   * @throws Exception
   */
  protected function getNextArgument($optional=false)
  {
    if (count($this->_arguments)>0) {
      $argument = array_shift($this->_arguments);
      return $argument;
    }

    if ($optional) {
      return null;
    }

    throw new Application_Exception_MissingArgument('too few arguments given');
  }
}
