<?PHP

  class InvalidEventException extends Exception {}

  class InvalidTransitionException extends LogicException {}

  interface State {}

  interface StateMachine {
    public function getCurrentState();
    public function addEvent($name, array $transitions);
    public function setInitialState($state);
  }

  class FiniteStateMachine implements StateMachine {
    protected $state;
    protected $events;

    public function getCurrentState() {
      return $this->state;
    }

    public function addEvent($name, array $transitions) {
      $this->events[$name] = $transitions;
      return $this;
    }

    public function setInitialState($state) {
      $this->state = $state;
      return $this;
    }

    public function __call($name, $arguments)
    {
      if (!array_key_exists($name, $this->events))
        throw new InvalidEventException('Event [' . $name . '] does not exist');

      $transitions = $this->events[$name];

      if (!array_key_exists((string)$this->state, $transitions))
        throw new InvalidTransitionException('Machine cannot transition from '. '[' . $this->state . '] to [' . $name . ']');

      $this->state = $transitions[(string)$this->state];
    }
  }


  $machine = new FiniteStateMachine();
  $machine->addEvent('start', array('parked' => 'idling'));
  $machine->addEvent('drive', array('idling' => 'driving'));
  $machine->addEvent('stop', array('driving' => 'idling'));
  $machine->addEvent('park', array('idling' => 'parked'));
  $machine->setInitialState('parked');

  echo "<p>State: " . $machine->getCurrentState();

  try { $machine->drive(); } // cannot go from parked to driving
  catch(LogicException $e) { echo "<p>Error: " . $e->getMessage(); }

  try { $machine->ali(); } // no event called ali
  catch(Exception $e) { echo "<p>Error: " . $e->getMessage(); }

  $machine->start();
  $machine->drive();

  echo "<p>State: " . $machine->getCurrentState();

?>
