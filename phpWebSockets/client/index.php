<?php

/* WebSocket implementation in php */

session_start();

?>

<html>
	<head>
		<title>WebSocket</title>

		<style type="text/css">
			html,body {
				font:normal 0.9em arial,helvetica;
			}
			#log {
				width: 440px;
				height: 200px;
				border: 1px solid #7F9DB9;
				overflow: auto;
				float: left;
			}
			#userlist {
				width: 440px;
				height: 200px;
				border: 1px solid #7F9DB9;
				overflow: auto;
				float: left;
			}
			#msg {
				width:330px;
			}
		</style>

		<script type="text/javascript">
			var socket;
			var start_command = false;
			var sendUser = false;

			function connect()
			{
				var host = "ws://localhost:12345/websocket/server/startDaemon.php";
				try
				{
					socket = new WebSocket(host);
					log('WebSocket - status '+socket.readyState);

					socket.onopen    = function(msg){
						log("Welcome - status "+this.readyState);
						$('connect').innerHTML = 'disconnect';
						$('connect').onclick = function(){
							quit();
						};
					};

					socket.onmessage = function(msg){
						log("Received: "+msg.data);
					};

					socket.onclose   = function(msg){ log("Disconnected - status "+this.readyState); };
				}
				catch(ex){ log(ex); }
				$("msg").focus();
			}

			function send()
			{
				var txt,msg;
				txt = $("msg");
				msg = txt.value;
				if(!msg) {
					alert("Message can not be empty"); return;
				}
				txt.value="";
				txt.focus();
				try{
					socket.send(msg);
					log('Sent: '+msg);
				}
				catch(ex){
					log(ex);
				}
			}

			function quit()
			{
				log("Goodbye!");
				socket.close();
				socket=null;
				$('connect').innerHTML = 'connect';
				$('connect').onclick = function(){
					connect();
				};
			}

			// Utilities
			function $(id){ return document.getElementById(id); }
			function log(msg){ $("log").innerHTML+="<br>"+msg; }
			function onkey(event){ if(event.keyCode==13){ send(); } }
		</script>
	</head>
	<body onload="init()">
		<h3>WebSocket v1.00</h3>
		<button onclick="connect()" id="connect">connect</button>
		<br />
		<br />
		<div id="log"></div>
		<div style="clear:left"></div>
		<input id="msg" type="textbox" onkeypress="onkey(event)"/>
		<button onclick="send()">Send</button>
		<button onclick="quit()">Quit</button>
		<div>Commands: hello, hi, name, age, date, time, thanks, bye</div>
	</body>
</html>