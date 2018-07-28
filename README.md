# State Machine
Simple state machine code using PHP, then transform it to be able to convert transaction log records into single row per unique transaction.

The asscii [flowchart](http://asciiflow.com/) is awsome!

## First - Basic Class for State Machine
The basic class idea taken from [stackoverflow](https://stackoverflow.com/questions/4274031/php-state-machine-framework) by [Gordon](https://stackoverflow.com/users/208809/gordon), thank you!
#### And by adding my notes to it:
I've created whole working code into a single PHP file called [simple_machine1](simple_machine1.php), this way it is easier for me to read and understand simple basic flow of the code. Maybe it could be useful for others!

The states and transitions does not make sense because the implementation is hardcoded and very easy! This is just for understanding the flow. 
This is to understand how the to implement state machine and how to link them with success and error state.
Please see the 2nd example that makes sense!
#### Note 2:
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
#### Notes 2
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

## Credits
Thanks to:
* [Gordon](https://stackoverflow.com/users/208809/gordon) for [stackoverflow post](https://stackoverflow.com/questions/4274031/php-state-machine-framework)
* [chriswoodford](https://stackoverflow.com/users/250198/chriswoodford) for the same [stackoverflow post](https://stackoverflow.com/questions/4274031/php-state-machine-framework), and for his [techne
](https://github.com/chriswoodford/techne) code
