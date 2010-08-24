<?php
    include_once 'event.php';
    include_once 'mycallbacks.php'; // the callbacks that are gonna used to handle the events

    session_start();

    $handler = cjEventHandler::singleton(false);

    
    
    // Setting up a handler for the events that are coming from javascript
    $handler->attach('phpevent1', 'answerjsevent');
    $handler->attach('phpevent2', 'answerjsevent');

//    var_dump($handler);

    // Work out the events we got from javascript
    $handler->handleJSEvents();

//    var_dump($handler);
    
    /**
     * Only use this if you are sure that all callbacks that are defined can be called in this script.
     * So you need to make sure everything is set up before doing a call to handle()
     *
     * Best practice would be to collect all your callback-classes and functions in an include-file and instantiate all the objects
     * that serve as callbacks in that file too. This way you can include that file in all the places where you handle PHP-events
     */
    $handler->handle();

//    var_dump($handler);
    
    // Now send all the js-events from PHP to the frontend
    $handler->sendPHPEvents();
?>
