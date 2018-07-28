<?PHP

/************************************************************************************************

car states: 
  - parked
  - idling
  - driving

transitions:
  - start: parked  -> idling
  - park:  idling  -> parked
  - drive: idling  -> driving
  - stop:  driving -> idling

     +---- start ---+   +---- drive -----+
     |              |   |                |
     |              v   |                v
+----+----+       +-+---+--+        +----+----+
| parked  |       | idling |        | driving |
+----+----+       +-+---+--+        +----+----+
     ^              |   ^                |
     |              |   |                |
     +---- park ----+   +---- stop ------+

Example 3:
----------

Plain Text Tables generator - TablesGenerator.com
https://www.tablesgenerator.com/text_tables

Note:
Car transaction logs - transactional log data
It has car state, transition and date
The aim is to create single record per car per ride!

So the start of the ride is when: parked and then start
and the end of the ride is when:  idling and then park

Semi-real life car csv data:
+---------------------+------------+---------------+
| Date                | Transition | Location      |
+---------------------+------------+---------------+
| 2018-07-28 10:50:00 | start      | Home          |
+---------------------+------------+---------------+
| 2018-07-28 10:55:00 | drive      | Road          |
+---------------------+------------+---------------+
| 2018-07-28 11:04:00 | stop       | Traffic light |
+---------------------+------------+---------------+

Desired output:
+--------+---------------------+---------------------+-----------+------------+-----------+------------------+
| RideNo | StartDate           | EndDate             | IdleCount | DriveCount | StopCount | TotalTransitions |
+--------+---------------------+---------------------+-----------+------------+-----------+------------------+
| 1      | 2018-07-28 10:50:00 | 2018-07-28 11:19:00 | 4         | 2          | 2         | 8                |
+--------+---------------------+---------------------+-----------+------------+-----------+------------------+
| 2      | 2018-07-28 11:43:00 | 2018-07-28 11:58:00 | 3         | 2          | 2         | 6                |
+--------+---------------------+---------------------+-----------+------------+-----------+------------------+

Note 4:
This is not a stright forward state machine design.

Example 4:
----------
later to add non defined transition and more complex

************************************************************************************************/

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
      if (!array_key_exists($name, $this->events)) return false;

      $transitions = $this->events[$name];

      if (!array_key_exists((string)$this->state, $transitions)) return false;

      $this->state = $transitions[(string)$this->state];

      return true;
    }
  }


  $machine = new FiniteStateMachine();
  $machine->addEvent('start', array('parked' => 'idling'));
  $machine->addEvent('drive', array('idling' => 'driving'));
  $machine->addEvent('stop', array('driving' => 'idling'));
  $machine->addEvent('park', array('idling' => 'parked'));

  $machine->setInitialState('parked');

  # Header: Date, Transition, Location
  $car1csv = array(
    array("2018-07-28 10:50:00", "start", "Home"), 
    // array("2018-07-28 10:50:00", "stop", "Home"), # this is not valid to be dealt with in Example 4
    array("2018-07-28 10:55:00", "drive", "Road"), 
    array("2018-07-28 11:04:00", "stop",  "Traffic light"), 
    array("2018-07-28 11:06:00", "drive", "Highway"), 
    array("2018-07-28 11:15:00", "stop",  "Traffic light"), 
    array("2018-07-28 11:16:00", "drive", "Road"), 
    array("2018-07-28 11:18:00", "stop",  "Supermarket"), 
    array("2018-07-28 11:19:00", "park",  "Supermarket"), 
    array("2018-07-28 11:43:00", "start", "Supermarket"), 
    array("2018-07-28 11:44:00", "drive", "Highway"), 
    array("2018-07-28 11:55:00", "stop",  "Traffic light"), 
    array("2018-07-28 11:56:00", "drive", "road"), 
    array("2018-07-28 11:58:00", "stop",  "Home"), 
    array("2018-07-28 11:58:00", "park",  "Home"), 
  );

  $car1record = array( 
    array(
      "RideNo"            => "",
      "StartDate"         => "",
      "EndDate"           => "",
      "IdleCount"         => "",
      "DriveCount"        => "",
      "StopCount"         => "",
      "TotalTransitions"  => ""
    ) 
  );
  $rideCount  = 0;
  $ErrorFound = 0;

  foreach ($car1csv as $carentry) {
    # Start of the record
    if ($machine->getCurrentState() == "parked" && $carentry[1] == "start") {
      $car1record[$rideCount]['RideNo']    = $rideCount;
      $car1record[$rideCount]['StartDate'] = $carentry[0];

      $IdleCount  = 0;
      $DriveCount = 0;
      $StopCount  = 0;
      $TotalTransitions = 0;
    }

    # process the transaction logs rows
    $result = $machine->$carentry[1]();

    if ($result) {
      // echo "<p>row: $carentry[1], getCurrentState: " . $machine->getCurrentState(); # for debugging
      if ($machine->getCurrentState() == "idling")  $IdleCount++;
      if ($machine->getCurrentState() == "driving") $DriveCount++; // or IdleCount - 1
      if ($carentry[1]                == "stop")    $StopCount++;  // or IdleCount - 1
      $TotalTransitions++;
    }
    else {
      echo "<p>Found errors. To deal with them as per context of the problem that is needed to be solved.";
      echo "<p>Error: can't " . $carentry[1] . " from " . $machine->getCurrentState();
      $ErrorFound++;
    }

    # End of the record
    if ($machine->getCurrentState() == "parked" && $carentry[1] == "park") {
      $car1record[$rideCount]['EndDate']          = $carentry[0];
      $car1record[$rideCount]['IdleCount']        = $IdleCount;
      $car1record[$rideCount]['DriveCount']       = $DriveCount;
      $car1record[$rideCount]['StopCount']        = $StopCount;
      $car1record[$rideCount]['TotalTransitions'] = $TotalTransitions;
      $rideCount++;
    }
  } // foreach ($car1csv as $carentry)

  // print_r($car1record); # for debugging

  # Write the header first:
  foreach ($car1record[0] as $key => $value) { echo "$key |"; }

  # Write the data:
  echo "<p>";
  foreach ($car1record as $value) { 
    foreach ($value as $record) {
      echo "$record |"; 
    }
    echo "<br>"; 
  }

?>
