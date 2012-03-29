<?php

class Application_Controller_Milestones extends Application_Controller_Abstract
{
  public function listAction()
  {
    $milestones = $this->getApplication()->getTrac()->findMilestones();
    printf('%-20s | %-20s | %s' . PHP_EOL, 'due', 'completed', 'name');
    foreach ($milestones as $milestone) {
      printf('%-20s | %-20s | %s' . PHP_EOL, $milestone['due'], $milestone['completed'], $milestone['name']);
    }
    // $json = Zend_Json::encode($milestones);
    // print Zend_Json::prettyPrint($json) . PHP_EOL;
  }

  public function searchAction()
  {
    $query = $this->getNextArgument();

    $milestones = $this->getApplication()->getTrac()->findMilestones($query);
    printf('%-20s | %-20s | %s' . PHP_EOL, 'due', 'completed', 'name');
    foreach ($milestones as $milestone) {
      printf('%-20s | %-20s | %s' . PHP_EOL, $milestone['due'], $milestone['completed'], $milestone['name']);
    }
  }

  public function addAction()
  {
    $name = $this->getNextArgument();
    $due = $this->getNextArgument(true);
    $completed = $this->getNextArgument(true);

    $data = array();
    $data['due'] = empty($due) ? null : new Zend_Date($due);
    $data['completed'] = empty($completed) ? null : new Zend_Date($completed);

    $this->getApplication()->getTrac()->saveMilestone($name, $data);
  }

  public function deleteAction()
  {
    $name = $this->getNextArgument();

    $this->getApplication()->getTrac()->deleteMilestone($name);
  }
}
