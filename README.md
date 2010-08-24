cjEvents by Jo Giraerts <jo.giraerts@gmail.com>
========

With this library you can do event-based programming in PHP and Javascript.
The base-class is cjEventHandler in event.php. This class implements a singleton observer pattern.

Primary methods:
.   $handler->singleton($handle_directly): get an instance of this class. The parameter is a boolean which determines if events should be handled immediately after they are raised or if you want to call the handle()-method yourself.
.   $handler->attach: Link a callback-function or method to a self-named event.
.   $handler->raise : Raise an event. If you set $handle_directly to 'true' when instantiating this object, it will also be handled. This function accepts unlimited amount of parameters which will be given to the callback in 1 array.
.   $handler->handle: Handle all the raised events. Calls all the callbacks you defined.

For some examples on how you can use this class, check test_purephp.php.


The real strength comes when you start mixing it up with JQuery or Prototype. With this package it's easy to send events between PHP and Javascript.
Just check the test_jquery.html and test_prototype.html for some examples.





Todo:
-----
. websocket integration
. making the API cleaner to use
. wrapping the JS into it's own namespace and make it load automatically



Caveats:
--------

1. When sending events from javascript to PHP, the arguments you send along, will all arrive to the callback in 1 array.
<script>
raisePHPEvent('some event', 'arg1', 'arg2', 'arg3');
</script>

The correct callback for this event would be something like this:

<?php
    $handler = cjEventHandler::singleton();
    $handler->attach('some event', 'get_vars_from_js');

    function get_vars_from_js($params)
    {
        echo $params[0]; // 'some event'
        echo $params[1]; // 'arg1'
        echo $params[2]; // 'arg2'
        echo $params[3]; // 'arg3'
        echo $params[4]; // '' because it does not exist
    }
?>
