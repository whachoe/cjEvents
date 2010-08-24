<?php

function answerjsevent()
{
    $args = func_get_args();

    // $args[0] is always the eventname
    // the rest of the array-elements will be the arguments you gave to the raise-function
    $eventname = $args[0];
    $new_event = ($eventname == 'phpevent1' ? 'jsevent1' : 'jsevent2');

    $handler = cjEventHandler::singleton();
    $handler->raiseJSEvent($new_event, 'arg1', 'arg2', array('arg3', 'arg4'));
}

