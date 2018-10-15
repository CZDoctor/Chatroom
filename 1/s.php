<?php
$host = '192.168.21.102';
$port = '9505';
$null = NULL;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, $port);
socket_listen($socket);

$clients = array($socket);
$client_list=array();
$privacy=array();

while(true){
    $changed = $clients;
    socket_select($changed, $null, $null, 0, 10);

    if(in_array($socket, $changed)){
        $socket_new = socket_accept($socket);
        $clients[]  = $socket_new;

        $header = socket_read($socket_new, 1024);
        perform_handshaking($header, $socket_new, $host, $port);
    }

    foreach($changed as $changed_socket){

        while(socket_recv($changed_socket, $buf, 1024, 0) >= 1){
            $received_text = unmask($buf);
            $tst_msg       = json_decode($received_text);
			$user_type     = $tst_msg->type;
            $user_name     = $tst_msg->name;
            $user_message  = $tst_msg->message;
			$to_name       = $tst_msg->to_name;
			
			$link = mysqli_connect('localhost','root','123456','liaotianshi');
			mysqli_query($link,"set names 'utf8'");
			if($user_type=='mess' or $user_type=='priv')
			{
				$name1=$user_name;
				$time1=date('Y-m-d H:i:s');
				$content1=$user_message;
				$to_name1=$to_name;
				mysqli_query($link,"INSERT INTO message(time,name,to_name,content) VALUES ('$time1','$name1','$to_name1','$content1')");
			}

            if($user_type=='login'){
            	array_push($client_list,$user_name);
				$privacy[$user_name]=$changed_socket;
            	$response_text = mask(json_encode(array(
                	'type'    => 'login',
                	'name'    => 'null',
                	'message' => $client_list
            	)));
            	send_message($response_text);
				$response_text = mask(json_encode(array(
                	'type'    => 'system',
                	'name'    => 'null',
                	'message' => $user_name.'进入聊天室',
            	)));
            	send_message($response_text);
            }
			
			else if($user_type=='mess'){
            $response_text = mask(json_encode(array(
                'type'    => 'usermsg',
                'name'    => $user_name,
                'message' => $user_message
            )));
            send_message($response_text);
			}
			
			else if($user_type=='priv'){
				$msg = mask(json_encode(array(
             		'type'    => 'priv',
                	'name'    => $user_name,
                	'message' => $user_message
            	)));
            	$priv_socket=$privacy[$to_name];
				
				if(!$priv_socket){
					$msg = mask(json_encode(array(
	             		'type'    => 'system',
	                	'name'    => $user_name,
	                	'message' => '发送失败，没有该用户！'
	            	)));
	            	$priv_socket=$privacy[$user_name];
					@socket_write($priv_socket, $msg, strlen($msg));
				}
				else{
					@socket_write($priv_socket, $msg, strlen($msg));
				}
			}

			else if($user_type=='kick'){
				$response_text = mask(json_encode(array(
                	'type'    => 'system',
                	'name'    => $to_name,
                	'message' => $user_message
            	)));
           		send_message($response_text);
				
				global $clients;
				foreach($clients as $h => $kick_client){
					if($kick_client==$privacy[$to_name]){
						socket_close($kick_client);
						unset($clients[$h]);
					}
				}
				$clients=array_values($clients);
				foreach($client_list as $f => $g){
					if($g==$to_name){
						unset($client_list[$f]);
					}
				}
				unset($privacy[$to_name]);
				$client_list=array_values($client_list);
				$response_text = mask(json_encode(array(
                	'type'    => 'login',
                	'name'    => 'null',
                	'message' => $client_list
            	)));
            	send_message($response_text);
			}
            break 2;
        }
    }
}
socket_close($socket);

function send_message($msg){
    global $clients;
    foreach($clients as $changed_socket){
        @socket_write($changed_socket, $msg, strlen($msg));
    }
    return true;
}

function unmask($text){
    $length = ord($text[1]) & 127;
    if($length == 126){
        $masks = substr($text, 4, 4);
        $data  = substr($text, 8);
    }elseif($length == 127){
        $masks = substr($text, 10, 4);
        $data  = substr($text, 14);
    }else{
        $masks = substr($text, 2, 4);
        $data  = substr($text, 6);
    }
    $text = "";
    for($i = 0; $i < strlen($data); ++$i){
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

function mask($text){
    $b1     = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if($length <= 125){
        $header = pack('CC', $b1, $length);
    }elseif($length > 125 && $length < 65536){
        $header = pack('CCn', $b1, 126, $length);
    }elseif($length >= 65536){
        $header = pack('CCNN', $b1, 127, $length);
    }
    return $header . $text;
}

function perform_handshaking($receved_header, $client_conn, $host, $port){
    $headers = array();
    $lines   = preg_split("/\r\n/", $receved_header);
    foreach($lines as $line){
        $line = chop($line);
        if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)){
            $headers[$matches[1]] = $matches[2];
        }
    }

    $secKey    = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $upgrade   = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" . "Upgrade: websocket\r\n" . "Connection: Upgrade\r\n" . "WebSocket-Origin: $host\r\n" . "WebSocket-Location: ws://$host:$port/demo/shout.php\r\n" . "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    socket_write($client_conn, $upgrade, strlen($upgrade));
}
