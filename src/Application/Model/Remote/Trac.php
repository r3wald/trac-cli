<?php

class Application_Model_Remote_Trac
{
  /**
   * @var Zend_XmlRpc_Client
   */
  private $_client;

  public function __construct(Zend_Config $config)
  {
    $url =  str_replace('http://', 'http://' . $config->user. ':' . $config->password. '@', $config->url) . 'login/xmlrpc/';
    $this->_client = new Zend_XmlRpc_Client($url);
  }

  public function getUserList()
  {
  }

  public function deleteMilestone($name)
  {
    $proxy = $this->_client->getProxy('ticket.milestone');
    $result = $proxy->delete($name);
    return true;
  }

  public function saveMilestone($name, $data)
  {
    $proxy = $this->_client->getProxy('ticket.milestone');
    $data['due'] = $this->convertDateToTrac($data['due']);
    $data['completed'] = $this->convertDateToTrac($data['completed']);
    $result = $proxy->create($name, $data);
    return true;
  }

  /**
   * @param string $name
   *
   * @return array name, due, completed, description
   */
  public function loadMilestone($name)
  {
    $proxy = $this->_client->getProxy('ticket.milestone');
    $result = $proxy->get($name);
    $result['due'] = $this->convertDateFromTrac($result['due']);
    $result['completed'] = $this->convertDateFromTrac($result['completed']);
    return $result;
  }

  public function existsMilestone($name)
  {
    $result = true;
    try {
      $this->loadMilestone($name);
    } catch (Zend_XmlRpc_Client_FaultException $e) {
      if ($e->getMessage() != 'Milestone ' . $name . ' does not exist.') {
        throw $e;
      }
      $result = false;
    }
    return $result;
  }

  /**
   * @param string $query
   *
   * @return array
   */
  public function findMilestones($query = null)
  {
    $proxy = $this->_client->getProxy('ticket.milestone');
    $milestones = $proxy->getAll();
    $result = array();
    foreach ($milestones as $milestone) {
      if (is_null($query) || stristr($milestone, $query) !== false) {
        $result[] = $this->loadMilestone($milestone);
      }
    }
    return $result;
  }

  public function findComponents($query=null)
  {
    $proxy = $this->_client->getProxy('ticket.component');
    $components = $proxy->getAll();
    $result = array();
    foreach ($components as $component) {
      if (is_null($query) || stristr($component, $query) !== false) {
        $result[] = $this->loadComponent($component);
      }
    }
    return $result;
  }

  /**
   * @param string $name
   *
   * @return array[string]string name, owner, description
   */
  public function loadComponent($name)
  {
    $proxy = $this->_client->getProxy('ticket.component');
    $result = $proxy->get($name);
    return $result;
  }

  /**
   * @param string $name
   * @param string $owner
   * @param string $description
   *
   * @return bool
   * @throws Zend_XmlRpc_Client_FaultException
   */
  public function saveComponent($name, $owner, $description)
  {
    $proxy = $this->_client->getProxy('ticket.component');
    try {
      /* try to update existing component */
      $result = $proxy->update($name, array('owner' => $owner, 'description' => $description, 'iteritems' => array()));
    } catch (Zend_XmlRpc_Client_FaultException $e) {
      if ($e->getMessage() != 'Component ' . $name . ' does not exist.') {
        throw $e;
      }
      /* no such component to update -> create new component */
      $result = $proxy->create($name, array('owner' => $owner, 'description' => $description, 'iteritems' => array()));
    }
    return ($result === 0);
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  public function deleteComponent($name)
  {
    $proxy = $this->_client->getProxy('ticket.component');
    $result = $proxy->delete($name);
    return ($result === 0);
  }

  public function findTickets($query)
  {
    $proxy = $this->_client->getProxy('ticket');
    $ids = $proxy->query($query);
    $result = array();
    foreach ($ids as $id) {
      $result[] = $this->loadTicket($id);
/*
   array(4) {
     [0]=>
     int(1821)
     [1]=>
     string(17) "20120322T09:47:02"
     [2]=>
     string(17) "20120322T09:47:02"
     [3]=>
     array(20) {
       ["status"]=>
       string(3) "new"
       ["changetime"]=>
       string(17) "20120322T09:47:02"
       ["totalhours"]=>
       string(1) "0"
       ["hours"]=>
       string(1) "0"
       ["description"]=>
       string(228) "Die Bilder müssen bislang einzeln über den Übergabeparameter refresh=1 aktualisiert werden. Da sich bei der Immissionsschutz mehrere Ausgaben verändert haben, ist dies händisch für jedes einzelne Bild nicht mehr zu machen."
       ["_ts"]=>
       string(32) "2012-03-22 09:47:02.026364+00:00"
       ["reporter"]=>
       string(10) "schoenherr"
       ["cc"]=>
       string(0) ""
       ["resolution"]=>
       string(0) ""
       ["time"]=>
       string(17) "20120322T09:47:02"
       ["component"]=>
       string(14) "media.esv.info"
       ["summary"]=>
       string(24) "Auto-Refresh für Bilder"
       ["priority"]=>
       string(5) "major"
       ["keywords"]=>
       string(0) ""
       ["billable"]=>
       string(1) "1"
       ["milestone"]=>
       string(0) ""
       ["owner"]=>
       string(5) "ewald"
       ["estimatedhours"]=>
       string(3) "0.0"
       ["type"]=>
       string(4) "task"
       ["internal"]=>
       string(1) "0"
     }
   }

 */
    }
    return $result;
  }

  public function loadTicket($id)
  {
    $proxy = $this->_client->getProxy('ticket');
    $result = $proxy->get($id);
    return $result;
  }

  public function createTicket($reporter, $summary, $component, array $data=array())
  {
    $attributes = array();
    $attributes['component'] = $component;
    $attributes['reporter'] = $reporter;
    $attributes['owner'] = $reporter;
    if (!empty($data['owner'])) {
      $attributes['owner'] = $data['owner'];
    }
    if (!empty($data['milestone'])) {
      $attributes['milestone'] = $data['milestone'];
    }
    $description = '';
    if (!empty($data['description'])) {
      $description = $data['description'];
    }
    $notify = true;

    $proxy = $this->_client->getProxy('ticket');
    // int ticket.create(string summary, string description, struct attributes={}, boolean notify=False, dateTime.iso8601 when=None)
    $id = $proxy->create($summary, $description, $attributes, $notify);

    $result = $this->loadTicket($id);
    return $result;
  }

  public function acceptTicket($author, $id)
  {
    $attributes = array();
    $attributes['action'] = 'accept';
    $attributes['owner'] = $author;
    $comment = null;
    $notify = true;

    $proxy = $this->_client->getProxy('ticket');
    // array ticket.update(int id, string comment, struct attributes={}, boolean notify=False, string author="", dateTime.iso8601 when=None)
    $result = $proxy->update(intval($id), $comment, $attributes, $notify, $author);
    return $result;
  }

  public function updateTicket($author, $id, $comment, array $data=array())
  {
    $fields = array('owner', 'reporter', 'milestone', 'summary', 'action');
    $attributes = array();
    foreach ($fields as $field) {
      if (!empty($data[$field])) {
        $attributes[$field] = $data[$field];
      }
    }
    $attributes['action'] = 'leave';
    $notify = true;
    $proxy = $this->_client->getProxy('ticket');
    $result = $proxy->getActions(intval($id));
    // array ticket.update(int id, string comment, struct attributes={}, boolean notify=False, string author="", dateTime.iso8601 when=None)
    $result = $proxy->update(intval($id), $comment, $attributes, $notify, $author);
    return $result;
  }

  public function deleteTicket($id)
  {
    $proxy = $this->_client->getProxy('ticket');
    // int ticket.delete(int id)
    $result = $proxy->delete($id);
    return ($result === 0);
  }
  /**
   * @param string $value
   * @return null|Zend_Date
   * @throws Exception
   */
  protected function convertDateFromTrac($value)
  {
    if ($value=='0') {
      return null;
    }
    if (preg_match('/(....)(..)(..)T(..):(..):(..)/', $value, $match)) {
      $result = new Zend_Date("$match[1]-$match[2]-$match[3] $match[4]:$match[5]:$match[6]");
      return $result;
    }
    throw new Exception('neither "0" nor python datetime: ' . print_r($value, true));
  }

  /**
   * @param Zend_Date $value
   * @return string
   */
  protected function convertDateToTrac($value)
  {
    if (empty($value)) {
      return 0;
    }
    if ($value instanceof Zend_Date) {
      $result = new Zend_XmlRpc_Value_DateTime($value);
      return $result;
    }
    throw new Exception('neither null nor Zend_Date: ' . print_r($value, true));
  }
}
