<?php
/**
 * An event handler. In essence this is an implementation of an Observer Pattern (http://en.wikipedia.org/wiki/Observer_pattern).
 * Events are lowercase strings.
 *
 * @author Jo Giraerts <jo.giraerts@gmail.com>
 * @license LGPL (http://www.gnu.org/licenses/lgpl.html)
 * @copyright Jo Giraerts 2010
 * @version 0.0.1
 */
class baseEventHandler
{
    protected $events;
    protected $event_callback_matrix;
    protected $handle_directly;
    
    protected function __construct($handle_directly = true)
    {
        $this->handle_directly = $handle_directly;
        $this->events = array();
        $this->event_callback_matrix = array();
        $this->handle();
    }

    static public function singleton($handle_directly = true)
    {
        if (!isset($_SESSION['cjEventHandler'])) {
            // function will always exist since we got a replacement for PHP-versions < 5.3
            if (function_exists('get_called_class'))
                $c = get_called_class();
            else
                $c = get_class(); //fallback: will return baseEventHandler even in extended classes.

            //echo "classname: $c\n<br/>";
            $_SESSION['cjEventHandler'] = new $c($handle_directly);
        }

        return $_SESSION['cjEventHandler'];
    }

    // disable clone. we are a singleton
    private function __clone() { return null; }

    /**
     * This method can get as much parameters as you like. All the parameters will be given to the callbackfunctions as argument
     * @param <type> $eventname Name of the event we want to raise
     */
    function raise($eventname, $args = null)
    {
        if (!is_array($this->events[strtolower($eventname)]))
            $this->events[strtolower($eventname)] = array();

        array_unshift($args, $eventname);
        array_push($this->events[strtolower($eventname)], $args);

        // automatically handle the event as soon as it is raised
        if ($this->handle_directly)
            return $this->handle();
        
        return true;
    }

    /**
     * Handle all the pending events
     */
    function handle()
    {
        foreach ($this->events as $event => $argumentarray) {
            // jsevent gets handled elsewhere, so ignore it here
            if ($event == 'jsevent')
                continue;

            if (!empty($argumentarray)) {
                foreach ($argumentarray as $arguments) {
                    // Execute all the callbacks in order
                    foreach ($this->event_callback_matrix[$event] as $cb) {
                        call_user_func_array($cb, $arguments);
                    }
                }
            }
            
            // If all went well, throw away this event
            $this->events[$event] = null;
        }

        return true;
    }

    /**
     * Attach a callback to a particular event
     * 
     * @param string $eventname
     * @param callback $callback
     */
    function attach($eventname, $callback)
    {
        if (!is_callable($callback))
            return false;
        
        // If this event has no callbacks yet, make an empty array
        if (!is_array($this->event_callback_matrix[strtolower($eventname)]))
            $this->event_callback_matrix[strtolower($eventname)] = array();
        
        // Only attach if this particular callback has not already been attached
        if (!in_array($callback, $this->event_callback_matrix[strtolower($eventname)])) {
            array_push($this->event_callback_matrix[strtolower($eventname)], $callback);
        }
        return true;
    }

    /**
     * Magic setter so we can do javascript style event-attaching:
     * $eventhandler->onload = "myCustomOnloadFunction";
     * This is completely analog to: $eventhandler->attach("onload", "myCustomOnloadFunction";
     *
     * @param string $eventname
     * @param callback $value
     * @return boolean
     */
    function __set($eventname, $value)
    {
        return $this->attach($eventname, $value);
    }

    /**
     *
     * @param <type> $eventname
     * @return mixed Returns an array of callbacks. Check http://be.php.net/manual/en/language.pseudo-types.php for more info on the pseudotype 'callback'
     */
    function __get($eventname)
    {
        return $this->event_callback_matrix[strtolower($eventname)];
    }

    /**
     * Checks if a certain event is already handled
     * 
     * @param <type> $eventname 
     */
    function __isset($eventname)
    {
       return isset($this->event_callback_matrix[strtolower($eventname)]);
    }

    /**
     * Unhooks all the handlers for a certain event
     * 
     * @param string $name
     */
    function  __unset($name) {
        unset($this->event_callback_matrix[strtolower($name)]);
    }
}


class cjEventHandler extends baseEventHandler
{
    /**
     * The next methods are to integrate more tightly with javascript. These are not part of the Observer pattern and they are
     * context-aware.
     */

    /**
     * Check if javascript has thrown events our way and schedule them together with the rest
     */
    function handleJSEvents()
    {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'raise_event') {
            $args = json_decode($_REQUEST['arguments']);
            $this->raise($_REQUEST['eventname'], $args);
        }
    }

    /**
     * Raise an event that needs to be sent to javascript.
     * @return boolean
     */
    function raiseJSEvent()
    {
        $args = func_get_args();
        
        // If this event has no callbacks yet, make an empty array
        if (!isset($this->events['jsevent']) || !is_array($this->events['jsevent']))
            $this->events['jsevent'] = array();
        
        $this->events['jsevent'][] = $args;
        return true;
    }

    /**
     * Send all the jsevents to javascript: Returns a json-string with all the events and their arguments
     */
    function sendPHPEvents()
    {
        if (isset($this->events['jsevent'])) {
            $data = $this->events['jsevent'];
            unset($this->events['jsevent']);
            returnJson($data);
        }
    }
}

/** Utility functions **/


// Helper function to send json strings
function returnJson($data)
{
    ob_start();
    header("Content-Type: application/json");
    echo json_encode($data);
    echo ob_get_clean();
    die;
}

/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if(!function_exists('get_called_class')) {
    function get_called_class($bt = false,$l = 1) {
        if (!$bt) $bt = debug_backtrace();
        if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
        if (!isset($bt[$l]['type'])) {
            throw new Exception ('type not set');
        }
        else switch ($bt[$l]['type']) {
            case '::':
                $lines = file($bt[$l]['file']);
                $i = 0;
                $callerLine = '';
                do {
                    $i++;
                    $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
                } while (stripos($callerLine,$bt[$l]['function']) === false);
                preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                            $callerLine,
                            $matches);
                if (!isset($matches[1])) {
                    // must be an edge case.
                    throw new Exception ("Could not find caller class: originating method call is obscured.");
                }
                switch ($matches[1]) {
                    case 'self':
                    case 'parent':
                        return get_called_class($bt,$l+1);
                    default:
                        return $matches[1];
                }
                // won't get here.
            case '->': switch ($bt[$l]['function']) {
                    case '__get':
                        // edge case -> get class of calling object
                        if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
                        return get_class($bt[$l]['object']);
                    default: return $bt[$l]['class'];
                }

            default: throw new Exception ("Unknown backtrace method type");
        }
    }
}