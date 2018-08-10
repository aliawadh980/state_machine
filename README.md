# State Machine
Simple state machine code using PHP, then transform it to be able to convert transaction log records into single row per unique transaction.

The asscii [flowchart](http://asciiflow.com/) is awsome!

Plain Text [Tables generator](https://www.tablesgenerator.com/text_tables) is also is awsome!

## Table of contents
- [Basic Class for State Machine](#first--basic-class-for-state-machine)
- [General purpose Class for State Machine](#second--general-purpose-class-for-state-machine)
- [Transaction logs with machine state](#third--transaction-logs-with-machine-state)
- [Real life example based on the Car Example]($forth--real-life-example-based-the-car-example)

## First - Basic Class for State Machine
The basic class idea taken from [stackoverflow](https://stackoverflow.com/questions/4274031/php-state-machine-framework) by [Gordon](https://stackoverflow.com/users/208809/gordon), thank you!
#### And by adding my notes to it:
I've created whole working code into a single PHP file called [simple_machine1](simple_machine1.php), this way it is easier for me to read and understand simple basic flow of the code. Maybe it could be useful for others!

The states and transitions does not make sense because the implementation is hardcoded and very easy! This is just for understanding the flow. 
This is to understand how the to implement state machine and how to link them with success and error state.
Please see the 2nd example that makes sense!
#### Note:
The states and transitions are hardcoded in the class!

Car states: (3rd is introduced by me)
```
  - OffState
  - OnState
  - driveState (hidden as it is hardcoded twice in the above two states!)
```
Transitions:
```
  - startEngine: OffState -> OnState
  - moveForward: OnState  -> OnState
```
Flowchart:
```
      +--+ startEngine   +--+
      |    & moveForward    |
      |                     |
      |                     v
+-----+----+           +----+----+
| OffState |           | OnState +------------+
+----------+           +----+----+            |
                            ^                 |
                            |                 |
                            +-- moveForward --+
```
For any other transition not mentioned an error should be thrown or return false *depending on the context of the problem that is needed to be solved.*
### Usage:
```php
  $car = new Car;
  $car->startEngine();
  $car->moveForward(); // works fine
```
And the only way allowed in start and keep moving forward only!
```php
  $car = new Car;
  $car->startEngine();
  $car->moveForward();
  $car->moveForward(); // works fine
```
Any other usage will throws exception as per the code and try & catch is needed here:
```php
  try { 
    $car = new Car; 
    $car->moveForward(); // throws Exception
  }
  catch(LogicException $e) { 
    echo $e->getMessage(); 
  }

  try { 
    $car = new Car;
    $car->startEngine();
    $car->startEngine(); // throws Exception
  }
  catch(LogicException $e) { 
    echo $e->getMessage(); 
  }
```
## Second - General purpose Class for State Machine
This has been taken from [techne](https://github.com/chriswoodford/techne) by [chriswoodford](https://github.com/chriswoodford), thank you!
#### And by adding my notes to it:
I've re-created a whole working code into a single PHP file called [simple_machine2](simple_machine2.php), this way it is easier for me to read and understand simple basic flow of the code. Maybe it could be useful for others!
#### Note:
The states and transitions are not hardcoded! It is created outside the class

Car states: 
```
  - parked
  - idling
  - driving
```
Transitions:
```
  - start: parked  -> idling
  - park:  idling  -> parked
  - drive: idling  -> driving
  - stop:  driving -> idling
```
Flowchart:
```
     +---- start ---+   +---- drive -----+
     |              |   |                |
     |              v   |                v
+----+----+       +-+---+--+        +----+----+
| parked  |       | idling |        | driving |
+----+----+       +-+---+--+        +----+----+
     ^              |   ^                |
     |              |   |                |
     +---- park ----+   +---- stop ------+
```
### Usage:
First defining the states and transitions. The code below define the above car example.
```php
  $machine = new FiniteStateMachine();
  $machine->addEvent('start', array('parked'  => 'idling'));
  $machine->addEvent('drive', array('idling'  => 'driving'));
  $machine->addEvent('stop',  array('driving' => 'idling'));
  $machine->addEvent('park',  array('idling'  => 'parked'));
```
Then start the testing:
```php
  $machine->setInitialState('parked');
  echo "<p>State: " . $machine->getCurrentState(); // should give: parked
  $machine->start();
  $machine->drive();
  echo "<p>State: " . $machine->getCurrentState(); // should give: driving
```
And if calling a none defined transition *same as agove an error is thrown*:
```php
  $machine->setInitialState('parked');

  try { $machine->drive(); } // cannot go from parked to driving
  catch(LogicException $e) { echo "<p>Error: " . $e->getMessage(); }

  try { $machine->ali(); } // no event called ali
  catch(Exception $e) { echo "<p>Error: " . $e->getMessage(); }
```

## Third - Transaction logs with machine state
Here I'll simulate real life example but with the car example above. Call it: Car transaction logs - transactional log data. Again the full code in done in one PHP file called [car_transactionlog1](car_transactionlog1.php).

It has car state, transition and date. The aim is to create single record per car per ride!

So the start of the ride is when: parked and then start

and the end of the ride is when:  idling and then park -> this will translate to park and then parked!

So Semi-real life car csv data:

| Date                | Transition | Location      |
| ------------------- | ---------- | ------------- |
| 2018-07-28 10:50:00 | start      | Home          |
| 2018-07-28 10:55:00 | drive      | Road          |
| 2018-07-28 11:04:00 | stop       | Traffic light |

And the desired output is:

| RideNo | StartDate           | EndDate             | IdleCount | DriveCount | StopCount | TotalTransitions |
|--------|---------------------|---------------------|-----------|------------|-----------|------------------|
| 1      | 2018-07-28 10:50:00 | 2018-07-28 11:19:00 | 4         | 2          | 2         | 8                |
| 2      | 2018-07-28 11:43:00 | 2018-07-28 11:58:00 | 3         | 2          | 2         | 6                |

#### Note:
This is not a stright forward state machine design! Normally in real life scenarios the data is not stright forward having clear state and transition definition. In the car CSV file there is no state, but we managed to understand there is a secrete finite state machine classic example behind it. In example 4 this will be dealt with more details.

More insights can be generated from the the simple car CSV file provided, like making use of `Location` to get unique visited lcoations, time spent in each location per ride, per day, average time spent per location, and so on!

### Usage:
The general class from example 2 will be used. Then read the data from CSV or inside PHP file
```php
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
```
Initialize few variables:
```php
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
  $ErrorFound = 0; // no need now, will be used in Example 4
```
Start looping through the CSV file and generate the desired output:
```php
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
    else { // No need for this part now, will be used in Example 4
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
```
Finally we have the desired output, write it back to CSV / DB or just display it:
```php
  print_r($car1record); # for debugging

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
```

## Forth - Real Life Example based on the Car Example
The code is done in single PHP file [car_transactionlog2](car_transactionlog2.php).

I'll jump directly to the input file:
```PHP
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
```
And looking the the desired output:

|carID | rideID | StartDate           | TransitionErrorCount  | EndDate             | IdleCount | DriveCount | StopCount | TotalTransitions | Finished | LastState | LastStateDate       |
|---   |---     |---                  | ---                   |---                  |---        |---         |---        |---               |---       |---        |---                  |
|1113  | 0      | 2018-07-28 08:15:00 | 0                     | 2018-07-28 08:50:00 | 2         | 1          | 1         | 4                | Yes      | park      | 2018-07-28 08:50:00 |
|1111  | 0      | 2018-07-28 10:50:00 | 1                     | 2018-07-28 11:19:00 | 4         | 3          | 3         | 8                | Yes      | park      | 2018-07-28 11:19:00 |
|1111  | 1      | 2018-07-28 11:43:00 | 0                     | 2018-07-28 11:58:00 | 3         | 2          | 2         | 6                | Yes      | park      | 2018-07-28 11:58:00 |
|1112  | 0      | 2018-07-28 10:52:00 | 0                     | 2018-07-28 10:58:00 | 2         | 1          | 1         | 4                | Yes      | park      | 2018-07-28 10:58:00 |
|1115  | 0      | 2018-07-28 10:52:00 | 0                     |                     | 1         | 1          | 0         | 2                | No       | drive     | 2018-07-28 10:53:00 |
|1114  | 0      | 2018-07-28 11:12:00 | 0                     | 2018-07-28 11:15:00 | 2         | 1          | 1         | 4                | Yes      | park      | 2018-07-28 11:15:00 |
|1116  | 0      | 2018-07-28 13:10:00 | 0                     |                     | 2         | 1          | 1         | 3                | No       | stop      | 2018-07-28 13:24:00 |


### Note:
Here finding the solution depends on the context of the problem and what is the desired solution looking for. For this example the problem and solution is completely make from my own.

### Usage:
The code should be easy to go through which is based on the previous examples.

```PHP
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
```

## Credits
Thanks to:
* [Gordon](https://stackoverflow.com/users/208809/gordon) for [stackoverflow post](https://stackoverflow.com/questions/4274031/php-state-machine-framework)
* [chriswoodford](https://stackoverflow.com/users/250198/chriswoodford) for the same [stackoverflow post](https://stackoverflow.com/questions/4274031/php-state-machine-framework), and for his [techne
](https://github.com/chriswoodford/techne) code
