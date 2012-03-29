<?php
class Application
{
  /**
   * @var Zend_Config
   */
  private $_config;
  /**
   * @var string
   */
  private $_controller;
  /**
   * @var string
   */
  private $_action;
  /**
   * @var array
   */
  private $_arguments;
  /**
   * @var Application_Model_Remote_Trac
   */
  private $_trac;

  public function __construct()
  {
    $this->_environment = 'production';
  }

  public function prepareAutoloader()
  {
    require_once 'Zend/Loader/Autoloader.php';
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->registerNamespace('Application');
    return $this;
  }

  public function parseCommandLine()
  {
    try {
      $opts = new Zend_Console_Getopt(array(
        'development|d' => 'development mode',
        'verbose|v' => 'verbose output',
        'json|j' => 'JSON output'
      ));
      $opts->parse();
    } catch (Zend_Console_Getopt_Exception $e) {
      echo $e->getUsageMessage();
      exit;
    }

    if ($opts->getOption('d')) {
      $this->_environment = 'development';
    }

    $args = $opts->getRemainingArgs();
    if (count($args) < 1) {
      $args = array('help');
    }
    $this->_controller = array_shift($args);

    if (count($args) < 1) {
      $args = array('help');
    }
    $this->_action = array_shift($args);

    $this->_arguments = $args;

    return $this;
  }

  public function call($controller, $action, array $arguments=array())
  {
    $d2cc = new Zend_Filter_Word_DashToCamelCase();
    $controllerName = 'Application_Controller_' . $d2cc->filter($controller);
    $actionName = $action . 'Action';
    /* @var $controllerInstance Application_Controller_Abstract */
    $controllerInstance = new $controllerName();
    $controllerInstance->setApplication($this);
    $controllerInstance->setArguments($arguments);
    return $controllerInstance->$actionName();
  }

  public function run()
  {
    return $this->call($this->_controller, $this->_action, $this->_arguments);
  }

  /**
   * @return Zend_Config
   */
  public function getConfig()
  {
    if (empty($this->_config)) {
      $this->_config = new Zend_Config_Ini(getenv('HOME') . '/.trac.ini', $this->_environment);
    }
    return $this->_config;
  }

  /**
   * @return Application_Model_Remote_Trac
   */
  public function getTrac()
  {
    if (empty($this->_trac)) {
      /* @var $tracConfig Zend_Config */
      $config = $this->getConfig()->get('trac');
      $this->_trac = new Application_Model_Remote_Trac($config);
    }
    return $this->_trac;
  }
}
