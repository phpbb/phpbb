<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*
*	Class.Jabber.PHP v0.4.2
*	(c) 2004 Nathan "Fritzy" Fritz
*	http://cjphp.netflint.net *** fritzy@netflint.net
*
*	This is a bugfix version, specifically for those who can't get 
*	0.4 to work on Jabberd2 servers. 
*
*	last modified: 24.03.2004 13:01:53 
*
*	Modified by phpBB Development Team
*	version: v0.4.3
*
* @package phpBB3
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

	var $enable_logging;
	var $log_array;

	var $iq_sleep_timer;
	var $last_ping_time;

	var $packet_queue;

	var $iq_version_name;
	var $iq_version_os;
	var $iq_version_version;

	var $error_codes;

	var $connected;
	var $keep_alive_id;
	var $returned_keep_alive;
	var $txnid;

	var $connector;

	var $version;
	var $show_version;

	/**
	* Constructor
	*/
	function jabber($server, $port, $username, $password, $resource)
	{
		$this->server				= ($server) ? $server : 'localhost';
		$this->port					= ($port) ? $port : '5222';
		$this->username				= $username;
		$this->password				= $password;
		$this->resource				= ($resource) ? $resource : NULL;

		$this->enable_logging		= true;
		$this->log_array			= array();

		$this->version				= '1.0';
		$this->show_version			= false;

		$this->packet_queue			= array();
		$this->iq_sleep_timer		= $this->delay_disconnect = 1;

		$this->returned_keep_alive	= true;
		$this->txnid				= 0;

		$this->iq_version_name		= "Class.Jabber.PHP -- http://cjphp.netflint.net -- by Nathan 'Fritzy' Fritz, fritz@netflint.net";
		$this->iq_version_version	= '0.4';
		$this->iq_version_os		= $_SERVER['SERVER_SOFTWARE'];

		$this->error_codes			= array(
			400 => 'Bad Request',
			401 => 'Unauthorised',
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

	/**
	* Connect
	*/
	function connect()
	{
		$this->connector = new cjp_standard_connector;

		if ($this->connector->open_socket($this->server, $this->port))
		{
			$this->send_packet("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
			$this->send_packet("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams'" . (($this->show_version) ? " version='{$this->version}'" : '') . ">\n");

			sleep(2);

			if ($this->_check_connected())
			{
				$this->connected = true; // Nathan Fritz
				return true;
			}
			else
			{
				$this->add_to_log('ERROR: connect() #1');
				return false;
			}
		}
		else
		{
			$this->add_to_log('ERROR: connect() #2');
			return false;
		}
	}

	/**
	* Disconnect
	*/
	function disconnect()
	{
		if (is_int($this->delay_disconnect))
		{
			sleep($this->delay_disconnect);
		}

		$this->send_packet('</stream:stream>');
		$this->connector->close_socket();
	}

	/**
	* Send authentication request
	*/
	function send_auth()
	{
		$this->auth_id	= 'auth_' . md5(time() . $_SERVER['REMOTE_ADDR']);
		$this->resource	= ($this->resource != NULL) ? $this->resource : ('Class.Jabber.PHP ' . md5($this->auth_id));
		$this->jid		= "{$this->username}@{$this->server}/{$this->resource}";

		// request available authentication methods
		$payload	= "<username>{$this->username}</username>";
		$packet		= $this->send_iq(NULL, 'get', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		if ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id)
		{
			// yes, now check for auth method availability in descending order (best to worst)
			if (isset($packet['iq']['#']['query'][0]['#']['sequence'][0]['#']) && isset($packet['iq']['#']['query'][0]['#']['token'][0]['#']))
			{
				// auth_0k
				return $this->_sendauth_ok($packet['iq']['#']['query'][0]['#']['token'][0]['#'], $packet['iq']['#']['query'][0]['#']['sequence'][0]['#']);
			}
			else if (isset($packet['iq']['#']['query'][0]['#']['digest']))
			{
				// digest
				return $this->_sendauth_digest();
			}
			else if ($packet['iq']['#']['query'][0]['#']['password'])
			{
				// plain text
				return $this->_sendauth_plaintext();
			}
			else
			{
				$this->add_to_log('ERROR: send_auth() #2 - No auth method available!');
				return false;
			}
		}
		else
		{
			// no result returned
			$this->add_to_log('ERROR: send_auth() #1');
			return false;
		}
	}

	/**
	* Register account
	*/
	function account_registration($reg_email = NULL, $reg_name = NULL)
	{
		$packet = $this->send_iq($this->server, 'get', 'reg_01', 'jabber:iq:register');

		if ($packet)
		{
			// just in case a key was passed back from the server
			$key = $this->get_info_from_iq_key($packet);
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
			else if ($this->get_info_from_iq_type($packet) == 'error' && isset($packet['iq']['#']['error'][0]['#']))
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

	/**
	* Change password
	*/
	function change_password($new_password)
	{
		$packet = $this->send_iq($this->server, 'get', 'A0', 'jabber:iq:register');

		if ($packet)
		{
			// just in case a key was passed back from the server
			$key = $this->get_info_from_iq_key($packet);
			unset($packet);

			$payload = "<username>{$this->username}</username>
						<password>{$new_password}</password>\n";
			$payload .= ($key) ? "<key>$key</key>\n" : '';

			$packet = $this->send_iq($this->server, 'set', 'A0', 'jabber:iq:register', $payload);

			if ($this->get_info_from_iq_type($packet) == 'result')
			{
				$return_code = (isset($packet['iq']['#']['query'][0]['#']['registered'][0]['#'])) ? 1 : 2;
			}
			else if ($this->get_info_from_iq_type($packet) == 'error' && isset($packet['iq']['#']['error'][0]['#']))
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

	/**
	* Send packet
	*/
	function send_packet($xml)
	{
		$xml = trim($xml);

		if ($this->connector->write_to_socket($xml))
		{
			$this->add_to_log('SEND: ' . $xml);
			return true;
		}
		else
		{
			$this->add_to_log('ERROR: send_packet() #1');
			return false;
		}
	}

	/**
	* Listen to socket
	*/
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
			$this->add_to_log('RECV: ' . $incoming);
			$temp = $this->_split_incoming($incoming);

			for ($i = 0, $size = sizeof($temp); $i < $size; $i++)
			{
				$this->packet_queue[] = $this->xmlize($temp[$i]);
			}
		}

		return true;
	}

	/**
	* Strip jid
	*/
	function strip_jid($jid = NULL)
	{
		preg_match('#(.*)\/(.*)#Ui', $jid, $temp);
		return ($temp[1] != '') ? $temp[1] : $jid;
	}

	/**
	* Send a message
	*/
	function send_message($to, $type = 'normal', $id = NULL, $content = NULL, $payload = NULL)
	{
		if ($to && is_array($content))
		{
			if (!$id)
			{
				$id = $type . '_' . time();
			}

			$this->_array_xmlspecialchars($content);

			$xml = "<message to='$to' type='$type' id='$id'>\n";

			if (!empty($content['subject']))
			{
				$xml .= '<subject>' . $content['subject'] . "</subject>\n";
			}

			if (!empty($content['thread']))
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
				$this->add_to_log('ERROR: send_message() #1');
			}
		}
		else
		{
			$this->add_to_log('ERROR: send_message() #2');
			return false;
		}
	}

	/**
	* Send presence
	*/
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

		if ($this->send_packet($xml))
		{
			return true;
		}
		else
		{
			$this->add_to_log('ERROR: send_presence() #1');
			return false;
		}
	}

	/**
	* Send error
	*/
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

	/**
	* Get first from queue
	*/
	function get_first_from_queue()
	{
		return array_shift($this->packet_queue);
	}

	/**
	* Get from queue by id
	*/
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

	/**
	* Call handler
	*/
	function call_handler($packet = NULL)
	{
		$packet_type = $this->_get_packet_type($packet);

		if ($packet_type == 'message')
		{
			$type		= $packet['message']['@']['type'];
			$type		= ($type != '') ? $type : 'normal';
			$funcmeth	= "handler_message_$type";
		}
		else if ($packet_type == 'iq')
		{
			$namespace	= $packet['iq']['#']['query'][0]['@']['xmlns'];
			$namespace	= str_replace(':', '_', $namespace);
			$funcmeth	= "handler_iq_$namespace";
		}
		else if ($packet_type == 'presence')
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
			else if (method_exists($this, $funcmeth))
			{
				call_user_func(array(&$this, $funcmeth), $packet);
			}
			else
			{
				$this->handler_not_implemented($packet);
				$this->add_to_log("ERROR: call_handler() #1 - neither method nor function $funcmeth() available");
			}
		}
	}

	/**
	* Cruise Control
	*/
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

			if (($this->last_ping_time + 180) < time())
			{
				// Modified by Nathan Fritz
				if ($this->returned_keep_alive == false)
				{
					$this->connected = false;
					$this->add_to_log('EVENT: Disconnected');
				}

				if ($this->returned_keep_alive == true)
				{
					$this->connected = true;
				}

				$this->returned_keep_alive = false;

				$this->keep_alive_id = 'keep_alive_' . time();
				// $this->send_packet("<iq id='{$this->keep_alive_id}'/>", 'cruise_control');
				$this->send_packet("<iq type='get' from='{$this->username}@{$this->server}/{$this->resource}' to='{$this->server}' id='{$this->keep_alive_id}'><query xmlns='jabber:iq:time' /></iq>");
				$this->last_ping_time = time();
			}
		}

		return true;
	}

	/**
	* Send iq
	*/
	function send_iq($to = NULL, $type = 'get', $id = NULL, $xmlns = NULL, $payload = NULL, $from = NULL)
	{
		if (!preg_match('#^(get|set|result|error)$#', $type))
		{
			unset($type);

			$this->add_to_log("ERROR: send_iq() #2 - type must be 'get', 'set', 'result' or 'error'");
			return false;
		}
		else if ($id && $xmlns)
		{
			$xml = "<iq type='$type' id='$id'";
			$xml .= ($to) ? " to='" . htmlspecialchars($to) . "'" : '';
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
			$this->add_to_log('ERROR: send_iq() #1 - to, id and xmlns are mandatory');
			return false;
		}
	}

	/**
	* get the transport registration fields
	* method written by Steve Blinch, http://www.blitzaffe.com 
	*/
	function transport_registration_details($transport)
	{
		$this->txnid++;
		$packet = $this->send_iq($transport, 'get', "reg_{$this->txnid}", 'jabber:iq:register', NULL, $this->jid);

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

	/**
	* register with the transport
	* method written by Steve Blinch, http://www.blitzaffe.com 
	*/
	function transport_registration($transport, $details)
	{
		$this->txnid++;
		$packet = $this->send_iq($transport, 'get', "reg_{$this->txnid}", 'jabber:iq:register', NULL, $this->jid);

		if ($packet)
		{
			// just in case a key was passed back from the server
			$key = $this->get_info_from_iq_key($packet);
			unset($packet);

			$payload = ($key) ? "<key>$key</key>\n" : '';
			foreach ($details as $element => $value)
			{
				$payload .= "<$element>$value</$element>\n";
			}

			$packet = $this->send_iq($transport, 'set', "reg_{$this->txnid}", 'jabber:iq:register', $payload);

			if ($this->get_info_from_iq_type($packet) == 'result')
			{
				$return_code = (isset($packet['iq']['#']['query'][0]['#']['registered'][0]['#'])) ? 1 : 2;
			}
			else if ($this->get_info_from_iq_type($packet) == 'error')
			{
				if (isset($packet['iq']['#']['error'][0]['#']))
				{
					$return_code = 'Error ' . $packet['iq']['#']['error'][0]['@']['code'] . ': ' . $packet['iq']['#']['error'][0]['#'];
					$this->add_to_log('ERROR: transport_registration()');
				}
			}

			return $return_code;
		}
		else
		{
			return 3;
		}
	}

	/**
	* Return log
	*/
	function get_log()
	{
		if ($this->enable_logging && sizeof($this->log_array))
		{
			return implode("<br /><br />", $this->log_array);
		}

		return '';
	}

	/**
	* Add information to log
	*/
	function add_to_log($string)
	{
		if ($this->enable_logging)
		{
			$this->log_array[] = htmlspecialchars($string);
		}
	}


	// ======================================================================
	// private methods
	// ======================================================================

	/**
	* Send auth
	* @access private
	*/
	function _sendauth_ok($zerok_token, $zerok_sequence)
	{
		// initial hash of password
		$zerok_hash = sha1($this->password);

		// sequence 0: hash of hashed-password and token
		$zerok_hash = sha1($zerok_hash . $zerok_token);

		// repeat as often as needed
		for ($i = 0; $i < $zerok_sequence; $i++)
		{
			$zerok_hash = sha1($zerok_hash);
		}

		$payload = "<username>{$this->username}</username>
					<hash>$zerok_hash</hash>
					<resource>{$this->resource}</resource>";

		$packet = $this->send_iq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		if ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id)
		{
			return true;
		}
		else
		{
			$this->add_to_log('ERROR: _sendauth_ok() #1');
			return false;
		}
	}

	/**
	* Send auth digest
	* @access private
	*/
	function _sendauth_digest()
	{
		$payload = "<username>{$this->username}</username>
					<resource>{$this->resource}</resource>
					<digest>" . sha1($this->stream_id . $this->password) . "</digest>";

		$packet = $this->send_iq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		if ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id)
		{
			return true;
		}
		else
		{
			$this->add_to_log('ERROR: _sendauth_digest() #1');
			return false;
		}
	}

	/**
	* Send auth plain
	* @access private
	*/
	function _sendauth_plaintext()
	{
		$payload = "<username>{$this->username}</username>
					<password>{$this->password}</password>
					<resource>{$this->resource}</resource>";

		$packet = $this->send_iq(NULL, 'set', $this->auth_id, 'jabber:iq:auth', $payload);

		// was a result returned?
		if ($this->get_info_from_iq_type($packet) == 'result' && $this->get_info_from_iq_id($packet) == $this->auth_id)
		{
			return true;
		}
		else
		{
			$this->add_to_log('ERROR: _sendauth_plaintext() #1');
			return false;
		}
	}

	/**
	* Listen on socket
	* @access private
	*/
	function _listen_incoming()
	{
		$incoming = '';
		
		while ($line = $this->connector->read_from_socket(4096))
		{
			$incoming .= $line;
		}

		$incoming = trim($incoming);

		if ($incoming != '')
		{
			$this->add_to_log('RECV: ' . $incoming);
		}

		return $this->xmlize($incoming);
	}

	/**
	* Check if connected
	* @access private
	*/
	function _check_connected($in_tls = false)
	{
		$incoming_array = $this->_listen_incoming();

		if (is_array($incoming_array))
		{
			if ($incoming_array['stream:stream']['@']['from'] == $this->server && $incoming_array['stream:stream']['@']['xmlns'] == 'jabber:client' && $incoming_array['stream:stream']['@']['xmlns:stream'] == 'http://etherx.jabber.org/streams')
			{
				$this->stream_id = $incoming_array['stream:stream']['@']['id'];

				// We only start TLS authentication if not called within TLS authentication itself, which may produce a never ending loop...
				if (!$in_tls)
				{
					if (!empty($incoming_array['stream:stream']['#']['stream:features'][0]['#']['starttls'][0]['@']['xmlns']) && $incoming_array['stream:stream']['#']['stream:features'][0]['#']['starttls'][0]['@']['xmlns'] == 'urn:ietf:params:xml:ns:xmpp-tls')
					{
						return $this->_starttls();
					}
				}

				return true;
			}
			else
			{
				$this->add_to_log('ERROR: _check_connected() #1');
				return false;
			}
		}
		else
		{
			$this->add_to_log('ERROR: _check_connected() #2');
			return false;
		}
	}

	/**
	* Start TLS/SSL session if supported (PHP5.1)
	* @access private
	*/
	function _starttls()
	{
		if (!function_exists('stream_socket_enable_crypto') || !function_exists('stream_get_meta_data') || !function_exists('socket_set_blocking') || !function_exists('stream_get_wrappers'))
		{
			$this->add_to_log('WARNING: TLS is not available');
			return true;
		}

		// Make sure the encryption stream is supported
		$streams = stream_get_wrappers();

		if (!in_array('streams.crypto', $streams))
		{
			$this->add_to_log('WARNING: SSL/crypto stream not supported');
			return true;
		}

		$this->send_packet("<starttls xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>\n");
		sleep(2);
		$incoming_array = $this->_listen_incoming();

		if (!is_array($incoming_array))
		{
			$this->add_to_log('ERROR: _starttls() #1');
			return false;
		}

		if ($incoming_array['proceed']['@']['xmlns'] != 'urn:ietf:params:xml:ns:xmpp-tls')
		{
			$this->add_to_log('ERROR: _starttls() #2');
			return false;
		}

		$meta = stream_get_meta_data($this->connector->active_socket);
		socket_set_blocking($this->connector->active_socket, 1);

		$result = @stream_socket_enable_crypto($this->connector->active_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
		if (!$result)
		{
			socket_set_blocking($this->connector->active_socket, $meta['blocked']);
			$this->add_to_log('ERROR: _starttls() #3');
			return false;
		}

		socket_set_blocking($this->connector->active_socket, $meta['blocked']);

		$this->send_packet("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
		$this->send_packet("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams'" . (($this->show_version) ? " version='{$this->version}'" : '') . ">\n");
		sleep(2);

		if (!$this->_check_connected(true))
		{
			$this->add_to_log('ERROR: _starttls() #4');
			return false;
		}

		return true;
	}

	/**
	* Get packet type
	* @access private
	*/
	function _get_packet_type($packet = NULL)
	{
		if (is_array($packet))
		{
			reset($packet);
			$packet_type = key($packet);
		}

		return ($packet_type) ? $packet_type : false;
	}

	/**
	* Split incoming packet
	* @access private
	*/
	function _split_incoming($incoming)
	{
		$temp = preg_split('#<(message|iq|presence|stream)#', $incoming, -1, PREG_SPLIT_DELIM_CAPTURE);
		$array = array();

		for ($i = 1, $size = sizeof($temp); $i < $size; $i += 2)
		{
			$array[] = '<' . $temp[$i] . $temp[($i + 1)];
		}

		return $array;
	}

	/**
	* Recursively prepares the strings in an array to be used in XML data.
	* @access private
	*/
	function _array_xmlspecialchars(&$array)
	{
		if (is_array($array))
		{
			foreach ($array as $k => $v)
			{
				if (is_array($v))
				{
					$this->_array_xmlspecialchars($array[$k]);
				}
				else
				{
					$this->_xmlspecialchars($array[$k]);
				}
			}
		}
	}

	/**
	* Prepares a string for usage in XML data.
	* @access private
	*/
	function _xmlspecialchars(&$string)
	{
		// we only have a few entities in xml
		$string = str_replace(array('&', '>', '<', '"', '\''), array('&amp;', '&gt;', '&lt;', '&quot;', '&apos;'), $string);
	}

	// ======================================================================
	// <message/> parsers
	// ======================================================================

	/**
	* Get info from message (from)
	*/
	function get_info_from_message_from($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['from'] : false;
	}

	/**
	* Get info from message (type)
	*/
	function get_info_from_message_type($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['type'] : false;
	}

	/**
	* Get info from message (id)
	*/
	function get_info_from_message_id($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['@']['id'] : false;
	}

	/**
	* Get info from message (thread)
	*/
	function get_info_from_message_thread($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['thread'][0]['#'] : false;
	}

	/**
	* Get info from message (subject)
	*/
	function get_info_from_message_subject($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['subject'][0]['#'] : false;
	}

	/**
	* Get info from message (body)
	*/
	function get_info_from_message_body($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['body'][0]['#'] : false;
	}

	/**
	* Get info from message (xmlns)
	*/
	function get_info_from_message_xmlns($packet = NULL)
	{
		return (is_array($packet)) ? $packet['message']['#']['x'] : false;
	}

	/**
	* Get info from message (error)
	*/
	function get_info_from_message_error($packet = NULL)
	{
		$error = preg_replace('#^\/$#', '', ($packet['message']['#']['error'][0]['@']['code'] . '/' . $packet['message']['#']['error'][0]['#']));
		return (is_array($packet)) ? $error : false;
	}

	// ======================================================================
	// <iq/> parsers
	// ======================================================================

	/**
	* Get info from iq (from)
	*/
	function get_info_from_iq_from($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['from'] : false;
	}

	/**
	* Get info from iq (type)
	*/
	function get_info_from_iq_type($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['type'] : false;
	}

	/**
	* Get info from iq (id)
	*/
	function get_info_from_iq_id($packet = NULL)
	{
		return (is_array($packet)) ? $packet['iq']['@']['id'] : false;
	}

	/**
	* Get info from iq (key)
	*/
	function get_info_from_iq_key($packet = NULL)
	{
		return (is_array($packet) && isset($packet['iq']['#']['query'][0]['#']['key'][0]['#'])) ? $packet['iq']['#']['query'][0]['#']['key'][0]['#'] : false;
	}

	/**
	* Get info from iq (error)
	*/
	function get_info_from_iq_error($packet = NULL)
	{
		$error = preg_replace('#^\/$#', '', ($packet['iq']['#']['error'][0]['@']['code'] . '/' . $packet['iq']['#']['error'][0]['#']));
		return (is_array($packet)) ? $error : false;
	}

	// ======================================================================
	// <message/> handlers
	// ======================================================================

	/**
	* Message type normal
	*/
	function handler_message_normal($packet)
	{
		$from = $packet['message']['@']['from'];
		$this->add_to_log("EVENT: Message (type normal) from $from");
	}

	/**
	* Message type chat
	*/
	function handler_message_chat($packet)
	{
		$from = $packet['message']['@']['from'];
		$this->add_to_log("EVENT: Message (type chat) from $from");
	}

	/**
	* Message type groupchat
	*/
	function handler_message_groupchat($packet)
	{
		$from = $packet['message']['@']['from'];
		$this->add_to_log("EVENT: Message (type groupchat) from $from");
	}

	/**
	* Message type headline
	*/
	function handler_message_headline($packet)
	{
		$from = $packet['message']['@']['from'];
		$this->add_to_log("EVENT: Message (type headline) from $from");
	}

	/**
	* Message type error
	*/
	function handler_message_error($packet)
	{
		$from = $packet['message']['@']['from'];
		$this->add_to_log("EVENT: Message (type error) from $from");
	}

	// ======================================================================
	// <iq/> handlers
	// ======================================================================

	/**
	* application version updates
	*/
	function handler_iq_jabber_iq_autoupdate($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:autoupdate from $from");
	}

	/**
	* interactive server component properties
	*/
	function handler_iq_jabber_iq_agent($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:agent from $from");
	}

	/**
	* method to query interactive server components
	*/
	function handler_iq_jabber_iq_agents($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:agents from $from");
	}

	/**
	* simple client authentication
	*/
	function handler_iq_jabber_iq_auth($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:auth from $from");
	}

	/**
	* out of band data
	*/
	function handler_iq_jabber_iq_oob($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:oob from $from");
	}

	/**
	* method to store private data on the server
	*/
	function handler_iq_jabber_iq_private($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:private from $from");
	}

	/**
	* method for interactive registration
	*/
	function handler_iq_jabber_iq_register($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:register from $from");
	}

	/**
	* client roster management
	*/
	function handler_iq_jabber_iq_roster($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:roster from $from");
	}

	/**
	* method for searching a user database
	*/
	function handler_iq_jabber_iq_search($packet)
	{
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: jabber:iq:search from $from");
	}

	/**
	* method for requesting the current time
	*/
	function handler_iq_jabber_iq_time($packet)
	{
		if ($this->keep_alive_id == $this->get_info_from_iq_id($packet))
		{
			$this->returned_keep_alive = true;
			$this->connected = true;

			$this->add_to_log('EVENT: Keep-Alive returned, connection alive.');
		}

		$type	= $this->get_info_from_iq_type($packet);
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);
		$id		= ($id != '') ? $id : 'time_' . time();

		if ($type == 'get')
		{
			$payload = '<utc>' . gmdate("Ydm\TH:i:s") . '</utc><tz>' . date('T') . '</tz><display>' . date("Y/d/m h:i:s A") . '</display>';
			$this->send_iq($from, 'result', $id, 'jabber:iq:time', $payload);
		}

		$this->add_to_log("EVENT: jabber:iq:time (type $type) from $from");
	}

	/**
	*/
	function handler_iq_error($packet)
	{
		// We'll do something with these later. This is a placeholder so that errors don't bounce back and forth.
	}

	/**
	* method for requesting version
	*/
	function handler_iq_jabber_iq_version($packet)
	{
		$type	= $this->get_info_from_iq_type($packet);
		$from	= $this->get_info_from_iq_from($packet);
		$id		= $this->get_info_from_iq_id($packet);
		$id		= ($id != '') ? $id : 'version_' . time();

		if ($type == 'get')
		{
			$payload = "<name>{$this->iq_version_name}</name>
				<os>{$this->iq_version_os}</os>
				<version>{$this->iq_version_version}</version>";

			//$this->SendIq($from, 'result', $id, "jabber:iq:version", $payload);
		}

		$this->add_to_log("EVENT: jabber:iq:version (type $type) from $from -- DISABLED");
	}

	// ======================================================================
	// Generic handlers
	// ======================================================================

	/**
	* Generic handler for unsupported requests
	*/
	function handler_not_implemented($packet)
	{
		$packet_type	= $this->_get_packet_type($packet);
		$from			= call_user_func(array(&$this, 'get_info_from_' . strtolower($packet_type) . '_from'), $packet);
		$id				= call_user_func(array(&$this, 'get_info_from_' . strtolower($packet_type) . '_id'), $packet);

		$this->send_error($from, $id, 501);
		$this->add_to_log("EVENT: Unrecognized <$packet_type/> from $from");
	}

	// ======================================================================
	// Third party code
	// m@d pr0ps to the coders ;)
	// ======================================================================

	/**
	* xmlize()
	* @author Hans Anderson
	* @copyright Hans Anderson / http://www.hansanderson.com/php/xml/
	*/
	function xmlize($data, $skip_white = 1, $encoding = 'UTF-8')
	{
		$data = trim($data);

		$vals = $index = $array = array();
		$parser = xml_parser_create($encoding);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, $skip_white);
		xml_parse_into_struct($parser, $data, $vals, $index);
		xml_parser_free($parser);

		$i = 0;
		$tagname = $vals[$i]['tag'];

		$array[$tagname]['@'] = (isset($vals[$i]['attributes'])) ? $vals[$i]['attributes'] : array();
		$array[$tagname]['#'] = $this->_xml_depth($vals, $i);

		return $array;
	}

	/**
	* _xml_depth()
	* @author Hans Anderson
	* @copyright Hans Anderson / http://www.hansanderson.com/php/xml/
	*/
	function _xml_depth($vals, &$i)
	{
		$children = array();

		if (isset($vals[$i]['value']))
		{
			array_push($children, $vals[$i]['value']);
		}

		while (++$i < sizeof($vals))
		{
			switch ($vals[$i]['type'])
			{
				case 'open':

					$tagname = (isset($vals[$i]['tag'])) ? $vals[$i]['tag'] : '';
					$size = (isset($children[$tagname])) ? sizeof($children[$tagname]) : 0;

					if (isset($vals[$i]['attributes']))
					{
						$children[$tagname][$size]['@'] = $vals[$i]['attributes'];
					}

					$children[$tagname][$size]['#'] = $this->_xml_depth($vals, $i);

				break;

				case 'cdata':
					array_push($children, $vals[$i]['value']);
				break;

				case 'complete':

					$tagname = $vals[$i]['tag'];
					$size = (isset($children[$tagname])) ? sizeof($children[$tagname]) : 0;
					$children[$tagname][$size]['#'] = (isset($vals[$i]['value'])) ? $vals[$i]['value'] : array();

					if (isset($vals[$i]['attributes']))
					{
						$children[$tagname][$size]['@'] = $vals[$i]['attributes'];
					}

				break;

				case 'close':
					return $children;
				break;
			}
		}

		return $children;
	}

	/**
	* TraverseXMLize()
	* @author acebone@f2s.com
	* @copyright acebone@f2s.com, a HUGE help!
	*/
	function traverse_xmlize($array, $arr_name = 'array', $level = 0)
	{
		if ($level == 0)
		{
			echo '<pre>';
		}

		foreach ($array as $key => $val)
		{
			if (is_array($val))
			{
				$this->traverse_xmlize($val, $arr_name . '[' . $key . ']', $level + 1);
			}
			else
			{
				$GLOBALS['traverse_array'][] = '$' . $arr_name . '[' . $key . '] = "' . $val . "\"\n";
			}
		}

		if ($level == 0)
		{
			echo '</pre>';
		}

		return 1;
	}
}

/**
* Jabber Connector
* @package phpBB3
*/
class cjp_standard_connector
{
	var $active_socket;

	/**
	* Open socket
	*/
	function open_socket($server, $port)
	{
		if (function_exists('dns_get_record'))
		{
			$record = dns_get_record("_xmpp-client._tcp.$server", DNS_SRV);

			if (!empty($record))
			{
				$server = $record[0]['target'];
				$port = $record[0]['port'];
			}
		}

		$errno = 0;
		$errstr = '';

		if ($this->active_socket = @fsockopen($server, $port, $errno, $errstr, 5))
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

	/**
	* Close socket
	*/
	function close_socket()
	{
		return @fclose($this->active_socket);
	}

	/**
	* Write to socket
	*/
	function write_to_socket($data)
	{
		return @fwrite($this->active_socket, $data);
	}

	/**
	* Read from socket
	*/
	function read_from_socket($chunksize)
	{
		$buffer = @fread($this->active_socket, $chunksize);
		$buffer = (STRIP) ? stripslashes($buffer) : $buffer;

		return $buffer;
	}
}

?>