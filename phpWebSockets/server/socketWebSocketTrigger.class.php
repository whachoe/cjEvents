<?php
/**
 * WebSocketTrigger class of phpWebSockets
 *
 * In this class you can define a method for every recieved command.
 * The returned string of the function will be send to the client.
 *
 * @author Moritz Wutz <moritzwutz@gmail.com>
 * @version 0.1
 * @package phpWebSockets
 */

class socketWebSocketTrigger
{
	function hello()
	{
		$a = 'hello world';

		return $a;
	}
}

?>