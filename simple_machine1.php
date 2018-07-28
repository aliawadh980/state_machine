<?PHP

  interface EngineState {
    public function startEngine();
    public function moveForward();
  }

  class EngineTurnedOffState implements EngineState {
    public function startEngine() {
      echo "<p>Started Engine";
      return new EngineTurnedOnState;
    }

    public function moveForward() {
      throw new LogicException('<p>Have to start engine first');
    }
  }

  class EngineTurnedOnState implements EngineState {
    public function startEngine() {
      throw new LogicException('<p>Engine already started');
    }

    public function moveForward() {
      echo "<p>Moved Car forward";
      return $this;
    }
  }

  class Car implements EngineState {
    protected $state;

    public function __construct() {
      $this->state = new EngineTurnedOffState;
    }

    public function startEngine() {
      $this->state = $this->state->startEngine();
    }

    public function moveForward() {
      $this->state = $this->state->moveForward();
    }
  }

  echo "<p>First call";
  $car = new Car;
  $car->startEngine();
  $car->moveForward(); // works fine

  echo "<p>Second call";
  $car = new Car;
  $car->startEngine();
  $car->moveForward();
  $car->moveForward(); // works fine

  echo "<p>3rd call";
  try { $car = new Car; 
    $car->moveForward();  } // throws Exception
  catch(LogicException $e) { echo $e->getMessage(); }

  echo "<p>4rd call";
  try { $car = new Car;
    $car->startEngine();
    $car->startEngine(); }  // throws Exception
  catch(LogicException $e) { echo $e->getMessage(); }

?>
