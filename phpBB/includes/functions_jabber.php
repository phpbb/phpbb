<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package phpBB3
*
*	Class.Jabber.PHP v0.4
*	(c) 2002 Carlo "Gossip" Zottmann
*	http://phpjabber.g-blog.net *** gossip@jabber.g-blog.net
*
*	The FULL documentation and examples for this software can be found at
*	http://phpjabber.g-blog.net (not many doc comments in here, sorry)
*
*	last modified: 27.04.2003 13:01:53 CET
*
* 
*	Modified by psoTFX, phpBB Group, 2003.
*	Removed functions/support not critical to integration with phpBB
*
*/
class Jabber
{
	var $server;
	var $port;
	var $username;
	var $password;
	var $resource;
	var $jid;

	var $connection;
	var $delay_disconnect;

	var $stream_id;
	var $roster;

	var $iq_sleep_timer;
	var $last_ping_time;

	var $packet_queue;
	var $subscription_queue;

	var $iq_version_name;
	var $iq_version_os;
	var $iq_version_version;

	var $error_codes;

	var $connected;
	var $keep_alive_id;
	var $returned_keep_alive;
	var $txnid;

	var $CONNECTOR;

	function Jabber()
	{
		$this->port					= "5222";

		$this->resource				= NULL;

		$this->packet_queue			= $this->subscription_queue	= array();

		$this->iq_sleep_timer		= $this->delay_disconnect		= 1;

		$this->returned_keep_alive	= TRUE;
		$this->txnid				= 0;

		$this->iq_version_name		= "Class.Jabber.PHP -- http://phpjabber.g-blog.net -- by Carlo 'Gossip' Zottmann, gossip@jabber.g-blog.net";
		$this->iq_version_version	= '0.4';
		$this->iq_version_os		= $_SERVER['SERVER_SOFTWARE'];

		$this->error_codes			= array(400 => 'Bad Request',
											401 => 'Unauthorized',
											402 => 'Payment Required',
											403 => 'Forbidden',
											404 => 'Not Found',
											405 => 'Not Allowed',
											406 => 'Not Acceptable',
											407 => 'Registration Required',
											408 => 'Request Timeout',
											409 => 'Conflict',
											500 => 'Internal Server Error',
											501 => 'Not Implemented',
											502 => 'Remove Server Error',
											503 => 'Service Unavailable',
											504 => 'Remove Server Timeout',
											510 => 'Disconnected');
	}

	function Connect()
	{
		$this->CONNECTOR = new CJP_StandardConnector;

		if ($this->CONNECTOR->OpenSocket($this->server, $this->port))
		{
			$this->SendPacket("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
			$this->SendPacket("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams'>\n");

			sleep(2);

			if ($this->_check_connected())
			{
				$this->connected = TRUE;	// Nathan Fritz
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	function Disconnect()
	{
		if (is_int($this->delay_disconnect))
		{
			sleep($this->delay_disconnect);
		}

		$this->SendPacket('</stream:stream>');
		$this->CONNECTOR->CloseSocket();
	}

	function CruiseControl($seconds = -1)
	{
		$count = 0;

		while ($count != $seconds)
		{
			$this->Listen();

			do
			{
				$packet = $this->GetFirstFromQueue();

				if ($packet)
				{
					$this->CallHandler($packet);
				}

			}
			while (count($this->packet_queue) > 1);

			$count += 0.25;
			usleep(250000);
			
			if ($this->last_ping_time != date('H:i'))
			{
				// Modified by Nathan Fritz
				if ($this->returned_keep_alive == FALSE)
				{
					$this->connected = FALSE;
					$this->AddToLog('EVENT: Disconnected');
				}

				$this->returned_keep_alive = FALSE;
				$this->keep_alive_id = 'keep_alive_' . time();
				$this->SendPacket("<iq id='{$this->keep_alive_id}'/>", 'CruiseControl');
				$this->last_ping_time = date("H:i");
			}
		}

		return TRUE;
	}

	function SendAuth()
	{
		$this->auth_id	= 'auth_' . md5(time() . $_SERVER['REMOTE_ADDR']);
		$this->jid		= "{$this->username}@{$this->server}/{$this->resource}";

		// request available authentication methods
		$payload	= "<username>{$this->username}</username>";
		$packet		= $this->SendIq(NULL, 'get', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		if ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id)
		{
			if (@function_exists('mhash') && isset($packet['iq']['#']['query'][0]['#']['sequence'][0]['#']) && isset($packet['iq']['#']['query'][0]['#']['token'][0]['#']))
			{
				// auth_0k
				return $this->_sendauth_0k($packet['iq']['#']['query'][0]['#']['token'][0]['#'], $packet['iq']['#']['query'][0]['#']['sequence'][0]['#']);
			}
			elseif (@function_exists('mhash') && isset($packet['iq']['#']['query'][0]['#']['digest']))
			{
				// digest
				return $this->_sendauth_digest();
			}
			elseif ($packet['iq']['#']['query'][0]['#']['password'])
			{
				// plain text
				return $this->_sendauth_plaintext();
			}
		}
		else
		{
			// no result returned
			return FALSE;
		}
	}

	function AccountRegistration($reg_email = NULL, $reg_name = NULL)
	{
		$packet = $this->SendIq($this->server, 'get', 'reg_01', 'jabber:iq:register');

		if ($packet)
		{
			$key = $this->GetInfoFromIqKey($packet);	// just in case a key was passed back from the server
			unset($packet);

			$payload = "<username>{$this->username}</username>
						<password>{$this->password}</password>
						<email>$reg_email</email>
						<name>$reg_name</name>\n";

			$payload .= ($key) ? "<key>$key</key>\n" : '';

			$packet = $this->SendIq($this->server, 'set', 'reg_01', 'jabber:iq:register', $payload);

			if ($this->GetInfoFromIqType($packet) == 'result')
			{
				$return_code = (isset($packet['iq']['#']['query'][0]['#']['registered'][0]['#'])) ? 1 : 2;
				$this->jid = ($this->resource) ? "{$this->username}@{$this->server}/{$this->resource}" : "{$this->username}@{$this->server}";
			}
			elseif ($this->GetInfoFromIqType($packet) == 'error' && isset($packet['iq']['#']['error'][0]['#']))
			{
				// "conflict" error, i.e. already registered
				if ($packet['iq']['#']['error'][0]['@']['code'] == '409')
				{
					$return_code = 1;
				}
				else
				{
					$return_code = 'Error ' . $packet['iq']['#']['error'][0]['@']['code'] . ': ' . $packet['iq']['#']['error'][0]['#'];
				}
			}

			return $return_code;
		}
		else
		{
			return 3;
		}
	}

	function ChangePassword($new_password)
	{
		$packet = $this->SendIq($this->server, 'get', 'A0', 'jabber:iq:register');

		if ($packet)
		{
			$key = $this->GetInfoFromIqKey($packet);	// just in case a key was passed back from the server
			unset($packet);

			$payload = "<username>{$this->username}</username>
						<password>{$new_password}</password>\n";
			$payload .= ($key) ? "<key>$key</key>\n" : '';

			$packet = $this->SendIq($this->server, 'set', 'A0', 'jabber:iq:register', $payload);

			if ($this->GetInfoFromIqType($packet) == 'result')
			{
				$return_code = (isset($packet['iq']['#']['query'][0]['#']['registered'][0]['#'])) ? 1 : 2;
			}
			elseif ($this->GetInfoFromIqType($packet) == 'error' && isset($packet['iq']['#']['error'][0]['#']))
			{
				// "conflict" error, i.e. already registered
				if ($packet['iq']['#']['error'][0]['@']['code'] == '409')
				{
					$return_code = 1;
				}
				else
				{
					$return_code = 'Error ' . $packet['iq']['#']['error'][0]['@']['code'] . ': ' . $packet['iq']['#']['error'][0]['#'];
				}
			}

			return $return_code;
		}
		else
		{
			return 3;
		}
	}

	function SendPacket($xml)
	{
		$xml = trim($xml);

		return ($this->CONNECTOR->WriteToSocket($xml)) ? TRUE : FALSE;
	}

	// get the transport registration fields
	// method written by Steve Blinch, http://www.blitzaffe.com 
	function TransportRegistrationDetails($transport)
	{
		$this->txnid++;
		$packet = $this->SendIq($transport, 'get', "reg_{$this->txnid}", "jabber:iq:register", NULL, $this->jid);

		if ($packet)
		{
			$res = array();

			foreach ($packet['iq']['#']['query'][0]['#'] as $element => $data)
			{
				if ($element != 'instructions' && $element != 'key')
				{
					$res[] = $element;
				}
			}

			return $res;
		}
		else
		{
			return 3;
		}
	}

	// register with the transport
	// method written by Steve Blinch, http://www.blitzaffe.com 
	function TransportRegistration($transport, $details)
	{
		$this->txnid++;
		$packet = $this->SendIq($transport, 'get', "reg_{$this->txnid}", "jabber:iq:register", NULL, $this->jid);

		if ($packet)
		{
			$key = $this->GetInfoFromIqKey($packet);	// just in case a key was passed back from the server
			unset($packet);
		
			$payload = ($key) ? "<key>$key</key>\n" : '';
			foreach ($details as $element => $value)
			{
				$payload .= "<$element>$value</$element>\n";
			}
		
			$packet = $this->SendIq($transport, 'set', "reg_{$this->txnid}", "jabber:iq:register", $payload);
		
			if ($this->GetInfoFromIqType($packet) == 'result')
			{
				if (isset($packet['iq']['#']['query'][0]['#']['registered'][0]['#']))
				{
					$return_code = 1;
				}
				else
				{
					$return_code = 2;
				}
			}
			elseif ($this->GetInfoFromIqType($packet) == 'error')
			{
				if (isset($packet['iq']['#']['error'][0]['#']))
				{
					$return_code = "Error " . $packet['iq']['#']['error'][0]['@']['code'] . ": " . $packet['iq']['#']['error'][0]['#'];
					$this->AddToLog('ERROR: TransportRegistration()');
				}
			}

			return $return_code;
		}
		else
		{
			return 3;
		}
	}

	function Listen()
	{
		unset($incoming);

		while ($line = $this->CONNECTOR->ReadFromSocket(4096))
		{
			$incoming .= $line;
		}

		$incoming = trim($incoming);

		if ($incoming != '')
		{
			$temp = $this->_split_incoming($incoming);

			for ($a = 0; $a < count($temp); $a++)
			{
				$this->packet_queue[] = $this->xmlize($temp[$a]);
			}
		}

		return TRUE;
	}

	function StripJID($jid = NULL)
	{
		preg_match('#(.*)\/(.*)#Ui', $jid, $temp);
		return ($temp[1] != '') ? $temp[1] : $jid;
	}

	function SendMessage($to, $type = 'normal', $id = NULL, $content = NULL, $payload = NULL)
	{
		if ($to && is_array($content))
		{
			if (!$id)
			{
				$id = $type . '_' . time();
			}

			$content = $this->_array_htmlspecialchars($content);

			$xml = "<message to='$to' type='$type' id='$id'>\n";

			if ($content['subject'])
			{
				$xml .= '<subject>' . $content['subject'] . "</subject>\n";
			}

			if ($content['thread'])
			{
				$xml .= '<thread>' . $content['thread'] . "</thread>\n";
			}

			$xml .= '<body>' . $content['body'] . "</body>\n";
			$xml .= $payload;
			$xml .= "</message>\n";

			if ($this->SendPacket($xml))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	function SendPresence($type = NULL, $to = NULL, $status = NULL, $show = NULL, $priority = NULL)
	{
		$xml = '<presence';
		$xml .= ($to) ? " to='$to'" : '';
		$xml .= ($type) ? " type='$type'" : '';
		$xml .= ($status || $show || $priority) ? ">\n" : " />\n";

		$xml .= ($status) ? " <status>$status</status>\n" : '';
		$xml .= ($show) ? "	<show>$show</show>\n" : '';
		$xml .= ($priority) ? "	<priority>$priority</priority>\n" : '';

		$xml .= ($status || $show || $priority) ? "</presence>\n" : '';

		return ($this->SendPacket($xml)) ? TRUE : FALSE;
	}

	function SendError($to, $id = NULL, $error_number, $error_message = NULL)
	{
		$xml = "<iq type='error' to='$to'";
		$xml .= ($id) ? " id='$id'" : '';
		$xml .= ">\n";
		$xml .= "	<error code='$error_number'>";
		$xml .= ($error_message) ? $error_message : $this->error_codes[$error_number];
		$xml .= "</error>\n";
		$xml .= '</iq>';

		$this->SendPacket($xml);
	}

	function GetFirstFromQueue()
	{
		return array_shift($this->packet_queue);
	}

	function GetFromQueueById($packet_type, $id)
	{
		$found_message = FALSE;

		foreach ($this->packet_queue as $key => $value)
		{
			if ($value[$packet_type]['@']['id'] == $id)
			{
				$found_message = $value;
				unset($this->packet_queue[$key]);

				break;
			}
		}

		return (is_array($found_message)) ? $found_message : FALSE;
	}

	function CallHandler($packet = NULL)
	{
		$packet_type	= $this->_get_packet_type($packet);

		if ($packet_type == 'message')
		{
			$type		= $packet['message']['@']['type'];
			$type		= ($type != '') ? $type : 'normal';
			$funcmeth	= "Handler_message_$type";
		}
		elseif ($packet_type == 'iq')
		{
			$namespace	= $packet['iq']['#']['query'][0]['@']['xmlns'];
			$namespace	= str_replace(':', '_', $namespace);
			$funcmeth	= "Handler_iq_$namespace";
		}
		elseif ($packet_type == 'presence')
		{
			$type		= $packet['presence']['@']['type'];
			$type		= ($type != '') ? $type : 'available';
			$funcmeth	= "Handler_presence_$type";
		}

		if ($funcmeth != '')
		{
			if (function_exists($funcmeth))
			{
				call_user_func($funcmeth, $packet);
			}
			elseif (method_exists($this, $funcmeth))
			{
				call_user_func(array(&$this, $funcmeth), $packet);
			}
			else
			{
				$this->Handler_NOT_IMPLEMENTED($packet);
			}
		}
	}

	function SendIq($to = NULL, $type = 'get', $id = NULL, $xmlns = NULL, $payload = NULL, $from = NULL)
	{
		if (!preg_match('#^(get|set|result|error)$#', $type))
		{
			unset($type);

			return FALSE;
		}
		elseif ($id && $xmlns)
		{
			$xml = "<iq type='$type' id='$id'";
			$xml .= ($to) ? " to='$to'" : '';
			$xml .= ($from) ? " from='$from'" : '';
			$xml .= ">
						<query xmlns='$xmlns'>
							$payload
						</query>
					</iq>";

			$this->SendPacket($xml);
			sleep($this->iq_sleep_timer);
			$this->Listen();

			return (preg_match('#^(get|set)$#', $type)) ? $this->GetFromQueueById('iq', $id) : TRUE;
		}
		else
		{
			return FALSE;
		}
	}


	// ======================================================================
	// private methods
	// ======================================================================

	function _sendauth_0k($zerok_token, $zerok_sequence)
	{
		// initial hash of password
		$zerok_hash = @mhash(MHASH_SHA1, $this->password);
		$zerok_hash = bin2hex($zerok_hash);

		// sequence 0: hash of hashed-password and token
		$zerok_hash = @mhash(MHASH_SHA1, $zerok_hash . $zerok_token);
		$zerok_hash = bin2hex($zerok_hash);

		// repeat as often as needed
		for ($a = 0; $a < $zerok_sequence; $a++)
		{
			$zerok_hash = @mhash(MHASH_SHA1, $zerok_hash);
			$zerok_hash = bin2hex($zerok_hash);
		}

		$payload = "<username>{$this->username}</username>
					<hash>$zerok_hash</hash>
					<resource>{$this->resource}</resource>";

		$packet = $this->SendIq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		return ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id) ? TRUE : FALSE;
	}

	function _sendauth_digest()
	{
		$payload = "<username>{$this->username}</username>
					<resource>{$this->resource}</resource>
					<digest>" . bin2hex(mhash(MHASH_SHA1, $this->stream_id . $this->password)) . "</digest>";

		$packet = $this->SendIq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		return ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id) ? TRUE : FALSE;
	}

	function _sendauth_plaintext()
	{
		$payload = "<username>{$this->username}</username>
					<password>{$this->password}</password>
					<resource>{$this->resource}</resource>";

		$packet = $this->SendIq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		return ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id) ? TRUE : FALSE;
	}

	function _listen_incoming()
	{
		unset($incoming);

		while ($line = $this->CONNECTOR->ReadFromSocket(4096))
		{
			$incoming .= $line;
		}

		$incoming = trim($incoming);
		return $this->xmlize($incoming);
	}

	function _check_connected()
	{
		$incoming_array = $this->_listen_incoming();

		if (is_array($incoming_array))
		{
			if ($incoming_array['stream:stream']['@']['from'] == $this->server
				&& $incoming_array['stream:stream']['@']['xmlns'] == 'jabber:client'
				&& $incoming_array['stream:stream']['@']['xmlns:stream'] == 'http://etherx.jabber.org/streams')
			{
				$this->stream_id = $incoming_array['stream:stream']['@']['id'];

				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	function _split_incoming($incoming)
	{
		$temp = preg_split('#<(message|iq|presence|stream)#', $incoming, -1, PREG_SPLIT_DELIM_CAPTURE);
		$array = array();

		for ($a = 1; $a < count($temp); $a = $a + 2)
		{
			$array[] = '<' . $temp[$a] . $temp[($a + 1)];
		}

		return $array;
	}

	function _get_packet_type($packet = NULL)
	{
		if (is_array($packet))
		{
			reset($packet);
			$packet_type = key($packet);
		}

		return ($packet_type) ? $packet_type : FALSE;
	}

	// _array_htmlspecialchars()
	// applies htmlspecialchars() to all values in an array
	function _array_htmlspecialchars(&$array)
	{
		if (is_array($array))
		{
			foreach ($array as $k => $v)
			{
				$v = (is_array($v)) ? $this->_array_htmlspecialchars($v) : htmlspecialchars($v);
			}
		}

		return $array;
	}

	// ======================================================================
	// <message/> parsers
	// ======================================================================

	function GetInfoFromMessageFrom($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['from'] : FALSE;
	}

	function GetInfoFromMessageType($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['type'] : FALSE;
	}

	function GetInfoFromMessageId($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['id'] : FALSE;
	}

	function GetInfoFromMessageThread($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['thread'][0]['#'] : FALSE;
	}

	function GetInfoFromMessageSubject($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['subject'][0]['#'] : FALSE;
	}

	function GetInfoFromMessageBody($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['body'][0]['#'] : FALSE;
	}

	function GetInfoFromMessageError($packet = NULL)
	{
		$error = preg_replace('#^\/$#', '', ($packet['message']['#']['error'][0]['@']['code'] . '/' . $packet['message']['#']['error'][0]['#']));
		return (is_array($packet)) ? $error : FALSE;
	}

	// ======================================================================
	// <iq/> parsers
	// ======================================================================

	function GetInfoFromIqFrom($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['from'] : FALSE;
	}

	function GetInfoFromIqType($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['type'] : FALSE;
	}

	function GetInfoFromIqId($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['id'] : FALSE;
	}

	function GetInfoFromIqKey($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['#']['query'][0]['#']['key'][0]['#'] : FALSE;
	}

	function GetInfoFromIqError($packet = NULL)
	{
		$error = preg_replace('#^\/$#', '', ($packet['iq']['#']['error'][0]['@']['code'] . '/' . $packet['iq']['#']['error'][0]['#']));
		return (is_array($packet)) ? $error : FALSE;
	}

	// ======================================================================
	// <message/> handlers
	// ======================================================================

	function Handler_message_normal($packet)
	{
		$from = $packet['message']['@']['from'];
	}

	function Handler_message_error($packet)
	{
		$from = $packet['message']['@']['from'];
	}

	// ======================================================================
	// <iq/> handlers
	// ======================================================================

	// simple client authentication
	function Handler_iq_jabber_iq_auth($packet)
	{
		$from	= $this->GetInfoFromIqFrom($packet);
		$id		= $this->GetInfoFromIqId($packet);

		$this->SendError($from, $id, 501);
	}

	// method for interactive registration
	function Handler_iq_jabber_iq_register($packet)
	{
		$from	= $this->GetInfoFromIqFrom($packet);
		$id		= $this->GetInfoFromIqId($packet);

		$this->SendError($from, $id, 501);
	}

	// keepalive method, added by Nathan Fritz
	function Handler_iq_($packet)
	{
		if ($this->keep_alive_id == $this->GetInfoFromIqId($packet))
		{
			$this->returned_keep_alive = TRUE;
		}
	}
	
	// ======================================================================
	// Generic handlers
	// ======================================================================

	// Generic handler for unsupported requests
	function Handler_NOT_IMPLEMENTED($packet)
	{
		$packet_type	= $this->_get_packet_type($packet);
		$from			= call_user_func(array(&$this, 'GetInfoFrom' . ucfirst($packet_type) . 'From'), $packet);
		$id				= call_user_func(array(&$this, 'GetInfoFrom' . ucfirst($packet_type) . 'Id'), $packet);

		$this->SendError($from, $id, 501);
	}

	// Third party code
	// m@d pr0ps to the coders ;)

	// xmlize()
	// (c) Hans Anderson / http://www.hansanderson.com/php/xml/
	function xmlize($data)
	{
		$vals = $index = $array = array();
		$parser = @xml_parser_create();
		@xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		@xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		@xml_parse_into_struct($parser, $data, $vals, $index);
		@xml_parser_free($parser);

		$i = 0;

		$tagname = $vals[$i]['tag'];
		$array[$tagname]['@'] = $vals[$i]['attributes'];
		$array[$tagname]['#'] = $this->_xml_depth($vals, $i);

		return $array;
	}

	// _xml_depth()
	// (c) Hans Anderson / http://www.hansanderson.com/php/xml/
	function _xml_depth($vals, &$i)
	{
		$children = array();

		if ($vals[$i]['value'])
		{
			array_push($children, trim($vals[$i]['value']));
		}

		while (++$i < count($vals))
		{
			switch ($vals[$i]['type'])
			{
				case 'cdata':
					array_push($children, trim($vals[$i]['value']));
	 				break;

				case 'complete':
					$tagname = $vals[$i]['tag'];
					$size = sizeof($children[$tagname]);
					$children[$tagname][$size]['#'] = trim($vals[$i]['value']);
					if ($vals[$i]['attributes'])
					{
						$children[$tagname][$size]['@'] = $vals[$i]['attributes'];
					}
					break;

				case 'open':
					$tagname = $vals[$i]['tag'];
					$size = sizeof($children[$tagname]);
					if ($vals[$i]['attributes'])
					{
						$children[$tagname][$size]['@'] = $vals[$i]['attributes'];
						$children[$tagname][$size]['#'] = $this->_xml_depth($vals, $i);
					}
					else
					{
						$children[$tagname][$size]['#'] = $this->_xml_depth($vals, $i);
					}
					break;

				case 'close':
					return $children;
					break;
			}
		}

		return $children;
	}

	// TraverseXMLize()
	// (c) acebone@f2s.com, a HUGE help!
	function TraverseXMLize($array, $arrName = 'array', $level = 0)
	{
		if ($level == 0)
		{
			echo '<pre>';
		}

		while (list($key, $val) = @each($array))
		{
			if (is_array($val))
			{
				$this->TraverseXMLize($val, $arrName . '[' . $key . ']', $level + 1);
			}
			else
			{
				echo '$' . $arrName . '[' . $key . '] = "' . $val . "\"\n";
			}
		}

		if ($level == 0)
		{
			echo '</pre>';
		}
	}
}

/**
* @package phpBB3
* MakeXML
*/
class MakeXML extends Jabber
{
	var $nodes;

	function MakeXML()
	{
		$nodes = array();
	}

	function AddPacketDetails($string, $value = NULL)
	{
		if (preg_match('#\(([0-9]*)\)$#i', $string))
		{
			$string .= '/["#"]';
		}

		$temp = @explode('/', $string);

		for ($a = 0; $a < count($temp); $a++)
		{
			$temp[$a] = preg_replace('#^[@]{1}([a-z0-9_]*)$#i', '["@"]["\1"]', $temp[$a]);
			$temp[$a] = preg_replace('#^([a-z0-9_]*)\(([0-9]*)\)$/i', '["\1"][\2]', $temp[$a]);
			$temp[$a] = preg_replace('#^([a-z0-9_]*)$#i', '["\1"]', $temp[$a]);
		}

		$node = implode('', $temp);

		// Yeahyeahyeah, I know it's ugly... get over it. ;)
		echo '$this->nodes' . $node . ' = "' . htmlspecialchars($value) . '";<br/>';
		eval('$this->nodes' . $node . ' = "' . htmlspecialchars($value) . '";');
	}

	function BuildPacket($array = NULL)
	{
		if (!$array)
		{
			$array = $this->nodes;
		}

		if (is_array($array))
		{
			array_multisort($array, SORT_ASC, SORT_STRING);

			foreach ($array as $key => $value)
			{
				if (is_array($value) && $key == '@')
				{
					foreach ($value as $subkey => $subvalue)
					{
						$subvalue = htmlspecialchars($subvalue);
						$text .= " $subkey='$subvalue'";
					}

					$text .= ">\n";

				}
				elseif ($key == '#')
				{
					$text .= htmlspecialchars($value);
				}
				elseif (is_array($value))
				{
					for ($a = 0; $a < count($value); $a++)
					{
						$text .= "<$key";

						if (!$this->_preg_grep_keys('#^@#', $value[$a]))
						{
							$text .= '>';
						}

						$text .= $this->BuildPacket($value[$a]);
						$text .= "</$key>\n";
					}
				}
				else
				{
					$value = htmlspecialchars($value);
					$text .= "<$key>$value</$key>\n";
				}
			}

			return $text;
		}
	}

	function _preg_grep_keys($pattern, $array)
	{
		foreach ($array as $key => $val)
		{
			if (preg_match($pattern, $key))
			{
				$newarray[$key] = $val;
			}
		}
		return (is_array($newarray)) ? $newarray : FALSE;
	}
}

/**
* @package phpBB3
* Connector
*/
class CJP_StandardConnector
{
	var $active_socket;

	function OpenSocket($server, $port)
	{
		if ($this->active_socket = @fsockopen($server, $port, $err, $err2, 5))
		{
			@socket_set_blocking($this->active_socket, 0);
			@socket_set_timeout($this->active_socket, 31536000);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function CloseSocket()
	{
		return @fclose($this->active_socket);
	}

	function WriteToSocket($data)
	{
		return @fwrite($this->active_socket, $data);
	}

	function ReadFromSocket($chunksize)
	{
		$buffer = stripslashes(@fread($this->active_socket, $chunksize));
		@set_magic_quotes_runtime(get_magic_quotes_gpc());

		return $buffer;
	}
}

?>