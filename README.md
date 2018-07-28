# State Machine
Simple state machine code using PHP, then transform it to be able to convert transaction log records into single row per unique transaction.

The asscii [flowchart](http://asciiflow.com/) is awsome!

Plain Text [Tables generator](https://www.tablesgenerator.com/text_tables) is also is awsome!

## Table of contents
- [Basic Class for State Machine](#first--basic-class-for-state-machine)
- [General purpose Class for State Machine](#second--general-purpose-class-for-state-machine)
- [Transaction logs with machine state](#third--transaction-logs-with-machine-state)
- More complex real life transactional log handling -- *to be done as forth example*

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

## Credits
Thanks to:
* [Gordon](https://stackoverflow.com/users/208809/gordon) for [stackoverflow post](https://stackoverflow.com/questions/4274031/php-state-machine-framework)
* [chriswoodford](https://stackoverflow.com/users/250198/chriswoodford) for the same [stackoverflow post](https://stackoverflow.com/questions/4274031/php-state-machine-framework), and for his [techne
](https://github.com/chriswoodford/techne) code
