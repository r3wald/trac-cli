<?php

class Application_Controller_Tickets extends Application_Controller_Abstract
{
  public function acceptAction()
  {
    $author = getenv('USER');
    $id = $this->getNextArgument();
    $ticket = $this->getApplication()->getTrac()->acceptTicket($author, $id);
  }

  public function browserAction()
  {
    $id = $this->getNextArgument();
    $config = $this->getApplication()->getConfig()->get('trac');
    $url =  str_replace('http://', 'http://' . $config->user. ':' . $config->password. '@', $config->url) . 'ticket/' . $id;
    $command = 'xdg-open "' . $url . '"';
    shell_exec($command);
  }

  public function createAction()
  {
    $reporter = getenv('USER');
    $summary = $this->getNextArgument();
    try {
      $component = $this->getNextArgument();
    } catch (Application_Exception_MissingArgument $e) {
      print 'no component given.' . PHP_EOL;
      $this->getApplication()->call('components', 'list');
    }
    $ticket = $this->getApplication()->getTrac()->createTicket($reporter, $summary, $component);
    $this->printTickets(array($ticket));
  }

  public function updateAction()
  {
    $author = getenv('USER');
    $id = $this->getNextArgument();
    $comment = $this->getNextArgument();
    $ticket = $this->getApplication()->getTrac()->updateTicket($author, $id, $comment);
    $this->printTickets(array($ticket));
  }

/*  public function commentAction()
  {
    $author = getenv('USER');
    $id = $this->getNextArgument();
    $comment = $this->getNextArgument();
    $ticket = $this->getApplication()->getTrac()->updateTicket($author, $id, $comment);
    $this->printTickets(array($ticket));
  }
*/
  public function showAction()
  {
    $id = $this->getNextArgument();
    $ticket = $this->getApplication()->getTrac()->loadTicket($id);
    $this->printTickets(array($ticket));
  }

  public function listAction()
  {
    $user = $this->getNextArgument(true);
    if (!$user) {
      $user = getenv('USER');
    }
    $query = 'owner=' . $user . '&status!=closed';
    $tickets = $this->getApplication()->getTrac()->findTickets($query);
    $this->printTickets($tickets);
  }

  public function searchAction()
  {
    $query = $this->getNextArgument();
    $tickets = $this->getApplication()->getTrac()->findTickets($query);
    $this->printTickets($tickets);
  }

  public function deleteAction()
  {
    $id = $this->getNextArgument();
    $this->getApplication()->getTrac()->deleteTicket($id);
  }

  protected function printTickets($tickets)
  {
    printf('%-6s | %-10s | %s' . PHP_EOL, 'id', 'owner', 'summary');
    printf('%-6s-+-%-10s-+-%s' . PHP_EOL, str_repeat('-', 6), str_repeat('-', 10), str_repeat('-', 50));
    foreach ($tickets as $ticket) {
      printf('%-6s | %-10s | %s' . PHP_EOL, $ticket[0], $ticket[3]['owner'], $ticket[3]['summary']);
    }
  }
}
