<?php

class Application_Controller_Config extends Application_Controller_Abstract
{
  public function createAction()
  {
    $file = getenv('HOME') . '/.trac.ini';
    if (is_readable($file)) {
      throw new Exception('file already exists');
    }
    $content = '
[production]
trac.user = "{user}"
trac.password = "password"
trac.url = "http://server/trac/esv/"

[development : production]
trac.url = "http://server/trac/test/"
    ';
    $content = str_replace('{user}', getenv('USER'), $content);
    file_put_contents($file, $content);
    chmod($file, 0777);
    $this->getApplication()->call('config', 'show');
  }

  public function showAction()
  {
    $config = $this->getApplication()->getConfig();
    print_r($config->toArray());
  }

  public function setAction()
  {

  }

  public function getAction()
  {

  }
}
