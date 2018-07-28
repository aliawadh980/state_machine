# State Machine

Simple state machine code using PHP, then transform it to be able to convert transaction log records into single row per unique transaction.

The asscii [flowchart](http://asciiflow.com/) is awsome!

# First Basic Class for State Machine
The basic class idea taken [from](https://stackoverflow.com/questions/4274031/php-state-machine-framework).

##### And by adding my notes to it
The states and transitions does not make sense because the implementation is hardcoded and very easy! This is just for understanding the flow. 
This is to understand how the to implement state machine and how to link them with success and error state.
Please see the 2nd example that makes sense!

##### Note 2
The states and transitions are hardcoded in the class!

car states: (3rd is introduced by me)
```
  - OffState
  - OnState
  - driveState (hidden as it is hardcoded twice in the above two states!)
```

transitions:
```
  - startEngine: OffState -> OnState
  - moveForward: OnState  -> OnState
```

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

