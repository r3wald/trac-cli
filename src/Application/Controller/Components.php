<?php

class Application_Controller_Components extends Application_Controller_Abstract
{
  public function listAction()
  {
    $components = $this->getApplication()->getTrac()->findComponents();
    $this->printComponents($components);
  }

  public function searchAction()
  {
    $query = $this->getNextArgument();
    $components = $this->getApplication()->getTrac()->findComponents($query);
    $this->printComponents($components);
  }

  public function showComponent()
  {
    $name = $this->getNextArgument();
    $component = $this->getApplication()->getTrac()->loadComponent($name);
    $this->printComponents(array($component));
  }

//  public function addAction()
//  {
//    $name = $this->getNextArgument();
//    $due = $this->getNextArgument(true);
//    $completed = $this->getNextArgument(true);
//
//    $data = array();
//    $data['due'] = empty($due) ? null : new Zend_Date($due);
//    $data['completed'] = empty($completed) ? null : new Zend_Date($completed);
//
//    $this->getApplication()->getTrac()->saveMilestone($name, $data);
//  }

//  public function deleteAction()
//  {
//    $name = $this->getNextArgument();
//    $this->getApplication()->getTrac()->deleteMilestone($name);
//  }

  protected function printComponents($components)
  {
    printf('%-20s | %-10s | %s' . PHP_EOL, 'name', 'owner', 'description');
    printf('%-20s-+-%-10s-+-%s' . PHP_EOL, str_repeat('-', 20), str_repeat('-', 10), str_repeat('-', 50));
    foreach ($components as $component) {
      printf('%-20s | %-10s | %s' . PHP_EOL, $component['name'], $component['owner'], $component['description']);
    }
  }
}
