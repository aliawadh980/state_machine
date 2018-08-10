<?php

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

Example 4:
----------

Note:
Next is more realistic example that mimic a real world problem that needs to be solved via state machine.
Here it has more data with `CarID` and few non-valid state transition marked in `Notes_for_clarifying`.
Solving the non-valid states depends on the context of the problem that is needed to be solved, here we'll only identify them and mark `Has_error_transition` as `Yes`.
The input CSV and the output CSV can be guessed from the PHP code.

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

  $machine = array();

  # Header: Date, carID, Transition, Location, Notes_for_clarifying
  $car2csv = array(
    array("2018-07-28 08:15:00", "1113", "start", "Home",          ""), 
    array("2018-07-28 08:20:00", "1113", "drive", "Road",          ""), 
    array("2018-07-28 08:45:00", "1113", "stop",  "Work",          ""), 
    array("2018-07-28 08:50:00", "1113", "park",  "Work",          ""), 
    array("2018-07-28 10:50:00", "1111", "start", "Home",          ""), 
    array("2018-07-28 10:50:00", "1111", "stop",  "Home",          "This is not valid"), 
    array("2018-07-28 10:52:00", "1112", "start", "Home",          ""), 
    array("2018-07-28 10:52:00", "1115", "start", "Home",          ""), 
    array("2018-07-28 10:53:00", "1112", "drive", "Road",          ""), 
    array("2018-07-28 10:53:00", "1115", "drive", "Road",          "In driving state"), 
    array("2018-07-28 10:55:00", "1111", "drive", "Road",          ""), 
    array("2018-07-28 10:57:00", "1112", "stop",  "Home",          ""), 
    array("2018-07-28 10:58:00", "1112", "park",  "Home",          ""), 
    array("2018-07-28 11:04:00", "1111", "stop",  "Traffic light", ""), 
    array("2018-07-28 11:06:00", "1111", "drive", "Highway",       ""), 
    array("2018-07-28 11:12:00", "1114", "start", "Home",          ""), 
    array("2018-07-28 11:13:00", "1114", "drive", "Road",          ""), 
    array("2018-07-28 11:15:00", "1111", "stop",  "Traffic light", ""), 
    array("2018-07-28 11:15:00", "1114", "stop",  "Supermarket",   ""), 
    array("2018-07-28 11:15:00", "1114", "park",  "Supermarket",   ""), 
    array("2018-07-28 11:16:00", "1111", "drive", "Road",          ""), 
    array("2018-07-28 11:18:00", "1111", "stop",  "Supermarket",   ""), 
    array("2018-07-28 11:19:00", "1111", "park",  "Supermarket",   ""), 
    array("2018-07-28 11:43:00", "1111", "start", "Supermarket",   ""), 
    array("2018-07-28 11:44:00", "1111", "drive", "Highway",       ""), 
    array("2018-07-28 11:55:00", "1111", "stop",  "Traffic light", ""), 
    array("2018-07-28 11:56:00", "1111", "drive", "road",          ""), 
    array("2018-07-28 11:58:00", "1111", "stop",  "Home",          ""), 
    array("2018-07-28 11:58:00", "1111", "park",  "Home",          ""), 
    array("2018-07-28 13:10:00", "1116", "start", "Home",          ""), 
    array("2018-07-28 13:11:00", "1116", "drive", "Road",          ""), 
    array("2018-07-28 13:24:00", "1116", "stop",  "Traffic light", "In idling state"), 
  );

  echo "<table border = 1";
  echo "<tr>";
  echo "<th>Date</th><th>CarID</th><th>Transition</th><th>Location</th><th>Notes_for_clarifying</th>";
  echo "</tr>";
  foreach ($car2csv as $arr) {
    echo "<tr>";
    foreach ($arr as $row)
      echo "<td>$row</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "<p>";

  # Output header:
  // $car1record = array( 
  //   array(
  //     array(
  //       "carID"                 => "",
  //       "rideID"                => "",
  //       "LastState"             => "",
  //       "LastStateDate"         => "",
  //       "TransitionErrorCount"  => "",
  //       "StartDate"             => "",
  //       "EndDate"               => "",
  //       "IdleCount"             => "",
  //       "DriveCount"            => "",
  //       "StopCount"             => "",
  //       "TotalTransitions"      => "",
  //       "Finished"              => "",
  //     )
  //   ) 
  // );

  // ------------------------------------------------------------------------------------------------

  $carIDHistory = array();
  $carArray     = array();

  foreach ($car2csv as $carentry) {
    if(!isset ($machine[$carentry[1]])) {
      $machine[$carentry[1]] = new FiniteStateMachine();
      $machine[$carentry[1]]->addEvent('start', array('parked' => 'idling'));
      $machine[$carentry[1]]->addEvent('drive', array('idling' => 'driving'));
      $machine[$carentry[1]]->addEvent('stop', array('driving' => 'idling'));
      $machine[$carentry[1]]->addEvent('park', array('idling' => 'parked'));
      $machine[$carentry[1]]->setInitialState('parked');
    }

    # Start of the record
    if ($machine[$carentry[1]]->getCurrentState() == "parked" && $carentry[2] == "start") { 

      if (!isset ($carIDHistory[$carentry[1]])) {
        $carIDHistory[$carentry[1]] = $carentry[1];
        $carArray[$carentry[1]] = 0;
      }

      $car1record[$carentry[1]][$carArray[$carentry[1]]]['carID']                = $carentry[1];
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['rideID']               = $carArray[$carentry[1]];
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['StartDate']            = $carentry[0];
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['TransitionErrorCount'] = 0;
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['EndDate']              = "";
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['IdleCount']            = 0;
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['DriveCount']           = 0;
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['StopCount']            = 0;
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['TotalTransitions']     = 0;
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['Finished']             = "No";
    }

    # process the transaction logs rows
    $result = $machine[$carentry[1]]->$carentry[2]();
    $car1record[$carentry[1]][$carArray[$carentry[1]]]['LastState']      = $carentry[2];
    $car1record[$carentry[1]][$carArray[$carentry[1]]]['LastStateDate']  = $carentry[0];

    if ($result) {
      if ($machine[$carentry[1]]->getCurrentState() == "idling")  $car1record[$carentry[1]][$carArray[$carentry[1]]]['IdleCount']++;
      if ($machine[$carentry[1]]->getCurrentState() == "driving") $car1record[$carentry[1]][$carArray[$carentry[1]]]['DriveCount']++; // or IdleCount - 1
      if ($carentry[2]                              == "stop")    $car1record[$carentry[1]][$carArray[$carentry[1]]]['StopCount']++;  // or IdleCount - 1
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['TotalTransitions']++;
    }
    else {
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['TransitionErrorCount']++;
    }

    # End of the record
    if ($machine[$carentry[1]]->getCurrentState() == "parked" && $carentry[2] == "park") {
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['carID']                = $carentry[1];
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['rideID']               = $carArray[$carentry[1]];
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['EndDate']              = $carentry[0];
      $car1record[$carentry[1]][$carArray[$carentry[1]]]['Finished']             = "Yes";
      $carArray[$carentry[1]]++;
    }
  } // foreach ($car2csv as $carentry)

  // ------------------------------------------------------------------------------------------------

  // print_r($car1record); # for debugging
  // echo "<pre>"; print_r($car1record); echo "</pre>";
  // echo "<pre>"; print_r($carArray); echo "</pre>";

  # As table
  # Write the header first:
  reset($car1record);
  $first_car = key($car1record);
  $first_car_first_ride = key($car1record[$first_car]);
  echo "<table border = 1>\n";
  echo "<tr>\n";
  foreach ($car1record[$first_car][$first_car_first_ride] as $key => $value) { 
    echo "<th>" . $key . "</th>"; 
  }
  echo "</tr>\n";

  # Write the data:
  foreach ($car1record as $car) { 
    foreach ($car as $ride) {
      echo "<tr>\n";
      foreach ($ride as $field)
        echo "<td>$field</td>\n";
      echo "</tr>\n";
    }
  }
  echo "</table>";

  # Just incase if needed!
  // echo "<td>"; echo isset($ride['carID'])                 ? $ride['carID']                  : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['rideID'])                ? $ride['rideID']                 : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['StartDate'])             ? $ride['StartDate']              : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['TransitionErrorCount'])  ? $ride['TransitionErrorCount']   : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['EndDate'])               ? $ride['EndDate']                : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['IdleCount'])             ? $ride['IdleCount']              : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['DriveCount'])            ? $ride['DriveCount']             : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['StopCount'])             ? $ride['StopCount']              : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['TotalTransitions'])      ? $ride['TotalTransitions']       : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['LastState'])             ? $ride['LastState']              : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['LastStateDate'])         ? $ride['LastStateDate']          : ""; echo "</td>";
  // echo "<td>"; echo isset($ride['Finished'])              ? $ride['Finished']               : ""; echo "</td>";

  # As CSV
  # Write the header first:
  $fp = fopen('output2.csv', 'w');
  reset($car1record);
  $first_car = key($car1record);
  $first_car_first_ride = key($car1record[$first_car]);
  fputcsv($fp, array_keys($car1record[$first_car][$first_car_first_ride]));

  # Write the data:
  foreach ($car1record as $car) { 
    foreach ($car as $ride) {
      fputcsv($fp, $ride);
    }
  }

  fclose($fp);

?>
