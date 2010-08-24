// The url to our websocket-server
var websockethost = "ws://localhost:12345/cjEvents/websocketserver.php";

// When using polling (in case there's no websockets available)'
var listener_interval_ms = 1000; // the time we wait for our next call for events from php (in milliseconds)
var _listenerinterval = null;


/**
 * Send an event to the PHP-backend
 * @public
 */
function raisePHPEvent(eventname)
{
    var args = new Array();
    var args2 = new Array();
    
    if (arguments) {
        args = arguments;
    } else if (this.arguments) {
        args = this.arguments;
    }

    for (i=0; i< args.length; i++) {
        args2[i] = args[i];
    }
    
    if (typeof(jQuery) !== 'undefined') {
        $.post('transferevents.php', {'action': 'raise_event', 'eventname': eventname, 'arguments': JSONstring.make(args2)}, function (data) {_cjEventHandler(data)});
    } else if (typeof(Prototype) !== 'undefined') {
        new Ajax.Request('transferevents.php', {
                'method': 'post',
                'parameters': {'action': 'raise_event', 'eventname': eventname, 'arguments': JSONstring.make(args2)},
                onSuccess: function (data) {_cjEventHandler(data.responseJSON);}
            });
    } else {
        alert('You need JQuery or Prototype to raise php events');
    }
}

/**
 * Start up the listener to receive events from the PHP-backend
 * @public
 */
function listenForPHPEvents()
{
    /* disable this stuff until i find time to integrate websockets
    // Use WebSockets if available
    if ("WebSocket" in window) {
        socket = new WebSocket(websockethost);
        
        // Hang an onmessage-listener to the socket to call the event handler
        socket.onmessage = _cjEventHandler;
    } else { // in case we don't have websockets: fallback on long-polling '
        clearInterval(_listenerinterval);
        _listenerinterval = setInterval("_cjEventListener()", listener_interval_ms);
    }
    */

    clearInterval(_listenerinterval);
    _listenerinterval = setInterval("_cjEventListener()", listener_interval_ms);
}

/**
 * Gets called by listenForPHPEvents and sets up the callback function to handle the events we get from the backend
 * @private
 */
function _cjEventListener()
{
    if (typeof(jQuery) !== 'undefined') {
        $.post('transferevents.php', {'action': 'listen_for_event'}, function (data) {_cjEventHandler(data)});
    } else if (typeof(Prototype) !== 'undefined') {
        new Ajax.Request('transferevents.php', {'method': 'post', 'parameters' : {'action': 'raise_event'},
            onSuccess: function (data) {_cjEventHandler(data.responseJSON);}
        });
    } else {
        alert('You need JQuery or Prototype to use this library!');
    }
}

/**
 * The actual callback that receives the PHP events and raises them again as Javascript-Events
 * @private
 */
function _cjEventHandler(data)
{
    // Debugging
    //console.log("Received data from php: ");
    //console.log(data);
    if (data) {
        if (typeof(jQuery) !== 'undefined') {
            for (i=0; i<data.length; i++){
                $(document.body).trigger(data[i][0], data[i]);
            }
        } else if (typeof(Prototype) !== 'undefined') {
            for (i=0; i<data.length; i++) {
                Event.fire($(document.body), 'phpevent:'+data[i][0], data[i]);
            }
        } else {
            alert('You need JQuery or Prototype to use this library!');
        }
    }
}