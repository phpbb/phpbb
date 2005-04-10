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
class jabber
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

	var $connector;

	function jabber()
	{
		$this->port					= '5222';
		$this->resource				= NULL;
		$this->packet_queue			= $this->subscription_queue = array();
		$this->iq_sleep_timer		= $this->delay_disconnect = 1;

		$this->returned_keep_alive	= true;
		$this->txnid				= 0;

		$this->iq_version_name		= "Class.Jabber.PHP -- http://phpjabber.g-blog.net -- by Carlo 'Gossip' Zottmann, gossip@jabber.g-blog.net";
		$this->iq_version_version	= '0.4';
		$this->iq_version_os		= $_SERVER['SERVER_SOFTWARE'];

		$this->error_codes			= array(
			400 => 'Bad Request',
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
			510 => 'Disconnected'
		);
	}

	function connect()
	{
		$this->connector = new cjp_standard_connector;

		if ($this->connector->open_socket($this->server, $this->port))
		{
			$this->send_packet("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
			$this->send_packet("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams'>\n");

			sleep(2);

			if ($this->_check_connected())
			{
				$this->connected = true; // Nathan Fritz
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function disconnect()
	{
		if (is_int($this->delay_disconnect))
		{
			sleep($this->delay_disconnect);
		}

		$this->send_packet('</stream:stream>');
		$this->connector->close_socket();
	}

	function cruise_control($seconds = -1)
	{
		$count = 0;

		while ($count != $seconds)
		{
			$this->listen();

			do
			{
				$packet = $this->get_first_from_queue();

				if ($packet)
				{
					$this->call_handler($packet);
				}

			}
			while (sizeof($this->packet_queue) > 1);

			$count += 0.25;
			usleep(250000);
			
			if ($this->last_ping_time != date('H:i'))
			{
				// Modified by Nathan Fritz
				if ($this->returned_keep_alive == false)
				{
					$this->connected = false;
					//EVENT: Disconnected
				}

				$this->returned_keep_alive = FALSE;
				$this->keep_alive_id = 'keep_alive_' . time();
				$this->send_packet("<iq id='{$this->keep_alive_id}'/>", 'cruise_control');
				$this->last_ping_time = date('H:i');
			}
		}

		return true;
	}

	function send_auth()
	{
		$this->auth_id	= 'auth_' . md5(time() . $_SERVER['REMOTE_ADDR']);
		$this->jid		= "{$this->username}@{$this->server}/{$this->resource}";

		// request available authentication methods
		$payload	= "<username>{$this->username}</username>";
		$packet		= $this->send_iq(NULL, 'get', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		if ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id)
		{
			if (@function_exists('mhash') && isset($packet['iq']['#']['query'][0]['#']['sequence'][0]['#']) && isset($packet['iq']['#']['query'][0]['#']['token'][0]['#']))
			{
				// auth_0k
				return $this->_sendauth_ok($packet['iq']['#']['query'][0]['#']['token'][0]['#'], $packet['iq']['#']['query'][0]['#']['sequence'][0]['#']);
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
			return false;
		}
	}

	function account_registration($reg_email = NULL, $reg_name = NULL)
	{
		$packet = $this->send_iq($this->server, 'get', 'reg_01', 'jabber:iq:register');

		if ($packet)
		{
			$key = $this->get_info_from_iq_key($packet); // just in case a key was passed back from the server
			unset($packet);

			$payload = "<username>{$this->username}</username>
						<password>{$this->password}</password>
						<email>$reg_email</email>
						<name>$reg_name</name>\n";

			$payload .= ($key) ? "<key>$key</key>\n" : '';

			$packet = $this->send_iq($this->server, 'set', 'reg_01', 'jabber:iq:register', $payload);

			if ($this->get_info_from_iq_type($packet) == 'result')
			{
				$return_code = (isset($packet['iq']['#']['query'][0]['#']['registered'][0]['#'])) ? 1 : 2;
				$this->jid = ($this->resource) ? "{$this->username}@{$this->server}/{$this->resource}" : "{$this->username}@{$this->server}";
			}
			elseif ($this->get_info_from_iq_type($packet) == 'error' && isset($packet['iq']['#']['error'][0]['#']))
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

	function change_password($new_password)
	{
		$packet = $this->send_iq($this->server, 'get', 'A0', 'jabber:iq:register');

		if ($packet)
		{
			$key = $this->get_info_from_iq_key($packet); // just in case a key was passed back from the server
			unset($packet);

			$payload = "<username>{$this->username}</username>
						<password>{$new_password}</password>\n";
			$payload .= ($key) ? "<key>$key</key>\n" : '';

			$packet = $this->send_iq($this->server, 'set', 'A0', 'jabber:iq:register', $payload);

			if ($this->get_info_from_iq_type($packet) == 'result')
			{
				$return_code = (isset($packet['iq']['#']['query'][0]['#']['registered'][0]['#'])) ? 1 : 2;
			}
			elseif ($this->get_info_from_iq_type($packet) == 'error' && isset($packet['iq']['#']['error'][0]['#']))
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

	function send_packet($xml)
	{
		$xml = trim($xml);

		return ($this->connector->write_to_socket($xml)) ? true : false;
	}

	// get the transport registration fields
	// method written by Steve Blinch, http://www.blitzaffe.com 
	function transport_registration_details($transport)
	{
		$this->txnid++;
		$packet = $this->send_iq($transport, 'get', "reg_{$this->txnid}", "jabber:iq:register", NULL, $this->jid);

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
	function transport_registration($transport, $details)
	{
		$this->txnid++;
		$packet = $this->send_iq($transport, 'get', "reg_{$this->txnid}", "jabber:iq:register", NULL, $this->jid);

		if ($packet)
		{
			$key = $this->get_info_from_iq_key($packet); // just in case a key was passed back from the server
			unset($packet);
		
			$payload = ($key) ? "<key>$key</key>\n" : '';
			foreach ($details as $element => $value)
			{
				$payload .= "<$element>$value</$element>\n";
			}
		
			$packet = $this->send_iq($transport, 'set', "reg_{$this->txnid}", "jabber:iq:register", $payload);
		
			if ($this->get_info_from_iq_type($packet) == 'result')
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
			elseif ($this->get_info_from_iq_type($packet) == 'error')
			{
				if (isset($packet['iq']['#']['error'][0]['#']))
				{
					$return_code = "Error " . $packet['iq']['#']['error'][0]['@']['code'] . ": " . $packet['iq']['#']['error'][0]['#'];
					// ERROR: TransportRegistration()
				}
			}

			return $return_code;
		}
		else
		{
			return 3;
		}
	}

	function listen()
	{
		$incoming = '';

		while ($line = $this->connector->read_from_socket(4096))
		{
			$incoming .= $line;
		}

		$incoming = trim($incoming);

		if ($incoming != '')
		{
			$temp = $this->_split_incoming($incoming);

			for ($a = 0, $size = sizeof($temp); $a < $size; $a++)
			{
				$this->packet_queue[] = $this->xmlize($temp[$a]);
			}
		}

		return true;
	}

	function strip_jid($jid = NULL)
	{
		preg_match('#(.*)\/(.*)#Ui', $jid, $temp);
		return ($temp[1] != '') ? $temp[1] : $jid;
	}

	function send_message($to, $type = 'normal', $id = NULL, $content = NULL, $payload = NULL)
	{
		if ($to && is_array($content))
		{
			if (!$id)
			{
				$id = $type . '_' . time();
			}

			$content = $this->_array_htmlspecialchars($content);

			$xml = "<message to='$to' type='$type' id='$id'>\n";

			if (isset($content['subject']) && $content['subject'])
			{
				$xml .= '<subject>' . $content['subject'] . "</subject>\n";
			}

			if (isset($content['thread']) && $content['thread'])
			{
				$xml .= '<thread>' . $content['thread'] . "</thread>\n";
			}

			$xml .= '<body>' . $content['body'] . "</body>\n";
			$xml .= $payload;
			$xml .= "</message>\n";

			if ($this->send_packet($xml))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function send_presence($type = NULL, $to = NULL, $status = NULL, $show = NULL, $priority = NULL)
	{
		$xml = '<presence';
		$xml .= ($to) ? " to='$to'" : '';
		$xml .= ($type) ? " type='$type'" : '';
		$xml .= ($status || $show || $priority) ? ">\n" : " />\n";

		$xml .= ($status) ? " <status>$status</status>\n" : '';
		$xml .= ($show) ? "	<show>$show</show>\n" : '';
		$xml .= ($priority) ? "	<priority>$priority</priority>\n" : '';

		$xml .= ($status || $show || $priority) ? "</presence>\n" : '';

		return ($this->send_packet($xml)) ? true : false;
	}

	function send_error($to, $id = NULL, $error_number, $error_message = NULL)
	{
		$xml = "<iq type='error' to='$to'";
		$xml .= ($id) ? " id='$id'" : '';
		$xml .= ">\n";
		$xml .= "	<error code='$error_number'>";
		$xml .= ($error_message) ? $error_message : $this->error_codes[$error_number];
		$xml .= "</error>\n";
		$xml .= '</iq>';

		$this->send_packet($xml);
	}

	function get_first_from_queue()
	{
		return array_shift($this->packet_queue);
	}

	function get_from_queue_by_id($packet_type, $id)
	{
		$found_message = false;

		foreach ($this->packet_queue as $key => $value)
		{
			if ($value[$packet_type]['@']['id'] == $id)
			{
				$found_message = $value;
				unset($this->packet_queue[$key]);

				break;
			}
		}

		return (is_array($found_message)) ? $found_message : false;
	}

	function call_handler($packet = NULL)
	{
		$packet_type = $this->_get_packet_type($packet);

		if ($packet_type == 'message')
		{
			$type		= $packet['message']['@']['type'];
			$type		= ($type != '') ? $type : 'normal';
			$funcmeth	= "handler_message_$type";
		}
		elseif ($packet_type == 'iq')
		{
			$namespace	= $packet['iq']['#']['query'][0]['@']['xmlns'];
			$namespace	= str_replace(':', '_', $namespace);
			$funcmeth	= "handler_iq_$namespace";
		}
		elseif ($packet_type == 'presence')
		{
			$type		= $packet['presence']['@']['type'];
			$type		= ($type != '') ? $type : 'available';
			$funcmeth	= "handler_presence_$type";
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
				$this->handler_not_implemented($packet);
			}
		}
	}

	function send_iq($to = NULL, $type = 'get', $id = NULL, $xmlns = NULL, $payload = NULL, $from = NULL)
	{
		if (!preg_match('#^(get|set|result|error)$#', $type))
		{
			unset($type);

			return false;
		}
		else if ($id && $xmlns)
		{
			$xml = "<iq type='$type' id='$id'";
			$xml .= ($to) ? " to='$to'" : '';
			$xml .= ($from) ? " from='$from'" : '';
			$xml .= ">
						<query xmlns='$xmlns'>
							$payload
						</query>
					</iq>";

			$this->send_packet($xml);
			sleep($this->iq_sleep_timer);
			$this->listen();

			return (preg_match('#^(get|set)$#', $type)) ? $this->get_from_queue_by_id('iq', $id) : true;
		}
		else
		{
			return false;
		}
	}


	// ======================================================================
	// private methods
	// ======================================================================

	function _sendauth_ok($zerok_token, $zerok_sequence)
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

		$packet = $this->send_iq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		return ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id) ? true : false;
	}

	function _sendauth_digest()
	{
		$payload = "<username>{$this->username}</username>
					<resource>{$this->resource}</resource>
					<digest>" . bin2hex(mhash(MHASH_SHA1, $this->stream_id . $this->password)) . "</digest>";

		$packet = $this->send_iq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		return ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id) ? true : false;
	}

	function _sendauth_plaintext()
	{
		$payload = "<username>{$this->username}</username>
					<password>{$this->password}</password>
					<resource>{$this->resource}</resource>";

		$packet = $this->send_iq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		return ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id) ? true : false;
	}

	function _listen_incoming()
	{
		$incoming = '';
		
		while ($line = $this->connector->read_from_socket(4096))
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

				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function _split_incoming($incoming)
	{
		$temp = preg_split('#<(message|iq|presence|stream)#', $incoming, -1, PREG_SPLIT_DELIM_CAPTURE);
		$array = array();

		for ($a = 1; $a < sizeof($temp); $a = $a + 2)
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

		return ($packet_type) ? $packet_type : false;
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

	function get_info_from_message_from($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['from'] : false;
	}

	function get_info_from_message_type($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['type'] : false;
	}

	function get_info_from_message_id($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['id'] : false;
	}

	function get_info_from_message_thread($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['thread'][0]['#'] : false;
	}

	function get_info_from_message_subject($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['subject'][0]['#'] : false;
	}

	function get_info_from_message_body($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['body'][0]['#'] : false;
	}

	function get_info_from_message_error($packet = NULL)
	{
		$error = preg_replace('#^\/$#', '', ($packet['message']['#']['error'][0]['@']['code'] . '/' . $packet['message']['#']['error'][0]['#']));
		return (is_array($packet)) ? $error : false;
	}

	// ======================================================================
	// <iq/> parsers
	// ======================================================================

	function get_info_from_iq_from($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['from'] : false;
	}

	function get_info_from_iq_type($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['type'] : false;
	}

	function get_info_from_iq_id($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['id'] : false;
	}

	function get_info_from_iq_key($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['#']['query'][0]['#']['key'][0]['#'] : false;
	}

	function get_info_from_iq_error($packet = NULL)
	{
		$error = preg_replace('#^\/$#', '', ($packet['iq']['#']['error'][0]['@']['code'] . '/' . $packet['iq']['#']['error'][0]['#']));
		return (is_array($packet)) ? $error : false;
	}

	// ======================================================================
	// <message/> handlers
	// ======================================================================

	function handler_message_normal($packet)
	{
		$from = $packet['message']['@']['from'];
	}

	function handler_message_error($packet)
	{
		$from = $packet['message']['@']['from'];
	}

	// ======================================================================
	// <iq/> handlers
	// ======================================================================

	// simple client authentication
	function handler_iq_jabber_iq_auth($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
	}

	// method for interactive registration
	function handler_iq_jabber_iq_register($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
	}

	// keepalive method, added by Nathan Fritz
	function handler_iq_($packet)
	{
		if ($this->keep_alive_id == $this->get_info_from_iq_id($packet))
		{
			$this->returned_keep_alive = true;
		}
	}
	
	// ======================================================================
	// Generic handlers
	// ======================================================================

	// Generic handler for unsupported requests
	function handler_not_implemented($packet)
	{
		$packet_type	= $this->_get_packet_type($packet);
		$from			= call_user_func(array(&$this, 'get_info_from_' . strtolower($packet_type) . '_from'), $packet);
		$id				= call_user_func(array(&$this, 'get_info_from_' . strtolower($packet_type) . '_id'), $packet);

		$this->send_error($from, $id, 501);
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

		if (isset($vals[$i]['value']) && $vals[$i]['value'])
		{
			array_push($children, trim($vals[$i]['value']));
		}

		while (++$i < sizeof($vals))
		{
			switch ($vals[$i]['type'])
			{
				case 'cdata':
					array_push($children, trim($vals[$i]['value']));
	 				break;

				case 'complete':
					$tagname = $vals[$i]['tag'];
					$size = (isset($children[$tagname])) ? sizeof($children[$tagname]) : 0;
					$children[$tagname][$size]['#'] = (isset($vals[$i]['value'])) ? trim($vals[$i]['value']) : '';
					if (isset($vals[$i]['attributes']) && $vals[$i]['attributes'])
					{
						$children[$tagname][$size]['@'] = $vals[$i]['attributes'];
					}
					break;

				case 'open':
					$tagname = $vals[$i]['tag'];
					$size = (isset($children[$tagname])) ? sizeof($children[$tagname]) : 0;
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

	// traverse_xmlize()
	// (c) acebone@f2s.com, a HUGE help!
	function traverse_xmlize($array, $arr_name = 'array', $level = 0)
	{
		if ($level == 0)
		{
			echo '<pre>';
		}

		while (list($key, $val) = @each($array))
		{
			if (is_array($val))
			{
				$this->traverse_xmlize($val, $arr_name . '[' . $key . ']', $level + 1);
			}
			else
			{
				echo '$' . $arr_name . '[' . $key . '] = "' . $val . "\"\n";
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
* make_xml
* Currently not in use
*/
class make_xml extends jabber
{
	var $nodes;

	function make_xml()
	{
		$nodes = array();
	}

	function add_packet_details($string, $value = NULL)
	{
		if (preg_match('#\(([0-9]*)\)$#i', $string))
		{
			$string .= '/["#"]';
		}

		$temp = @explode('/', $string);

		for ($a = 0, $size = sizeof($temp); $a < $size; $a++)
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

	function build_packet($array = NULL)
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
					for ($a = 0, $size = sizeof($value); $a < $size; $a++)
					{
						$text .= "<$key";

						if (!$this->_preg_grep_keys('#^@#', $value[$a]))
						{
							$text .= '>';
						}

						$text .= $this->build_packet($value[$a]);
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
		return (is_array($newarray)) ? $newarray : false;
	}
}

/**
* @package phpBB3
* connector
*/
class cjp_standard_connector
{
	var $active_socket;

	function open_socket($server, $port)
	{
		if ($this->active_socket = @fsockopen($server, $port, $err, $err2, 5))
		{
			@socket_set_blocking($this->active_socket, 0);
			@socket_set_timeout($this->active_socket, 31536000);

			return true;
		}
		else
		{
			return false;
		}
	}

	function close_socket()
	{
		return @fclose($this->active_socket);
	}

	function write_to_socket($data)
	{
		return @fwrite($this->active_socket, $data);
	}

	function read_from_socket($chunksize)
	{
		$buffer = stripslashes(@fread($this->active_socket, $chunksize));
		//@set_magic_quotes_runtime(get_magic_quotes_gpc());

		return $buffer;
	}
}

?>