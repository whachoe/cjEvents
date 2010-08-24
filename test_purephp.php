<?php
include_once 'event.php';

session_start();
$handler = cjEventHandler::singleton();

function simplecallback()
{
    echo "Simplecallback has been called\n<br/>\n";
    $args = func_get_args();
    var_dump($args);
    echo "\n<br/>\n";
}

class simpleclass
{
    function __construct()
    {}

    function methodcallback()
    {
        echo "methodcallback has been called\n<br/>\n";
        $args = func_get_args();
        var_dump($args);
        echo "\n<br/>\n";
    }

    static function staticcallback()
    {
        echo "staticcallback has been called\n<br/>\n";
        $args = func_get_args();
        var_dump($args);
        echo "\n<br/>\n";
    }
}

// First test: simplecallback: just call a function when you get an event: simple_event
$handler->simple_event = "simplecallback";
$handler->raise('simple_event', array("arg1", array('arg2-0', 'arg2-1')));

// Second test: method callback.
// We can also call methods of an instantiated object by giving an array with the object name and
// the name of the method to call
$obj = new simpleclass();
$handler->attach("method_event", array($obj, 'methodcallback'));
$handler->raise('method_event', array("argument"));

// Third test: static method callback
$handler->attach("static_event", array('simpleclass', 'staticcallback'));
$handler->raise('static_event', 'argument1', 'argument2');


// Handle all the raised events if not handled immediately after the raising
$handler->handle();
//$_SESSION['cjEventHandler'] = $handler;
?>