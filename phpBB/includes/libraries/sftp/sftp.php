<?php
/**
*
* @package sftp
* @version $Id$
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Code from http://phpseclib.sourceforge.net/
*
* Modified by phpBB Group to meet our coding standards
* and being able to integrate into phpBB
*
* Pure-PHP implementation of ssh2
*
* Copyright 2007-2009 TerraFrost <terrafrost@php.net>
* Copyright 2009+ phpBB
*
* @package sftp
* @author  TerraFrost <terrafrost@php.net>
*/

include('biginteger.' . PHP_EXT);
include('random.' . PHP_EXT);
include('hash.' . PHP_EXT);
include('rc4.' . PHP_EXT);
include('aes.' . PHP_EXT);
include('des.' . PHP_EXT);

/**#@+
 * Execution Bitmap Masks
 *
 * @see ssh2::bitmap
 * @access private
 */
define('NET_SSH2_MASK_CONSTRUCTOR', 0x00000001);
define('NET_SSH2_MASK_LOGIN', 0x00000002);
/**#@-*/

/**#@+
 * @access public
 * @see ssh2::getLog()
 */
/**
 * Returns the message numbers
 */
define('NET_SSH2_LOG_SIMPLE',  1);
/**
 * Returns the message content
 */
define('NET_SSH2_LOG_COMPLEX', 2);
/**#@-*/

/**#@+
 * @access public
 * @see ssh2_sftp::get_log()
 */
/**
 * Returns the message numbers
 */
define('NET_SFTP_LOG_SIMPLE',  NET_SSH2_LOG_SIMPLE);
/**
 * Returns the message content
 */
define('NET_SFTP_LOG_COMPLEX', NET_SSH2_LOG_COMPLEX);
/**#@-*/

/**
 * Pure-PHP implementation of SSHv2.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.1.0
 * @access  public
 * @package ssh2
 */
class ssh2
{
	/**
	 * The SSH identifier
	 *
	 * @var String
	 * @access private
	 */
	var $identifier = 'SSH-2.0-phpseclib_0.1';

	/**
	 * The Socket Object
	 *
	 * @var Object
	 * @access private
	 */
	var $fsock;

	/**
	 * Execution Bitmap
	 *
	 * The bits that are set reprsent functions that have been called already.  This is used to determine
	 * if a requisite function has been successfully executed.  If not, an error should be thrown.
	 *
	 * @var Integer
	 * @access private
	 */
	var $bitmap = 0;

	/**
	 * Debug Info
	 *
	 * @see ssh2::get_debug_info()
	 * @var String
	 * @access private
	 */
	var $debug_info = '';

	/**
	 * Server Identifier
	 *
	 * @see ssh2::get_server_identification()
	 * @var String
	 * @access private
	 */
	var $server_identifier = '';

	/**
	 * Key Exchange Algorithms
	 *
	 * @see ssh2::get_kex_algorithims()
	 * @var Array
	 * @access private
	 */
	var $kex_algorithms;

	/**
	 * Server Host Key Algorithms
	 *
	 * @see ssh2::get_server_host_key_algorithms()
	 * @var Array
	 * @access private
	 */
	var $server_host_key_algorithms;

	/**
	 * Encryption Algorithms: Client to Server
	 *
	 * @see ssh2::get_encryption_algorithms_client_to_server()
	 * @var Array
	 * @access private
	 */
	var $encryption_algorithms_client_to_server;

	/**
	 * Encryption Algorithms: Server to Client
	 *
	 * @see ssh2::get_encryption_algorithms_server_to_client()
	 * @var Array
	 * @access private
	 */
	var $encryption_algorithms_server_to_client;

	/**
	 * MAC Algorithms: Client to Server
	 *
	 * @see ssh2::get_mac_algorithms_client_to_server()
	 * @var Array
	 * @access private
	 */
	var $mac_algorithms_client_to_server;

	/**
	 * MAC Algorithms: Server to Client
	 *
	 * @see ssh2::get_mac_algorithms_server_to_client()
	 * @var Array
	 * @access private
	 */
	var $mac_algorithms_server_to_client;

	/**
	 * Compression Algorithms: Client to Server
	 *
	 * @see ssh2::get_compression_algorithms_client_to_server()
	 * @var Array
	 * @access private
	 */
	var $compression_algorithms_client_to_server;

	/**
	 * Compression Algorithms: Server to Client
	 *
	 * @see ssh2::get_compression_algorithms_server_to_client()
	 * @var Array
	 * @access private
	 */
	var $compression_algorithms_server_to_client;

	/**
	 * Languages: Server to Client
	 *
	 * @see ssh2::get_languages_server_to_client()
	 * @var Array
	 * @access private
	 */
	var $languages_server_to_client;

	/**
	 * Languages: Client to Server
	 *
	 * @see ssh2::get_languages_client_to_server()
	 * @var Array
	 * @access private
	 */
	var $languages_client_to_server;

	/**
	 * Block Size for Server to Client Encryption
	 *
	 * "Note that the length of the concatenation of 'packet_length',
	 *  'padding_length', 'payload', and 'random padding' MUST be a multiple
	 *  of the cipher block size or 8, whichever is larger.  This constraint
	 *  MUST be enforced, even when using stream ciphers."
	 *
	 *  -- http://tools.ietf.org/html/rfc4253#section-6
	 *
	 * @see ssh2::__constructor()
	 * @see ssh2::_send_binary_packet()
	 * @var Integer
	 * @access private
	 */
	var $encrypt_block_size = 8;

	/**
	 * Block Size for Client to Server Encryption
	 *
	 * @see ssh2::ssh2()
	 * @see ssh2::_get_binary_packet()
	 * @var Integer
	 * @access private
	 */
	var $decrypt_block_size = 8;

	/**
	 * Server to Client Encryption Object
	 *
	 * @see ssh2::_get_binary_packet()
	 * @var Object
	 * @access private
	 */
	var $decrypt = false;

	/**
	 * Client to Server Encryption Object
	 *
	 * @see ssh2::_send_binary_packet()
	 * @var Object
	 * @access private
	 */
	var $encrypt = false;

	/**
	 * Client to Server HMAC Object
	 *
	 * @see ssh2::_send_binary_packet()
	 * @var Object
	 * @access private
	 */
	var $hmac_create = false;

	/**
	 * Server to Client HMAC Object
	 *
	 * @see ssh2::_get_binary_packet()
	 * @var Object
	 * @access private
	 */
	var $hmac_check = false;

	/**
	 * Size of server to client HMAC
	 *
	 * We need to know how big the HMAC will be for the server to client direction so that we know how many bytes to read.
	 * For the client to server side, the HMAC object will make the HMAC as long as it needs to be.  All we need to do is
	 * append it.
	 *
	 * @see ssh2::_get_binary_packet()
	 * @var Integer
	 * @access private
	 */
	var $hmac_size = false;

	/**
	 * Server Public Host Key
	 *
	 * @see ssh2::getServerPublicHostKey()
	 * @var String
	 * @access private
	 */
	var $server_public_host_key;

	/**
	 * Session identifer
	 *
	 * "The exchange hash H from the first key exchange is additionally
	 *  used as the session identifier, which is a unique identifier for
	 *  this connection."
	 *
	 *  -- http://tools.ietf.org/html/rfc4253#section-7.2
	 *
	 * @see ssh2::_key_exchange()
	 * @var String
	 * @access private
	 */
	var $session_id = false;

	/**
	 * Message Numbers
	 *
	 * @see ssh2::ssh2()
	 * @var Array
	 * @access private
	 */
	var $message_numbers = array();

	/**
	 * Disconnection Message 'reason codes' defined in RFC4253
	 *
	 * @see ssh2::ssh2()
	 * @var Array
	 * @access private
	 */
	var $disconnect_reasons = array();

	/**
	 * SSH_MSG_CHANNEL_OPEN_FAILURE 'reason codes', defined in RFC4254
	 *
	 * @see ssh2::ssh2()
	 * @var Array
	 * @access private
	 */
	var $channel_open_failure_reasons = array();

	/**
	 * Terminal Modes
	 *
	 * @link http://tools.ietf.org/html/rfc4254#section-8
	 * @see ssh2::ssh2()
	 * @var Array
	 * @access private
	 */
	var $terminal_modes = array();

	/**
	 * SSH_MSG_CHANNEL_EXTENDED_DATA's data_type_codes
	 *
	 * @link http://tools.ietf.org/html/rfc4254#section-5.2
	 * @see ssh2::ssh2()
	 * @var Array
	 * @access private
	 */
	var $channel_extended_data_type_codes = array();

	/**
	 * Send Sequence Number
	 *
	 * See 'Section 6.4.  Data Integrity' of rfc4253 for more info.
	 *
	 * @see ssh2::_send_binary_packet()
	 * @var Integer
	 * @access private
	 */
	var $send_seq_no = 0;

	/**
	 * Get Sequence Number
	 *
	 * See 'Section 6.4.  Data Integrity' of rfc4253 for more info.
	 *
	 * @see ssh2::_get_binary_packet()
	 * @var Integer
	 * @access private
	 */
	var $get_seq_no = 0;

	/**
	 * Message Number Log
	 *
	 * @see ssh2::getLog()
	 * @var Array
	 * @access private
	 */
	var $message_number_log = array();

	/**
	 * Message Log
	 *
	 * @see ssh2::getLog()
	 * @var Array
	 * @access private
	 */
	var $message_log = array();

	/**
	 * Default Constructor.
	 *
	 * Connects to an SSHv2 server
	 *
	 * @param String $host
	 * @param optional Integer $port
	 * @param optional Integer $timeout
	 * @return ssh2
	 * @access public
	 */
	function __construct($host, $port = 22, $timeout = 10)
	{
		$this->message_numbers = array(
			1 => 'NET_SSH2_MSG_DISCONNECT',
			2 => 'NET_SSH2_MSG_IGNORE',
			3 => 'NET_SSH2_MSG_UNIMPLEMENTED',
			4 => 'NET_SSH2_MSG_DEBUG',
			5 => 'NET_SSH2_MSG_SERVICE_REQUEST',
			6 => 'NET_SSH2_MSG_SERVICE_ACCEPT',
			20 => 'NET_SSH2_MSG_KEXINIT',
			21 => 'NET_SSH2_MSG_NEWKEYS',
			30 => 'NET_SSH2_MSG_KEXDH_INIT',
			31 => 'NET_SSH2_MSG_KEXDH_REPLY',
			50 => 'NET_SSH2_MSG_USERAUTH_REQUEST',
			51 => 'NET_SSH2_MSG_USERAUTH_FAILURE',
			52 => 'NET_SSH2_MSG_USERAUTH_SUCCESS',
			53 => 'NET_SSH2_MSG_USERAUTH_BANNER',
			60 => 'NET_SSH2_MSG_USERAUTH_PASSWD_CHANGEREQ',

			80 => 'NET_SSH2_MSG_GLOBAL_REQUEST',
			81 => 'NET_SSH2_MSG_REQUEST_SUCCESS',
			82 => 'NET_SSH2_MSG_REQUEST_FAILURE',
			90 => 'NET_SSH2_MSG_CHANNEL_OPEN',
			91 => 'NET_SSH2_MSG_CHANNEL_OPEN_CONFIRMATION',
			92 => 'NET_SSH2_MSG_CHANNEL_OPEN_FAILURE',
			93 => 'NET_SSH2_MSG_CHANNEL_WINDOW_ADJUST',
			94 => 'NET_SSH2_MSG_CHANNEL_DATA',
			95 => 'NET_SSH2_MSG_CHANNEL_EXTENDED_DATA',
			96 => 'NET_SSH2_MSG_CHANNEL_EOF',
			97 => 'NET_SSH2_MSG_CHANNEL_CLOSE',
			98 => 'NET_SSH2_MSG_CHANNEL_REQUEST',
			99 => 'NET_SSH2_MSG_CHANNEL_SUCCESS',
			100 => 'NET_SSH2_MSG_CHANNEL_FAILURE'
		);
		$this->disconnect_reasons = array(
			1 => 'NET_SSH2_DISCONNECT_HOST_NOT_ALLOWED_TO_CONNECT',
			2 => 'NET_SSH2_DISCONNECT_PROTOCOL_ERROR',
			3 => 'NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED',
			4 => 'NET_SSH2_DISCONNECT_RESERVED',
			5 => 'NET_SSH2_DISCONNECT_MAC_ERROR',
			6 => 'NET_SSH2_DISCONNECT_COMPRESSION_ERROR',
			7 => 'NET_SSH2_DISCONNECT_SERVICE_NOT_AVAILABLE',
			8 => 'NET_SSH2_DISCONNECT_PROTOCOL_VERSION_NOT_SUPPORTED',
			9 => 'NET_SSH2_DISCONNECT_HOST_KEY_NOT_VERIFIABLE',
			10 => 'NET_SSH2_DISCONNECT_CONNECTION_LOST',
			11 => 'NET_SSH2_DISCONNECT_BY_APPLICATION',
			12 => 'NET_SSH2_DISCONNECT_TOO_MANY_CONNECTIONS',
			13 => 'NET_SSH2_DISCONNECT_AUTH_CANCELLED_BY_USER',
			14 => 'NET_SSH2_DISCONNECT_NO_MORE_AUTH_METHODS_AVAILABLE',
			15 => 'NET_SSH2_DISCONNECT_ILLEGAL_USER_NAME'
		);
		$this->channel_open_failure_reasons = array(
			1 => 'NET_SSH2_OPEN_ADMINISTRATIVELY_PROHIBITED'
		);
		$this->terminal_modes = array(
			0 => 'NET_SSH2_TTY_OP_END'
		);
		$this->channel_extended_data_type_codes = array(
			1 => 'NET_SSH2_EXTENDED_DATA_STDERR'
		);

		$this->_define_array(
			$this->message_numbers,
			$this->disconnect_reasons,
			$this->channel_open_failure_reasons,
			$this->terminal_modes,
			$this->channel_extended_data_type_codes
		);

		$this->fsock = @fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$this->fsock)
		{
			return;
		}

		/* According to the SSH2 specs,

		  "The server MAY send other lines of data before sending the version
		   string.  Each line SHOULD be terminated by a Carriage Return and Line
		   Feed.  Such lines MUST NOT begin with "SSH-", and SHOULD be encoded
		   in ISO-10646 UTF-8 [RFC3629] (language is not specified).  Clients
		   MUST be able to process such lines." */
		$temp = '';
		while (!feof($this->fsock) && !preg_match('#^SSH-(\d\.\d+)#', $temp, $matches))
		{
			if (substr($temp, -2) == "\r\n")
			{
				$this->debug_info.= $temp;
				$temp = '';
			}
			$temp.= fgets($this->fsock, 255);
		}
		$this->server_identifier = trim($temp);
		$this->debug_info = utf8_decode($this->debug_info);

		if ($matches[1] != '1.99' && $matches[1] != '2.0')
		{
			return;
		}

		fputs($this->fsock, $this->identifier . "\r\n");

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return;
		}

		if (ord($response[0]) != NET_SSH2_MSG_KEXINIT)
		{
			return;
		}

		if (!$this->_key_exchange($response))
		{
			return;
		}

		$this->bitmap = NET_SSH2_MASK_CONSTRUCTOR;
	}

	/**
	 * Key Exchange
	 *
	 * @param String $kexinit_payload_server
	 * @access private
	 */
	function _key_exchange($kexinit_payload_server)
	{
		static $kex_algorithms = array(
			'diffie-hellman-group1-sha1', // REQUIRED
			'diffie-hellman-group14-sha1' // REQUIRED
		);

		static $server_host_key_algorithms = array(
			'ssh-rsa', // RECOMMENDED  sign   Raw RSA Key
			'ssh-dss'  // REQUIRED	 sign   Raw DSS Key
		);

		static $encryption_algorithms = array(
			'arcfour',	// OPTIONAL		  the ARCFOUR stream cipher with a 128-bit key
			'aes128-cbc', // RECOMMENDED	   AES with a 128-bit key
			'aes192-cbc', // OPTIONAL		  AES with a 192-bit key
			'aes256-cbc', // OPTIONAL		  AES in CBC mode, with a 256-bit key
			'3des-cbc',   // REQUIRED		  three-key 3DES in CBC mode
			'none'		// OPTIONAL		  no encryption; NOT RECOMMENDED
		);

		static $mac_algorithms = array(
			'hmac-sha1-96', // RECOMMENDED	 first 96 bits of HMAC-SHA1 (digest length = 12, key length = 20)
			'hmac-sha1',	// REQUIRED		HMAC-SHA1 (digest length = key length = 20)
			'hmac-md5-96',  // OPTIONAL		first 96 bits of HMAC-MD5 (digest length = 12, key length = 16)
			'hmac-md5',	 // OPTIONAL		HMAC-MD5 (digest length = key length = 16)
			'none'		  // OPTIONAL		no MAC; NOT RECOMMENDED
		);

		static $compression_algorithms = array(
			'none'   // REQUIRED		no compression
			//'zlib' // OPTIONAL		ZLIB (LZ77) compression
		);

		static $str_kex_algorithms, $str_server_host_key_algorithms,
			   $encryption_algorithms_server_to_client, $mac_algorithms_server_to_client, $compression_algorithms_server_to_client,
			   $encryption_algorithms_client_to_server, $mac_algorithms_client_to_server, $compression_algorithms_client_to_server;

		if (empty($str_kex_algorithms)) {
			$str_kex_algorithms = implode(',', $kex_algorithms);
			$str_server_host_key_algorithms = implode(',', $server_host_key_algorithms);
			$encryption_algorithms_server_to_client = $encryption_algorithms_client_to_server = implode(',', $encryption_algorithms);
			$mac_algorithms_server_to_client = $mac_algorithms_client_to_server = implode(',', $mac_algorithms);
			$compression_algorithms_server_to_client = $compression_algorithms_client_to_server = implode(',', $compression_algorithms);
		}

		$client_cookie = '';
		for ($i = 0; $i < 16; $i++)
		{
			$client_cookie.= chr(crypt_random(0, 255));
		}

		$response = $kexinit_payload_server;
		$this->_string_shift($response, 1); // skip past the message number (it should be SSH_MSG_KEXINIT)
		list(, $server_cookie) = unpack('a16', $this->_string_shift($response, 16));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->kex_algorithms = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->server_host_key_algorithms = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->encryption_algorithms_client_to_server = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->encryption_algorithms_server_to_client = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->mac_algorithms_client_to_server = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->mac_algorithms_server_to_client = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->compression_algorithms_client_to_server = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->compression_algorithms_server_to_client = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->languages_client_to_server = explode(',', $this->_string_shift($response, $temp['length']));

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->languages_server_to_client = explode(',', $this->_string_shift($response, $temp['length']));

		list(, $first_kex_packet_follows) = unpack('C', $this->_string_shift($response, 1));
		$first_kex_packet_follows = $first_kex_packet_follows != 0;

		// the sending of NET_SSH2_MSG_KEXINIT could go in one of two places.  this is the second place.
		$kexinit_payload_client = pack('Ca*Na*Na*Na*Na*Na*Na*Na*Na*Na*Na*CN',
			NET_SSH2_MSG_KEXINIT, $client_cookie, strlen($str_kex_algorithms), $str_kex_algorithms,
			strlen($str_server_host_key_algorithms), $str_server_host_key_algorithms, strlen($encryption_algorithms_client_to_server),
			$encryption_algorithms_client_to_server, strlen($encryption_algorithms_server_to_client), $encryption_algorithms_server_to_client,
			strlen($mac_algorithms_client_to_server), $mac_algorithms_client_to_server, strlen($mac_algorithms_server_to_client),
			$mac_algorithms_server_to_client, strlen($compression_algorithms_client_to_server), $compression_algorithms_client_to_server,
			strlen($compression_algorithms_server_to_client), $compression_algorithms_server_to_client, 0, '', 0, '',
			0, 0
		);

		if (!$this->_send_binary_packet($kexinit_payload_client))
		{
			return false;
		}
		// here ends the second place.

		// we need to decide upon the symmetric encryption algorithms before we do the diffie-hellman key exchange
		for ($i = 0; $i < count($encryption_algorithms) && !in_array($encryption_algorithms[$i], $this->encryption_algorithms_server_to_client); $i++);
		if ($i == count($encryption_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}

		// we don't initialize any crypto-objects, yet - we do that, later. for now, we need the lengths to make the
		// diffie-hellman key exchange as fast as possible
		$decrypt = $encryption_algorithms[$i];
		switch ($decrypt)
		{
			case '3des-cbc':
				$decryptKeyLength = 24; // eg. 192 / 8
				break;
			case 'aes256-cbc':
				$decryptKeyLength = 32; // eg. 256 / 8
				break;
			case 'aes192-cbc':
				$decryptKeyLength = 24; // eg. 192 / 8
				break;
			case 'aes128-cbc':
				$decryptKeyLength = 16; // eg. 128 / 8
				break;
			case 'arcfour':
				$decryptKeyLength = 16; // eg. 128 / 8
				break;
			case 'none';
				$decryptKeyLength = 0;
		}

		for ($i = 0; $i < count($encryption_algorithms) && !in_array($encryption_algorithms[$i], $this->encryption_algorithms_client_to_server); $i++);
		if ($i == count($encryption_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}

		$encrypt = $encryption_algorithms[$i];
		switch ($encrypt)
		{
			case '3des-cbc':
				$encryptKeyLength = 24;
				break;
			case 'aes256-cbc':
				$encryptKeyLength = 32;
				break;
			case 'aes192-cbc':
				$encryptKeyLength = 24;
				break;
			case 'aes128-cbc':
				$encryptKeyLength = 16;
				break;
			case 'arcfour':
				$encryptKeyLength = 16;
				break;
			case 'none';
				$encryptKeyLength = 0;
		}

		$keyLength = $decryptKeyLength > $encryptKeyLength ? $decryptKeyLength : $encryptKeyLength;

		// through diffie-hellman key exchange a symmetric key is obtained
		for ($i = 0; $i < count($kex_algorithms) && !in_array($kex_algorithms[$i], $this->kex_algorithms); $i++);
		if ($i == count($kex_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}

		switch ($kex_algorithms[$i])
		{
			// see http://tools.ietf.org/html/rfc2409#section-6.2 and 
			// http://tools.ietf.org/html/rfc2412, appendex E
			case 'diffie-hellman-group1-sha1':
				$p = pack('N32', 0xFFFFFFFF, 0xFFFFFFFF, 0xC90FDAA2, 0x2168C234, 0xC4C6628B, 0x80DC1CD1,
								 0x29024E08, 0x8A67CC74, 0x020BBEA6, 0x3B139B22, 0x514A0879, 0x8E3404DD,
								 0xEF9519B3, 0xCD3A431B, 0x302B0A6D, 0xF25F1437, 0x4FE1356D, 0x6D51C245,
								 0xE485B576, 0x625E7EC6, 0xF44C42E9, 0xA637ED6B, 0x0BFF5CB6, 0xF406B7ED,
								 0xEE386BFB, 0x5A899FA5, 0xAE9F2411, 0x7C4B1FE6, 0x49286651, 0xECE65381,
								 0xFFFFFFFF, 0xFFFFFFFF);
				$keyLength = $keyLength < 160 ? $keyLength : 160;
				$hash = 'sha1';
				break;
			// see http://tools.ietf.org/html/rfc3526#section-3
			case 'diffie-hellman-group14-sha1':
				$p = pack('N64', 0xFFFFFFFF, 0xFFFFFFFF, 0xC90FDAA2, 0x2168C234, 0xC4C6628B, 0x80DC1CD1,
								 0x29024E08, 0x8A67CC74, 0x020BBEA6, 0x3B139B22, 0x514A0879, 0x8E3404DD,
								 0xEF9519B3, 0xCD3A431B, 0x302B0A6D, 0xF25F1437, 0x4FE1356D, 0x6D51C245,
								 0xE485B576, 0x625E7EC6, 0xF44C42E9, 0xA637ED6B, 0x0BFF5CB6, 0xF406B7ED,
								 0xEE386BFB, 0x5A899FA5, 0xAE9F2411, 0x7C4B1FE6, 0x49286651, 0xECE45B3D,
								 0xC2007CB8, 0xA163BF05, 0x98DA4836, 0x1C55D39A, 0x69163FA8, 0xFD24CF5F,
								 0x83655D23, 0xDCA3AD96, 0x1C62F356, 0x208552BB, 0x9ED52907, 0x7096966D,
								 0x670C354E, 0x4ABC9804, 0xF1746C08, 0xCA18217C, 0x32905E46, 0x2E36CE3B,
								 0xE39E772C, 0x180E8603, 0x9B2783A2, 0xEC07A28F, 0xB5C55DF0, 0x6F4C52C9,
								 0xDE2BCBF6, 0x95581718, 0x3995497C, 0xEA956AE5, 0x15D22618, 0x98FA0510,
								 0x15728E5A, 0x8AACAA68, 0xFFFFFFFF, 0xFFFFFFFF);
				$keyLength = $keyLength < 160 ? $keyLength : 160;
				$hash = 'sha1';
		}

		$p = new biginteger($p, 256);
		//$q = $p->bitwise_right_shift(1);

		/* To increase the speed of the key exchange, both client and server may
		   reduce the size of their private exponents.  It should be at least
		   twice as long as the key material that is generated from the shared
		   secret.  For more details, see the paper by van Oorschot and Wiener
		   [VAN-OORSCHOT].

		   -- http://tools.ietf.org/html/rfc4419#section-6.2 */
		$q = new biginteger(1);
		$q = $q->bitwise_left_shift(2 * $keyLength);
		$q = $q->subtract(new biginteger(1));

		$g = new biginteger(2);
		$x = new biginteger();
		$x = $x->random(new biginteger(1), $q, 'crypt_random');
		$e = $g->mod_pow($x, $p);

		$eBytes = $e->to_bytes(true);
		$data = pack('CNa*', NET_SSH2_MSG_KEXDH_INIT, strlen($eBytes), $eBytes);

		if (!$this->_send_binary_packet($data))
		{
			return false;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		if ($type != NET_SSH2_MSG_KEXDH_REPLY)
		{
			return false;
		}

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$this->server_public_host_key = $server_public_host_key = $this->_string_shift($response, $temp['length']);

		$temp = unpack('Nlength', $this->_string_shift($server_public_host_key, 4));
		$public_key_format = $this->_string_shift($server_public_host_key, $temp['length']);

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$fBytes = $this->_string_shift($response, $temp['length']);
		$f = new biginteger($fBytes, -256);

		$temp = unpack('Nlength', $this->_string_shift($response, 4));
		$signature = $this->_string_shift($response, $temp['length']);

		$temp = unpack('Nlength', $this->_string_shift($signature, 4));
		$signature_format = $this->_string_shift($signature, $temp['length']);

		$key = $f->mod_pow($x, $p);
		$keyBytes = $key->to_bytes(true);

		$source = pack('Na*Na*Na*Na*Na*Na*Na*Na*',
			strlen($this->identifier), $this->identifier, strlen($this->server_identifier), $this->server_identifier,
			strlen($kexinit_payload_client), $kexinit_payload_client, strlen($kexinit_payload_server),
			$kexinit_payload_server, strlen($this->server_public_host_key), $this->server_public_host_key, strlen($eBytes),
			$eBytes, strlen($fBytes), $fBytes, strlen($keyBytes), $keyBytes
		);

		$source = pack('H*', $hash($source));

		if ($this->session_id === false)
		{
			$this->session_id = $source;
		}

		// if you the server's assymetric key matches the one you have on file, then you should be able to decrypt the
		// "signature" and get something that should equal the "exchange hash", as defined in the SSH-2 specs.
		// here, we just check to see if the "signature" is good.  you can verify whether or not the assymetric key is good,
		// later, with the getServerHostKeyAlgorithm() function
		for ($i = 0; $i < count($server_host_key_algorithms) && !in_array($server_host_key_algorithms[$i], $this->server_host_key_algorithms); $i++);
		if ($i == count($server_host_key_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}

		if ($public_key_format != $server_host_key_algorithms[$i] || $signature_format != $server_host_key_algorithms[$i])
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}

		switch ($server_host_key_algorithms[$i])
		{
			case 'ssh-dss':
				$temp = unpack('Nlength', $this->_string_shift($server_public_host_key, 4));
				$p = new biginteger($this->_string_shift($server_public_host_key, $temp['length']), -256);

				$temp = unpack('Nlength', $this->_string_shift($server_public_host_key, 4));
				$q = new biginteger($this->_string_shift($server_public_host_key, $temp['length']), -256);

				$temp = unpack('Nlength', $this->_string_shift($server_public_host_key, 4));
				$g = new biginteger($this->_string_shift($server_public_host_key, $temp['length']), -256);

				$temp = unpack('Nlength', $this->_string_shift($server_public_host_key, 4));
				$y = new biginteger($this->_string_shift($server_public_host_key, $temp['length']), -256);

				/* The value for 'dss_signature_blob' is encoded as a string containing
				   r, followed by s (which are 160-bit integers, without lengths or
				   padding, unsigned, and in network byte order). */
				$temp = unpack('Nlength', $this->_string_shift($signature, 4));
				if ($temp['length'] != 40)
				{
					return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
				}

				$r = new biginteger($this->_string_shift($signature, 20), 256);
				$s = new biginteger($this->_string_shift($signature, 20), 256);

				if ($r->compare($q) >= 0 || $s->compare($q) >= 0)
				{
					return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
				}

				$w = $s->mod_inverse($q);

				$u1 = $w->multiply(new biginteger(sha1($source), 16));
				list(, $u1) = $u1->divide($q);

				$u2 = $w->multiply($r);
				list(, $u2) = $u2->divide($q);

				$g = $g->mod_pow($u1, $p);
				$y = $y->mod_pow($u2, $p);

				$v = $g->multiply($y);
				list(, $v) = $v->divide($p);
				list(, $v) = $v->divide($q);

				if ($v->compare($r) != 0)
				{
					return $this->_disconnect(NET_SSH2_DISCONNECT_HOST_KEY_NOT_VERIFIABLE);
				}

				break;
			case 'ssh-rsa':
				$temp = unpack('Nlength', $this->_string_shift($server_public_host_key, 4));
				$e = new biginteger($this->_string_shift($server_public_host_key, $temp['length']), -256);

				$temp = unpack('Nlength', $this->_string_shift($server_public_host_key, 4));
				$n = new biginteger($this->_string_shift($server_public_host_key, $temp['length']), -256);
				$nLength = $temp['length'];

				$temp = unpack('Nlength', $this->_string_shift($signature, 4));
				$s = new biginteger($this->_string_shift($signature, $temp['length']), 256);

				// validate an RSA signature per "8.2 RSASSA-PKCS1-v1_5", "5.2.2 RSAVP1", and "9.1 EMSA-PSS" in the
				// following URL:
				// ftp://ftp.rsasecurity.com/pub/pkcs/pkcs-1/pkcs-1v2-1.pdf

				// also, see SSHRSA.c (rsa2_verifysig) in PuTTy's source.

				if ($s->compare(new biginteger()) < 0 || $s->compare($n->subtract(new biginteger(1))) > 0)
				{
					return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
				}

				$s = $s->mod_pow($e, $n);
				$s = $s->to_bytes();

				$h = pack('N4H*', 0x00302130, 0x0906052B, 0x0E03021A, 0x05000414, sha1($source));
				$h = chr(0x01) . str_repeat(chr(0xFF), $nLength - 3 - strlen($h)) . $h;

				if ($s != $h)
				{
					return $this->_disconnect(NET_SSH2_DISCONNECT_HOST_KEY_NOT_VERIFIABLE);
				}
		}

		$packet = pack('C',
			NET_SSH2_MSG_NEWKEYS
		);

		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		$response = $this->_get_binary_packet();

		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		if ($type != NET_SSH2_MSG_NEWKEYS)
		{
			return false;
		}

		switch ($encrypt)
		{
			case '3des-cbc':
				$this->encrypt = new tripledes();
				// $this->encrypt_block_size = 64 / 8 == the default
				break;
			case 'aes256-cbc':
				$this->encrypt = new aes();
				$this->encrypt_block_size = 16; // eg. 128 / 8
				break;
			case 'aes192-cbc':
				$this->encrypt = new aes();
				$this->encrypt_block_size = 16;
				break;
			case 'aes128-cbc':
				$this->encrypt = new aes();
				$this->encrypt_block_size = 16;
				break;
			case 'arcfour':
				$this->encrypt = new rc4();
		}

		switch ($decrypt)
		{
			case '3des-cbc':
				$this->decrypt = new tripledes();
				break;
			case 'aes256-cbc':
				$this->decrypt = new aes();
				$this->decrypt_block_size = 16;
				break;
			case 'aes192-cbc':
				$this->decrypt = new aes();
				$this->decrypt_block_size = 16;
				break;
			case 'aes128-cbc':
				$this->decrypt = new aes();
				$this->decrypt_block_size = 16;
				break;
			case 'arcfour':
				$this->decrypt = new rc4();
		}

		$keyBytes = pack('Na*', strlen($keyBytes), $keyBytes);

		if ($this->encrypt)
		{
			$this->encrypt->enable_continuous_buffer();
			$this->encrypt->disable_padding();

			$iv = pack('H*', $hash($keyBytes . $source . 'A' . $this->session_id));
			while ($this->encrypt_block_size > strlen($iv)) {
				$iv.= pack('H*', $hash($keyBytes . $source . $iv));
			}
			$this->encrypt->setIV(substr($iv, 0, $this->encrypt_block_size));

			$key = pack('H*', $hash($keyBytes . $source . 'C' . $this->session_id));
			while ($encryptKeyLength > strlen($key))
			{
				$key.= pack('H*', $hash($keyBytes . $source . $key));
			}
			$this->encrypt->set_key(substr($key, 0, $encryptKeyLength));
		}

		if ($this->decrypt)
		{
			$this->decrypt->enable_continuous_buffer();
			$this->decrypt->disable_padding();

			$iv = pack('H*', $hash($keyBytes . $source . 'B' . $this->session_id));
			while ($this->decrypt_block_size > strlen($iv))
			{
				$iv.= pack('H*', $hash($keyBytes . $source . $iv));
			}
			$this->decrypt->setIV(substr($iv, 0, $this->decrypt_block_size));

			$key = pack('H*', $hash($keyBytes . $source . 'D' . $this->session_id));
			while ($decryptKeyLength > strlen($key))
			{
				$key.= pack('H*', $hash($keyBytes . $source . $key));
			}
			$this->decrypt->set_key(substr($key, 0, $decryptKeyLength));
		}

		for ($i = 0; $i < count($mac_algorithms) && !in_array($mac_algorithms[$i], $this->mac_algorithms_client_to_server); $i++);
		if ($i == count($mac_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}

		$createKeyLength = 0; // ie. $mac_algorithms[$i] == 'none'
		switch ($mac_algorithms[$i])
		{
			case 'hmac-sha1':
				$this->hmac_create = new hash('sha1');
				$createKeyLength = 20;
				break;
			case 'hmac-sha1-96':
				$this->hmac_create = new hash('sha1-96');
				$createKeyLength = 20;
				break;
			case 'hmac-md5':
				$this->hmac_create = new hash('md5');
				$createKeyLength = 16;
				break;
			case 'hmac-md5-96':
				$this->hmac_create = new hash('md5-96');
				$createKeyLength = 16;
		}

		for ($i = 0; $i < count($mac_algorithms) && !in_array($mac_algorithms[$i], $this->mac_algorithms_server_to_client); $i++);
		if ($i == count($mac_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}

		$checkKeyLength = 0;
		$this->hmac_size = 0;
		switch ($mac_algorithms[$i])
		{
			case 'hmac-sha1':
				$this->hmac_check = new hash('sha1');
				$checkKeyLength = 20;
				$this->hmac_size = 20;
				break;
			case 'hmac-sha1-96':
				$this->hmac_check = new hash('sha1-96');
				$checkKeyLength = 20;
				$this->hmac_size = 12;
				break;
			case 'hmac-md5':
				$this->hmac_check = new hash('md5');
				$checkKeyLength = 16;
				$this->hmac_size = 16;
				break;
			case 'hmac-md5-96':
				$this->hmac_check = new hash('md5-96');
				$checkKeyLength = 16;
				$this->hmac_size = 12;
		}

		$key = pack('H*', $hash($keyBytes . $source . 'E' . $this->session_id));
		while ($createKeyLength > strlen($key))
		{
			$key.= pack('H*', $hash($keyBytes . $source . $key));
		}
		$this->hmac_create->set_key(substr($key, 0, $createKeyLength));

		$key = pack('H*', $hash($keyBytes . $source . 'F' . $this->session_id));
		while ($checkKeyLength > strlen($key))
		{
			$key.= pack('H*', $hash($keyBytes . $source . $key));
		}
		$this->hmac_check->set_key(substr($key, 0, $checkKeyLength));

		for ($i = 0; $i < count($compression_algorithms) && !in_array($compression_algorithms[$i], $this->compression_algorithms_server_to_client); $i++);
		if ($i == count($compression_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}
		$this->decompress = $compression_algorithms[$i] == 'zlib';

		for ($i = 0; $i < count($compression_algorithms) && !in_array($compression_algorithms[$i], $this->compression_algorithms_client_to_server); $i++);
		if ($i == count($compression_algorithms))
		{
			return $this->_disconnect(NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED);
		}
		$this->compress = $compression_algorithms[$i] == 'zlib';

		return true;
	}

	/**
	 * Login
	 *
	 * @param String $username
	 * @param optional String $password
	 * @return Boolean
	 * @access public
	 * @internal It might be worthwhile, at some point, to protect against {@link http://tools.ietf.org/html/rfc4251#section-9.3.9 traffic analysis}
				 by sending dummy SSH_MSG_IGNORE messages.
	 */
	function login($username, $password = '')
	{
		if (!($this->bitmap & NET_SSH2_MASK_CONSTRUCTOR))
		{
			return false;
		}

		$packet = pack('CNa*',
			NET_SSH2_MSG_SERVICE_REQUEST, strlen('ssh-userauth'), 'ssh-userauth'
		);

		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		if ($type != NET_SSH2_MSG_SERVICE_ACCEPT)
		{
			return false;
		}

		// publickey authentication is required, per the SSH-2 specs, however, we don't support it.
		$utf8_password = utf8_encode($password);
		$packet = pack('CNa*Na*Na*CNa*',
			NET_SSH2_MSG_USERAUTH_REQUEST, strlen($username), $username, strlen('ssh-connection'), 'ssh-connection',
			strlen('password'), 'password', 0, strlen($utf8_password), $utf8_password
		);

		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		// remove the username and password from the last logged packet
		if (defined('NET_SSH2_LOGGING'))
		{
			$packet = pack('CNa*Na*Na*CNa*',
				NET_SSH2_MSG_USERAUTH_REQUEST, strlen('username'), 'username', strlen('ssh-connection'), 'ssh-connection',
				strlen('password'), 'password', 0, strlen('password'), 'password'
			);
			$this->message_log[count($this->message_log) - 1] = $packet;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		switch ($type)
		{
			case NET_SSH2_MSG_USERAUTH_PASSWD_CHANGEREQ: // in theory, the password can be changed
				list(, $length) = unpack('N', $this->_string_shift($response, 4));
				$this->debug_info.= "\r\n\r\nSSH_MSG_USERAUTH_PASSWD_CHANGEREQ:\r\n" . utf8_decode($this->_string_shift($response, $length));
				return $this->_disconnect(NET_SSH2_DISCONNECT_AUTH_CANCELLED_BY_USER);
			case NET_SSH2_MSG_USERAUTH_FAILURE:
				list(, $length) = unpack('Nlength', $this->_string_shift($response, 4));
				$this->debug_info.= "\r\n\r\nSSH_MSG_USERAUTH_FAILURE:\r\n" . $this->_string_shift($response, $length);
				return $this->_disconnect(NET_SSH2_DISCONNECT_AUTH_CANCELLED_BY_USER);
			case NET_SSH2_MSG_USERAUTH_SUCCESS:
				$this->bitmap |= NET_SSH2_MASK_LOGIN;
				return true;
		}

		return false;
	}

	/**
	 * Execute Command
	 *
	 * @param String $command
	 * @return String
	 * @access public
	 */
	function exec($command)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$client_channel = 0; // PuTTy uses 0x100
		// RFC4254 defines the window size as "bytes the other party can send before it must wait for the window to be 
		// adjusted".  0x7FFFFFFF is, at 4GB, the max size.  technically, it should probably be decremented, but, honestly,
		// if you're transfering more than 4GB, you probably shouldn't be using phpseclib, anyway.
		// see http://tools.ietf.org/html/rfc4254#section-5.2 for more info
		$window_size = 0x7FFFFFFF;
		// 0x8000 is the maximum max packet size, per http://tools.ietf.org/html/rfc4253#section-6.1, although since PuTTy
		// uses 0x4000, that's what will be used here, as well.  0x7FFFFFFF could be used, as well (i've not encountered
		// any problems, using it, myself), but that's not what the specs say, so whatever.
		$packet_size = 0x4000;

		$packet = pack('CNa*N3',
			NET_SSH2_MSG_CHANNEL_OPEN, strlen('session'), 'session', $client_channel, $window_size, $packet_size);

		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		switch ($type)
		{
			case NET_SSH2_MSG_CHANNEL_OPEN_CONFIRMATION:
				$this->_string_shift($response, 4);
				list(, $server_channel) = unpack('N', $this->_string_shift($response, 4));
				break;
			case NET_SSH2_MSG_CHANNEL_OPEN_FAILURE:
				return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
		}

		$terminal_modes = pack('C', NET_SSH2_TTY_OP_END);
		$packet = pack('CNNa*CNa*N5a*',
			NET_SSH2_MSG_CHANNEL_REQUEST, $server_channel, strlen('pty-req'), 'pty-req', 1, strlen('vt100'), 'vt100',
			80, 24, 0, 0, strlen($terminal_modes), $terminal_modes);

		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		switch ($type)
		{
			case NET_SSH2_MSG_CHANNEL_SUCCESS:
				break;
			case NET_SSH2_MSG_CHANNEL_FAILURE:
			default:
				return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
		}

		$packet = pack('CNNa*CNa*',
			NET_SSH2_MSG_CHANNEL_REQUEST, $server_channel, strlen('exec'), 'exec', 1, strlen($command), $command);
		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		switch ($type)
		{
			case NET_SSH2_MSG_CHANNEL_SUCCESS:
				break;
			case NET_SSH2_MSG_CHANNEL_FAILURE:
			default:
				return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
		}

		$output = '';
		while (true)
		{
			$temp = $this->_get_channel_packet();
			switch (true)
			{
				case $temp === true:
					return $output;
				case $temp === false:
					return false;
				default:
					$output.= $temp;
			}
		}
	}

	/**
	 * Disconnect
	 *
	 * @access public
	 */
	function disconnect()
	{
		$this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
	}

	/**
	 * Destructor.
	 *
	 * Will be called, automatically, if you're supporting just PHP5.  If you're supporting PHP4, you'll need to call
	 * disconnect().
	 *
	 * @access public
	 */
	function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Gets Binary Packets
	 *
	 * See '6. Binary Packet Protocol' of rfc4253 for more info.
	 *
	 * @see ssh2::_send_binary_packet()
	 * @return String
	 * @access private
	 */
	function _get_binary_packet()
	{
		if (feof($this->fsock))
		{
			return false;
		}

		$raw = fread($this->fsock, $this->decrypt_block_size);

		if ($this->decrypt !== false)
		{
			$raw = $this->decrypt->decrypt($raw);
		}

		$temp = unpack('Npacket_length/Cpadding_length', $this->_string_shift($raw, 5));
		$packet_length = $temp['packet_length'];
		$padding_length = $temp['padding_length'];

		$remaining_length = $packet_length + 4 - $this->decrypt_block_size;
		$buffer = '';
		while ($remaining_length > 0)
		{
			$temp = fread($this->fsock, $remaining_length);
			$buffer.= $temp;
			$remaining_length-= strlen($temp);
		}
		if (!empty($buffer))
		{
			$raw.= $this->decrypt !== false ? $this->decrypt->decrypt($buffer) : $buffer;
			$buffer = $temp = '';
		}

		$payload = $this->_string_shift($raw, $packet_length - $padding_length - 1);
		$padding = $this->_string_shift($raw, $padding_length); // should leave $raw empty

		if ($this->hmac_check !== false)
		{
			$hmac = fread($this->fsock, $this->hmac_size);
			if ($hmac != $this->hmac_check->create(pack('NNCa*', $this->get_seq_no, $packet_length, $padding_length, $payload . $padding)))
			{
				return false;
			}
		}

		//if ($this->decompress)
		//{
		//	$payload = gzinflate(substr($payload, 2));
		//}

		$this->get_seq_no++;

		if (defined('NET_SSH2_LOGGING'))
		{
			$this->message_number_log[] = '<- ' . $this->message_numbers[ord($payload[0])];
			$this->message_log[] = $payload;
		}

		return $this->_filter($payload);
	}

	/**
	 * Filter Binary Packets
	 *
	 * Because some binary packets need to be ignored...
	 *
	 * @see ssh2::_get_binary_packet()
	 * @return String
	 * @access private
	 */
	function _filter($payload)
	{
		switch (ord($payload[0]))
		{
			case NET_SSH2_MSG_DISCONNECT:
				$this->_string_shift($payload, 1);
				list(, $reason_code, $length) = unpack('N2', $this->_string_shift($payload, 8));
				$this->debug_info.= "\r\n\r\nSSH_MSG_DISCONNECT:\r\n" . $this->disconnect_reasons[$reason_code] . "\r\n" . utf8_decode($this->_string_shift($payload, $temp['length']));
				$this->bitmask = 0;
				return false;
			case NET_SSH2_MSG_IGNORE:
				$payload = $this->_get_binary_packet();
				break;
			case NET_SSH2_MSG_DEBUG:
				$this->_string_shift($payload, 2);
				list(, $length) = unpack('N', $payload);
				$this->debug_info.= "\r\n\r\nSSH_MSG_DEBUG:\r\n" . utf8_decode($this->_string_shift($payload, $length));
				$payload = $this->_get_binary_packet();
				break;
			case NET_SSH2_MSG_UNIMPLEMENTED:
				return false;
			case NET_SSH2_MSG_KEXINIT:
				if ($this->session_id !== false)
				{
					if (!$this->_key_exchange($payload))
					{
						$this->bitmask = 0;
						return false;
					}
					$payload = $this->_get_binary_packet();
				}
		}

		// see http://tools.ietf.org/html/rfc4252#section-5.4; only called when the encryption has been activated and when we haven't already logged in
		if (($this->bitmap & NET_SSH2_MASK_CONSTRUCTOR) && !($this->bitmap & NET_SSH2_MASK_LOGIN) && ord($payload[0]) == NET_SSH2_MSG_USERAUTH_BANNER)
		{
			$this->_string_shift($payload, 1);
			list(, $length) = unpack('N', $payload);
			$this->debug_info.= "\r\n\r\nSSH_MSG_USERAUTH_BANNER:\r\n" . utf8_decode($this->_string_shift($payload, $length));
			$payload = $this->_get_binary_packet();
		}

		// only called when we've already logged in
		if (($this->bitmap & NET_SSH2_MASK_CONSTRUCTOR) && ($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			switch (ord($payload[0]))
			{
				case NET_SSH2_MSG_GLOBAL_REQUEST: // see http://tools.ietf.org/html/rfc4254#section-4
					$this->_string_shift($payload, 1);
					list(, $length) = unpack('N', $payload);
					$this->debug_info.= "\r\n\r\nSSH_MSG_GLOBAL_REQUEST:\r\n" . utf8_decode($this->_string_shift($payload, $length));

					if (!$this->_send_binary_packet(pack('C', NET_SSH2_MSG_REQUEST_FAILURE)))
					{
						return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
					}

					$payload = $this->_get_binary_packet();
					break;
				case NET_SSH2_MSG_CHANNEL_OPEN: // see http://tools.ietf.org/html/rfc4254#section-5.1
					$this->_string_shift($payload, 1);
					list(, $length) = unpack('N', $payload);
					$this->debug_info.= "\r\n\r\nSSH_MSG_CHANNEL_OPEN:\r\n" . utf8_decode($this->_string_shift($payload, $length));

					list(, $recipient_channel) = unpack('N', $this->_string_shift($payload, 4));

					$packet = pack('CN3a*Na*',
						NET_SSH2_MSG_REQUEST_FAILURE, $recipient_channel, NET_SSH2_OPEN_ADMINISTRATIVELY_PROHIBITED, 0, '', 0, '');

					if (!$this->_send_binary_packet($packet))
					{
						return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
					}

					$payload = $this->_get_binary_packet();
					break;
				case NET_SSH2_MSG_CHANNEL_WINDOW_ADJUST:
					$payload = $this->_get_binary_packet();
			}
		}

		return $payload;
	}

	/**
	 * Gets channel data
	 *
	 * Returns the data as a string if it's available and false if not.
	 *
	 * @return Mixed
	 * @access private
	 */
	function _get_channel_packet()
	{
		while (true)
		{
			$response = $this->_get_binary_packet();
			if ($response === false)
			{
				return false;
			}

			list(, $type) = unpack('C', $this->_string_shift($response, 1));

			switch ($type)
			{
				case NET_SSH2_MSG_CHANNEL_DATA:
					$this->_string_shift($response, 4); // skip over server channel
					list(, $length) = unpack('N', $this->_string_shift($response, 4));
					return $this->_string_shift($response, $length);
				case NET_SSH2_MSG_CHANNEL_EXTENDED_DATA:
					$this->_string_shift($response, 4); // skip over server channel
					list(, $data_type_code, $length) = unpack('N2', $this->_string_shift($response, 8));
					$data = $this->_string_shift($response, $length);
					switch ($data_type_code)
					{
						case NET_SSH2_EXTENDED_DATA_STDERR:
							$this->debug_info.= "\r\n\r\nSSH_MSG_CHANNEL_EXTENDED_DATA (SSH_EXTENDED_DATA_STDERR):\r\n" . $data;
					}
					break;
				case NET_SSH2_MSG_CHANNEL_REQUEST:
					$this->_string_shift($response, 4); // skip over server channel
					list(, $length) = unpack('N', $this->_string_shift($response, 4));
					$value = $this->_string_shift($response, $length);
					switch ($value)
					{
						case 'exit-signal':
							$this->_string_shift($response, 1);
							list(, $length) = unpack('N', $this->_string_shift($response, 4));
							$this->debug_info.= "\r\n\r\nSSH_MSG_CHANNEL_REQUEST (exit-signal):\r\nSIG" . $this->_string_shift($response, $length);
							$this->_string_shift($response, 1);
							list(, $length) = unpack('N', $this->_string_shift($response, 4));
							$this->debug_info.= "\r\n" . $this->_string_shift($response, $length);
						case 'exit-status':
						default:
							// "Some systems may not implement signals, in which case they SHOULD ignore this message."
							//  -- http://tools.ietf.org/html/rfc4254#section-6.9
							//break 2;
							break;
					}
					break;
				case NET_SSH2_MSG_CHANNEL_CLOSE:
					$this->_send_binary_packet(pack('CN', NET_SSH2_MSG_CHANNEL_CLOSE, $server_channel));
					return true;
				case NET_SSH2_MSG_CHANNEL_EOF:
					break;
				default:
					return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
			}
		}
	}

	/**
	 * Sends Binary Packets
	 *
	 * See '6. Binary Packet Protocol' of rfc4253 for more info.
	 *
	 * @param String $data
	 * @see ssh2::_get_binary_packet()
	 * @return Boolean
	 * @access private
	 */
	function _send_binary_packet($data)
	{
		if (feof($this->fsock))
		{
			return false;
		}

		if (defined('NET_SSH2_LOGGING'))
		{
			$this->message_number_log[] = '-> ' . $this->message_numbers[ord($data[0])];
			$this->message_log[] = $data;
		}

		//if ($this->compress)
		//{
		//	// the -4 removes the checksum:
		//	// http://php.net/function.gzcompress#57710
		//	$data = substr(gzcompress($data), 0, -4);
		//}

		// 4 (packet length) + 1 (padding length) + 4 (minimal padding amount) == 9
		$packet_length = strlen($data) + 9;
		// round up to the nearest $this->encrypt_block_size
		$packet_length+= (($this->encrypt_block_size - 1) * $packet_length) % $this->encrypt_block_size;
		// subtracting strlen($data) is obvious - subtracting 5 is necessary because of packet_length and padding_length
		$padding_length = $packet_length - strlen($data) - 5;

		$padding = '';
		for ($i = 0; $i < $padding_length; $i++)
		{
			$padding.= chr(crypt_random(0, 255));
		}

		// we subtract 4 from packet_length because the packet_length field isn't supposed to include itself
		$packet = pack('NCa*', $packet_length - 4, $padding_length, $data . $padding);

		$hmac = $this->hmac_create !== false ? $this->hmac_create->create(pack('Na*', $this->send_seq_no, $packet)) : '';
		$this->send_seq_no++;

		if ($this->encrypt !== false)
		{
			$packet = $this->encrypt->encrypt($packet);
		}

		$packet.= $hmac;

		return strlen($packet) == fputs($this->fsock, $packet);
	}

	/**
	 * Disconnect
	 *
	 * @param Integer $reason
	 * @return Boolean
	 * @access private
	 */
	function _disconnect($reason)
	{
		if ($this->bitmap)
		{
			$data = pack('CNNa*Na*', NET_SSH2_MSG_DISCONNECT, $reason, 0, '', 0, '');
			$this->_send_binary_packet($data);
			$this->bitmap = 0;
			fclose($this->fsock);
			return false;
		}
	}

	/**
	 * String Shift
	 *
	 * Inspired by array_shift
	 *
	 * @param String $string
	 * @param optional Integer $index
	 * @return String
	 * @access private
	 */
	function _string_shift(&$string, $index = 1)
	{
		$substr = substr($string, 0, $index);
		$string = substr($string, $index);
		return $substr;
	}

	/**
	 * Define Array
	 *
	 * Takes any number of arrays whose indices are integers and whose values are strings and defines a bunch of
	 * named constants from it, using the value as the name of the constant and the index as the value of the constant.
	 * If any of the constants that would be defined already exists, none of the constants will be defined.
	 *
	 * @param Array $array
	 * @access private
	 */
	function _define_array()
	{
		$args = func_get_args();
		foreach ($args as $arg)
		{
			foreach ($arg as $key=>$value)
			{
				if (!defined($value))
				{
					define($value, $key);
				}
				else
				{
					break 2;
				}
			}
		}
	}

	/**
	 * Returns a log of the packets that have been sent and received.
	 *
	 * $type can be either NET_SSH2_LOG_SIMPLE or NET_SSH2_LOG_COMPLEX.  Enable by defining NET_SSH2_LOGGING.
	 *
	 * @param Integer $type
	 * @access public
	 * @return String or Array
	 */
	function get_log($type = NET_SSH2_LOG_COMPLEX)
	{
		if ($type == NET_SSH2_LOG_SIMPLE)
		{
			return $this->message_number_log;
		}

		static $boundary = ':', $long_width = 65, $short_width = 15;

		$output = '';
		for ($i = 0; $i < count($this->message_log); $i++)
		{
			$output.= $this->message_number_log[$i] . "\r\n";
			$current_log = $this->message_log[$i];
			do
			{
				$fragment = $this->_string_shift($current_log, $short_width);
				$hex = substr(
						   preg_replace(
							   '#(.)#es',
							   '"' . $boundary . '" . str_pad(dechex(ord(substr("\\1", -1))), 2, "0", STR_PAD_LEFT)',
							   $fragment),
						   strlen($boundary)
					   );
				// replace non ASCII printable characters with dots
				// http://en.wikipedia.org/wiki/ASCII#ASCII_printable_characters
				$raw = preg_replace('#[^\x20-\x7E]#', '.', $fragment);
				$output.= str_pad($hex, $long_width - $short_width, ' ') . $raw . "\r\n";
			}
			while (!empty($current_log));
			$output.= "\r\n";
		}

		return $output;
	}

	/**
	 * Returns Debug Information
	 *
	 * If any debug information is sent by the server, this function can be used to access it.
	 *
	 * @return String
	 * @access public
	 */
	function get_debug_info()
	{
		return $this->debug_info;
	}

	/**
	 * Return the server identification.
	 *
	 * @return String
	 * @access public
	 */
	function get_server_identification()
	{
		return $this->server_identifier;
	}

	/**
	 * Return a list of the key exchange algorithms the server supports.
	 *
	 * @return Array
	 * @access public
	 */
	function get_kex_algorithms()
	{
		return $this->kex_algorithms;
	}

	/**
	 * Return a list of the host key (public key) algorithms the server supports.
	 *
	 * @return Array
	 * @access public
	 */
	function get_server_host_key_algorithms()
	{
		return $this->server_host_key_algorithms;
	}

	/**
	 * Return a list of the (symmetric key) encryption algorithms the server supports, when receiving stuff from the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_encryption_algorithms_client_to_server()
	{
		return $this->encryption_algorithms_client_to_server;
	}

	/**
	 * Return a list of the (symmetric key) encryption algorithms the server supports, when sending stuff to the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_encryption_algorithms_server_to_client()
	{
		return $this->encryption_algorithms_server_to_client;
	}

	/**
	 * Return a list of the MAC algorithms the server supports, when receiving stuff from the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_mac_algorithms_client_to_server()
	{
		return $this->mac_algorithms_client_to_server;
	}

	/**
	 * Return a list of the MAC algorithms the server supports, when sending stuff to the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_mac_algorithms_server_to_client()
	{
		return $this->mac_algorithms_server_to_client;
	}

	/**
	 * Return a list of the compression algorithms the server supports, when receiving stuff from the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_compression_algorithms_client_to_server()
	{
		return $this->compression_algorithms_client_to_server;
	}

	/**
	 * Return a list of the compression algorithms the server supports, when sending stuff to the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_compression_algorithms_server_to_client()
	{
		return $this->compression_algorithms_server_to_client;
	}

	/**
	 * Return a list of the languages the server supports, when sending stuff to the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_languages_server_to_client()
	{
		return $this->languages_server_to_client;
	}

	/**
	 * Return a list of the languages the server supports, when receiving stuff from the client.
	 *
	 * @return Array
	 * @access public
	 */
	function get_languages_client_to_server()
	{
		return $this->languages_client_to_server;
	}

	/**
	 * Returns the server public host key.
	 *
	 * Caching this the first time you connect to a server and checking the result on subsequent connections
	 * is recommended.
	 *
	 * @return Array
	 * @access public
	 */
	function get_server_public_host_key()
	{
		return $this->server_public_host_key;
	}
}

/**
 * Pure-PHP implementation of SFTP.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.1.0
 * @access  public
 * @package sftp
 */
class ssh2_sftp extends ssh2
{
	/**
	 * Packet Types
	 *
	 * @see ssh2_sftp::__construct()
	 * @var Array
	 * @access private
	 */
	var $packet_types = array();

	/**
	 * Status Codes
	 *
	 * @see ssh2_sftp::__construct()
	 * @var Array
	 * @access private
	 */
	var $status_codes = array();

	/**
	 * The Window Size
	 *
	 * Bytes the other party can send before it must wait for the window to be adjusted (0x7FFFFFFF = 4GB)
	 *
	 * @var Integer
	 * @see ssh2::exec()
	 * @access private
	 */
	var $window_size = 0x7FFFFFFF;

	/**
	 * The Packet Size
	 *
	 * Maximum max packet size
	 *
	 * @var Integer
	 * @see ssh2::exec()
	 * @access private
	 */
	var $packet_size = 0x4000;

	/**
	 * The Client Channel
	 *
	 * Net_SSH2::exec() uses 0
	 *
	 * @var Integer
	 * @see ssh2::exec()
	 * @access private
	 */
	var $client_channel = 1;

	/**
	 * The Server Channel
	 *
	 * @var Integer
	 * @see ssh2_sftp::_init_channel()
	 * @access private
	 */
	var $server_channel = -1;

	/**
	 * The Request ID
	 *
	 * The request ID exists in the off chance that a packet is sent out-of-order.  Of course, this library doesn't support
	 * concurrent actions, so it's somewhat academic, here.
	 *
	 * @var Integer
	 * @see ssh2_sftp::_send_sftp_packet()
	 * @access private
	 */
	var $request_id = false;

	/**
	 * The Packet Type
	 *
	 * The request ID exists in the off chance that a packet is sent out-of-order.  Of course, this library doesn't support
	 * concurrent actions, so it's somewhat academic, here.
	 *
	 * @var Integer
	 * @see ssh2_sftp::_send_sftp_packet()
	 * @access private
	 */
	var $packet_type = -1;

	/**
	 * Extensions supported by the server
	 *
	 * @var Array
	 * @see ssh2_sftp::_init_channel()
	 * @access private
	 */
	var $extensions = array();

	/**
	 * Server SFTP version
	 *
	 * @var Integer
	 * @see ssh2_sftp::_init_channel()
	 * @access private
	 */
	var $version;

	/**
	 * Current working directory
	 *
	 * @var String
	 * @see ssh2_sftp::_realpath()
	 * @see ssh2_sftp::chdir()
	 * @access private
	 */
	var $pwd = false;

	/**
	 * Packet Type Log
	 *
	 * @see ssh2_sftp::get_log()
	 * @var Array
	 * @access private
	 */
	var $packet_type_log = array();

	/**
	 * Packet Log
	 *
	 * @see ssh2_sftp::get_log()
	 * @var Array
	 * @access private
	 */
	var $packet_log = array();

	/**
	 * Default Constructor.
	 *
	 * Connects to an SFTP server
	 *
	 * @param String $host
	 * @param optional Integer $port
	 * @param optional Integer $timeout
	 * @return sftp
	 * @access public
	 */
	function __construct($host, $port = 22, $timeout = 10)
	{
		parent::__construct($host, $port, $timeout);
		$this->packet_types = array(
			1  => 'NET_SFTP_INIT',
			2  => 'NET_SFTP_VERSION',
			/* the format of SSH_FXP_OPEN changed between SFTPv4 and SFTPv5+:
				   SFTPv5+: http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.1.1
			   pre-SFTPv5 : http://tools.ietf.org/html/draft-ietf-secsh-filexfer-04#section-6.3 */
			3  => 'NET_SFTP_OPEN',
			4  => 'NET_SFTP_CLOSE',
			5  => 'NET_SFTP_READ',
			6  => 'NET_SFTP_WRITE',
			8  => 'NET_SFTP_FSTAT',
			9  => 'NET_SFTP_SETSTAT',
			11 => 'NET_SFTP_OPENDIR',
			12 => 'NET_SFTP_READDIR',
			13 => 'NET_SFTP_REMOVE',
			14 => 'NET_SFTP_MKDIR',
			15 => 'NET_SFTP_RMDIR',
			16 => 'NET_SFTP_REALPATH',
			17 => 'NET_SFTP_STAT',
			/* the format of SSH_FXP_RENAME changed between SFTPv4 and SFTPv5+:
				   SFTPv5+: http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.3
			   pre-SFTPv5 : http://tools.ietf.org/html/draft-ietf-secsh-filexfer-04#section-6.5 */
			18 => 'NET_SFTP_RENAME',

			101=> 'NET_SFTP_STATUS',
			102=> 'NET_SFTP_HANDLE',
			/* the format of SSH_FXP_NAME changed between SFTPv3 and SFTPv4+:
				   SFTPv4+: http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-9.4
			   pre-SFTPv4 : http://tools.ietf.org/html/draft-ietf-secsh-filexfer-02#section-7 */
			103=> 'NET_SFTP_DATA',
			104=> 'NET_SFTP_NAME',
			105=> 'NET_SFTP_ATTRS',

			200=> 'NET_SFTP_EXTENDED'
		);
		$this->status_codes = array(
			0 => 'NET_SFTP_STATUS_OK',
			1 => 'NET_SFTP_STATUS_EOF'
		);
		// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-7.1
		// the order, in this case, matters quite a lot - see ssh2_sftp::_parse_attributes() to understand why
		$this->attributes = array(
			0x00000001 => 'NET_SFTP_ATTR_SIZE',
			0x00000002 => 'NET_SFTP_ATTR_UIDGID', // defined in SFTPv3, removed in SFTPv4+
			0x00000004 => 'NET_SFTP_ATTR_PERMISSIONS',
			0x00000008 => 'NET_SFTP_ATTR_ACCESSTIME',
			0x80000000 => 'NET_SFTP_ATTR_EXTENDED'
		);
		// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-04#section-6.3
		// the flag definitions change somewhat in SFTPv5+.  if SFTPv5+ support is added to this library, maybe name
		// the array for that $this->open5_flags and similarily alter the constant names.
		$this->open_flags = array(
			0x00000001 => 'NET_SFTP_OPEN_READ',
			0x00000002 => 'NET_SFTP_OPEN_WRITE',
			0x00000008 => 'NET_SFTP_OPEN_CREATE'
		);
		$this->_define_array(
			$this->packet_types,
			$this->status_codes,
			$this->attributes,
			$this->open_flags
		);
	}

	/**
	 * Login
	 *
	 * @param String $username
	 * @param optional String $password
	 * @return Boolean
	 * @access public
	 */
	function login($username, $password = '')
	{
		if (!parent::login($username, $password))
		{
			return false;
		}

		$packet = pack('CNa*N3',
			NET_SSH2_MSG_CHANNEL_OPEN, strlen('session'), 'session', $this->client_channel, $this->window_size, $this->packet_size);

		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		switch ($type)
		{
			case NET_SSH2_MSG_CHANNEL_OPEN_CONFIRMATION:
				$this->_string_shift($response, 4);
				list(, $this->server_channel) = unpack('N', $this->_string_shift($response, 4));
				break;
			case NET_SSH2_MSG_CHANNEL_OPEN_FAILURE:
				return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
		}

		$packet = pack('CNNa*CNa*',
			NET_SSH2_MSG_CHANNEL_REQUEST, $this->server_channel, strlen('subsystem'), 'subsystem', 1, strlen('sftp'), 'sftp');
		if (!$this->_send_binary_packet($packet))
		{
			return false;
		}

		$response = $this->_get_binary_packet();
		if ($response === false)
		{
			return false;
		}

		list(, $type) = unpack('C', $this->_string_shift($response, 1));

		switch ($type)
		{
			case NET_SSH2_MSG_CHANNEL_SUCCESS:
				break;
			case NET_SSH2_MSG_CHANNEL_FAILURE:
			default:
				return $this->_disconnect(NET_SSH2_DISCONNECT_BY_APPLICATION);
		}

		if (!$this->_send_sftp_packet(NET_SFTP_INIT, "\0\0\0\3"))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_VERSION)
		{
			return false;
		}

		list(, $this->version) = unpack('N', $this->_string_shift($response, 4));
		while (!empty($response))
		{
			list(, $length) = unpack('N', $this->_string_shift($response, 4));
			$key = $this->_string_shift($response, $length);
			list(, $length) = unpack('N', $this->_string_shift($response, 4));
			$value = $this->_string_shift($response, $length);
			$this->extensions[$key] = $value;
		}

		/*
		 SFTPv4+ defines a 'newline' extension.  SFTPv3 seems to have unofficial support for it via 'newline@vandyke.com',
		 however, I'm not sure what 'newline@vandyke.com' is supposed to do (the fact that it's unofficial means that it's
		 not in the official SFTPv3 specs) and 'newline@vandyke.com' / 'newline' are likely not drop-in substitutes for
		 one another due to the fact that 'newline' comes with a SSH_FXF_TEXT bitmask whereas it seems unlikely that
		 'newline@vandyke.com' would.
		*/
		/*
		if (isset($this->extensions['newline@vandyke.com']))
		{
			$this->extensions['newline'] = $this->extensions['newline@vandyke.com'];
			unset($this->extensions['newline@vandyke.com']);
		}
		*/

		$this->request_id = 1;

		/*
		 A Note on SFTPv4/5/6 support:
		 <http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-5.1> states the following:

		 "If the client wishes to interoperate with servers that support noncontiguous version
		  numbers it SHOULD send '3'"

		 Given that the server only sends its version number after the client has already done so, the above
		 seems to be suggesting that v3 should be the default version.  This makes sense given that v3 is the
		 most popular.

		 <http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-5.5> states the following;

		 "If the server did not send the "versions" extension, or the version-from-list was not included, the
		  server MAY send a status response describing the failure, but MUST then close the channel without
		  processing any further requests."

		 So what do you do if you have a client whose initial SSH_FXP_INIT packet says it implements v3 and
		 a server whose initial SSH_FXP_VERSION reply says it implements v4 and only v4?  If it only implements
		 v4, the "versions" extension is likely not going to have been sent so version re-negotiation as discussed
		 in draft-ietf-secsh-filexfer-13 would be quite impossible.  As such, what sftp would do is close the
		 channel and reopen it with a new and updated SSH_FXP_INIT packet.
		*/
		if ($this->version != 3)
		{
			return false;
		}

		$this->pwd = $this->_realpath('.');

		return true;
	}

	/**
	 * Returns the current directory name
	 *
	 * @return Mixed
	 * @access public
	 */
	function pwd()
	{
		return $this->pwd;
	}

	/**
	 * Canonicalize the Server-Side Path Name
	 *
	 * SFTP doesn't provide a mechanism by which the current working directory can be changed, so we'll emulate it.  Returns
	 * the absolute (canonicalized) path.  If $mode is set to NET_SFTP_CONFIRM_DIR (as opposed to NET_SFTP_CONFIRM_NONE,
	 * which is what it is set to by default), false is returned if $dir is not a valid directory.
	 *
	 * @see ssh2_sftp::chdir()
	 * @param String $dir
	 * @param optional Integer $mode
	 * @return Mixed
	 * @access private
	 */
	function _realpath($dir)
	{
		/*
		"This protocol represents file names as strings.  File names are
		 assumed to use the slash ('/') character as a directory separator.

		 File names starting with a slash are "absolute", and are relative to
		 the root of the file system.  Names starting with any other character
		 are relative to the user's default directory (home directory).  Note
		 that identifying the user is assumed to take place outside of this
		 protocol."

		 -- http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-6
		*/
		$file = '';
		if ($this->pwd !== false)
		{
			// if the SFTP server returned the canonicalized path even for non-existant files this wouldn't be necessary
			// on OpenSSH it isn't necessary but on other SFTP servers it is.  that and since the specs say nothing on
			// the subject, we'll go ahead and work around it with the following.
			if ($dir[strlen($dir) - 1] != '/')
			{
				$file = basename($dir);
				$dir = dirname($dir);
			}

			if ($dir == '.' || $dir == $this->pwd)
			{
				return $this->pwd . $file;
			}

			if ($dir[0] != '/')
			{
				$dir = $this->pwd . '/' . $dir;
			}
			// on the surface it seems like maybe resolving a path beginning with / is unnecessary, but such paths
			// can contain .'s and ..'s just like any other.  we could parse those out as appropriate or we can let
			// the server do it.  we'll do the latter.
		}

		/*
		 that SSH_FXP_REALPATH returns SSH_FXP_NAME does not necessarily mean that anything actually exists at the
		 specified path.  generally speaking, no attributes are returned with this particular SSH_FXP_NAME packet
		 regardless of whether or not a file actually exists.  and in SFTPv3, the longname field and the filename
		 field match for this particular SSH_FXP_NAME packet.  for other SSH_FXP_NAME packets, this will likely
		 not be the case, but for this one, it is.
		*/
		// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.9
		if (!$this->_send_sftp_packet(NET_SFTP_REALPATH, pack('Na*', strlen($dir), $dir)))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		switch ($this->packet_type)
		{
			case NET_SFTP_NAME:
				// although SSH_FXP_NAME is implemented differently in SFTPv3 than it is in SFTPv4+, the following
				// should work on all SFTP versions since the only part of the SSH_FXP_NAME packet the following looks
				// at is the first part and that part is defined the same in SFTP versions 3 through 6.
				$this->_string_shift($response, 4); // skip over the count - it should be 1, anyway
				list(, $length) = unpack('N', $this->_string_shift($response, 4));
				$realpath = $this->_string_shift($response, $length);
				break;
			case NET_SFTP_STATUS:
				// skip over the status code - hopefully the error message will give us all the info we need, anyway
				$this->_string_shift($response, 4);
				list(, $length) = unpack('N', $this->_string_shift($response, 4));
				$this->debug_info.= "\r\n\r\nSSH_FXP_STATUS:\r\n" . $this->_string_shift($response, $length);
				return false;
			default:
				return false;
		}

		// if $this->pwd isn't set than the only thing $realpath could be is for '.', which is pretty much guaranteed to
		// be a bonafide directory
		return $realpath . '/' . $file;
	}

	/**
	 * Changes the current directory
	 *
	 * @param String $dir
	 * @return Boolean
	 * @access public
	 */
	function chdir($dir)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		if ($dir[strlen($dir) - 1] != '/')
		{
			$dir.= '/';
		}
		$dir = $this->_realpath($dir);

		// confirm that $dir is, in fact, a valid directory
		if (!$this->_send_sftp_packet(NET_SFTP_OPENDIR, pack('Na*', strlen($dir), $dir)))
		{
			return false;
		}

		// see ssh2_sftp::nlist() for a more thorough explanation of the following
		$response = $this->_get_sftp_packet();
		switch ($this->packet_type)
		{
			case NET_SFTP_HANDLE:
				$handle = substr($response, 4);
				break;
			case NET_SFTP_STATUS:
				return false;
			default:
				return false;
		}

		if (!$this->_send_sftp_packet(NET_SFTP_CLOSE, pack('Na*', strlen($handle), $handle)))
		{
			return false;
		}

		$this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		$this->pwd = $dir;
		return true;
	}

	/**
	 * Returns a list of files in the given directory
	 *
	 * @param optional String $dir
	 * @return Mixed
	 * @access public
	 */
	function nlist($dir = '.')
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$dir = $this->_realpath($dir);
		if ($dir === false)
		{
			return false;
		}

		// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.1.2
		if (!$this->_send_sftp_packet(NET_SFTP_OPENDIR, pack('Na*', strlen($dir), $dir)))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		switch ($this->packet_type)
		{
			case NET_SFTP_HANDLE:
				// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-9.2
				// since 'handle' is the last field in the SSH_FXP_HANDLE packet, we'll just remove the first four bytes that
				// represent the length of the string and leave it at that
				$handle = substr($response, 4);
				break;
			case NET_SFTP_STATUS:
				// presumably SSH_FX_NO_SUCH_FILE or SSH_FX_PERMISSION_DENIED
				return false;
			default:
				return false;
		}

		$contents = array();
		while (true)
		{
			// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.2.2
			// why multiple SSH_FXP_READDIR packets would be sent when the response to a single one can span arbitrarily many
			// SSH_MSG_CHANNEL_DATA messages is not known to me.
			if (!$this->_send_sftp_packet(NET_SFTP_READDIR, pack('Na*', strlen($handle), $handle)))
			{
				return false;
			}

			$response = $this->_get_sftp_packet();
			switch ($this->packet_type)
			{
				case NET_SFTP_NAME:
					list(, $count) = unpack('N', $this->_string_shift($response, 4));
					for ($i = 0; $i < $count; $i++)
					{
						list(, $length) = unpack('N', $this->_string_shift($response, 4));
						$contents[] = $this->_string_shift($response, $length);
						list(, $length) = unpack('N', $this->_string_shift($response, 4));
						$this->_string_shift($response, $length); // we don't care about the longname
						$this->_parse_attributes($response); // we also don't care about the attributes
						// SFTPv6 has an optional boolean end-of-list field, but we'll ignore that, since the
						// final SSH_FXP_STATUS packet should tell us that, already.
					}
					break;
				case NET_SFTP_STATUS:
					list(, $status) = unpack('N', $this->_string_shift($response, 4));
					if ($status != NET_SFTP_STATUS_EOF)
					{
						list(, $length) = unpack('N', $this->_string_shift($response, 4));
						$this->debug_info.= "\r\n\r\nSSH_FXP_STATUS:\r\n" . $this->_string_shift($response, $length);
						return false;
					}
					break 2;
				default:
					return false;
			}
		}

		if (!$this->_send_sftp_packet(NET_SFTP_CLOSE, pack('Na*', strlen($handle), $handle)))
		{
			return false;
		}

		// "The client MUST release all resources associated with the handle regardless of the status."
		//  -- http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.1.3
		$this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		return $contents;
	}

	/**
	 * Set permissions on a file.
	 *
	 * Returns the new file permissions on success or FALSE on error.
	 *
	 * @param Integer $mode
	 * @param String $filename
	 * @return Mixed
	 * @access public
	 */
	function chmod($mode, $filename)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$filename = $this->_realpath($filename);
		if ($filename === false)
		{
			return false;
		}

		// SFTPv4+ has an additional byte field - type - that would need to be sent, as well. setting it to
		// SSH_FILEXFER_TYPE_UNKNOWN might work. if not, we'd have to do an SSH_FXP_STAT before doing an SSH_FXP_SETSTAT.
		$attr = pack('N2', NET_SFTP_ATTR_PERMISSIONS, $mode & 07777);
		if (!$this->_send_sftp_packet(NET_SFTP_SETSTAT, pack('Na*a*', strlen($filename), $filename, $attr)))
		{
			return false;
		}

		/*
		 "Because some systems must use separate system calls to set various attributes, it is possible that a failure 
		  response will be returned, but yet some of the attributes may be have been successfully modified.  If possible,
		  servers SHOULD avoid this situation; however, clients MUST be aware that this is possible."

		  -- http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.6
		*/
		$this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		// rather than return what the permissions *should* be, we'll return what they actually are.  this will also
		// tell us if the file actually exists.
		// incidentally ,SFTPv4+ adds an additional 32-bit integer field - flags - to the following:
		$packet = pack('Na*', strlen($filename), $filename);
		if (!$this->_send_sftp_packet(NET_SFTP_STAT, $packet))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		switch ($this->packet_type)
		{
			case NET_SFTP_ATTRS:
				$attrs = $this->_parse_attributes($response);
				return $attrs['permissions'];
			case NET_SFTP_STATUS:
				return false;
		}

		return false;
	}

	/**
	 * Creates a directory.
	 *
	 * @param String $dir
	 * @return Boolean
	 * @access public
	 */
	function mkdir($dir)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$dir = $this->_realpath(rtrim($dir, '/'));
		if ($dir === false)
		{
			return false;
		}

		// by not providing any permissions, hopefully the server will use the logged in users umask - their 
		// default permissions.
		if (!$this->_send_sftp_packet(NET_SFTP_MKDIR, pack('Na*N', strlen($dir), $dir, 0)))
		{
			return false;
		}

		$this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		list(, $status) = unpack('N', $this->_string_shift($response, 4));
		if ($status != NET_SFTP_STATUS_OK)
		{
			list(, $length) = unpack('N', $this->_string_shift($response, 4));
			$this->debug_info.= "\r\n\r\nSSH_FXP_STATUS:\r\n" . $this->_string_shift($response, $length);
			return false;
		}

		return true;
	}

	/**
	 * Removes a directory.
	 *
	 * @param String $dir
	 * @return Boolean
	 * @access public
	 */
	function rmdir($dir)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$dir = $this->_realpath($dir);
		if ($dir === false)
		{
			return false;
		}

		if (!$this->_send_sftp_packet(NET_SFTP_RMDIR, pack('Na*', strlen($dir), $dir)))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		list(, $status) = unpack('N', $this->_string_shift($response, 4));
		if ($status != NET_SFTP_STATUS_OK)
		{
			// presumably SSH_FX_NO_SUCH_FILE or SSH_FX_PERMISSION_DENIED?
			return false;
		}

		return true;
	}

	/**
	 * Uploads a file to the SFTP server.
	 *
	 * By default, ssh2_sftp::put() does not read from the local filesystem.  $data is dumped directly into $remote_file.
	 * So, for example, if you set $data to 'filename.ext' and then do ssh2_sftp::get(), you will get a file, twelve bytes
	 * long, containing 'filename.ext' as its contents.
	 *
	 * Setting $mode to NET_SFTP_LOCAL_FILE will change the above behavior.  With NET_SFTP_LOCAL_FILE, $remote_file will 
	 * contain as many bytes as filename.ext does on your local filesystem.  If your filename.ext is 1MB then that is how
	 * large $remote_file will be, as well.
	 *
	 * Currently, only binary mode is supported.  As such, if the line endings need to be adjusted, you will need to take
	 * care of that, yourself.
	 *
	 * @param String $remote_file
	 * @param String $data
	 * @param optional Integer $flags
	 * @return Boolean
	 * @access public
	 * @internal ASCII mode for SFTPv4/5/6 can be supported by adding a new function - ssh2_sftp::setMode().
	 */
	function put($remote_file, $data, $mode = NET_SFTP_STRING)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$remote_file = $this->_realpath($remote_file);
		if ($remote_file === false)
		{
			return false;
		}

		$packet = pack('Na*N2', strlen($remote_file), $remote_file, NET_SFTP_OPEN_WRITE | NET_SFTP_OPEN_CREATE, 0);
		if (!$this->_send_sftp_packet(NET_SFTP_OPEN, $packet))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		switch ($this->packet_type)
		{
			case NET_SFTP_HANDLE:
				$handle = substr($response, 4);
				break;
			case NET_SFTP_STATUS:
				$this->_string_shift($response, 4);
				list(, $length) = unpack('N', $this->_string_shift($response, 4));
				$this->debug_info.= "\r\n\r\nSSH_FXP_STATUS:\r\n" . $this->_string_shift($response, $length);
				return false;
			default:
				return false;
		}

		$initialize = true;

		// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.2.3
		if ($mode == NET_SFTP_LOCAL_FILE)
		{
			if (!is_file($data))
			{
				return false;
			}
			$fp = fopen($data, 'r');
			if (!$fp)
			{
				return false;
			}
			$sent = 0;
			$size = filesize($data);
			while ($sent < $size)
			{
				/*
				 "The 'maximum packet size' specifies the maximum size of an individual data packet that can be sent to the
				  sender.  For example, one might want to use smaller packets for interactive connections to get better
				  interactive response on slow links."

				 -- http://tools.ietf.org/html/rfc4254#section-5.1

				 per that, we're going to assume that the 'maximum packet size' field of the SSH_MSG_CHANNEL_OPEN message 
				 does not apply to the client.  the client is the one who sends the SSH_MSG_CHANNEL_OPEN message, anyway,
				 so it's not as if the above could be referring to the server.

				 the reason that's mentioned is because sending $this->packet_size as the payload will result in a packet
				 that's larger than $this->packet_size, but that's not a problem, as per the above.
				*/
				if ($initialize)
				{
					$temp = fread($fp, $this->packet_size);
					$sent+= strlen($temp);
					$packet = pack('NCN2a*N3a*',
						$size + strlen($handle) + 21, NET_SFTP_WRITE, $this->request_id, strlen($handle), $handle, 0, 0, $size, $temp
					);
					$initialize = false;
					if (defined('NET_SFTP_LOGGING'))
					{
						$log_index = count($this->packet_type_log);
						$this->packet_type_log[] = '<- ' . $this->packet_types[$packet_type];
						$this->packet_log[] = $packet;
					}
				}
				else
				{
					$packet = fread($fp, $this->packet_size);
					$sent+= strlen($packet);
					if (defined('NET_SFTP_LOGGING'))
					{
						$this->packet_log[$log_index].= $packet;
					}
				}
				if (!$this->_send_channel_packet($packet))
				{
					fclose($fp);
					return false;
				}
			}
			fclose($fp);
		}
		else
		{
			while (strlen($data))
			{
				if ($initialize)
				{
					$packet = pack('NCN2a*N3a*',
						strlen($data) + strlen($handle) + 21, NET_SFTP_WRITE, $this->request_id, strlen($handle), $handle, 0, 0, strlen($data),
						$this->_string_shift($data, $this->packet_size)
					);
					$initialize = false;
				}
				else
				{
					$packet = $this->_string_shift($data, $this->packet_size);
				}
				if (!$this->_send_channel_packet($packet))
				{
					return false;
				}
			}
		}

		$this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		if (!$this->_send_sftp_packet(NET_SFTP_CLOSE, pack('Na*', strlen($handle), $handle)))
		{
			return false;
		}

		$this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		return true;
	}

	/**
	 * Downloads a file from the SFTP server.
	 *
	 * Returns a string containing the contents of $remote_file if $local_file is left undefined or a boolean false if
	 * the operation was unsuccessful.  If $local_file is defined, returns true or false depending on the success of the
	 * operation
	 *
	 * @param String $remote_file
	 * @param optional String $local_file
	 * @return Mixed
	 * @access public
	 */
	function get($remote_file, $local_file = false)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$remote_file = $this->_realpath($remote_file);
		if ($remote_file === false)
		{
			return false;
		}

		$packet = pack('Na*N2', strlen($remote_file), $remote_file, NET_SFTP_OPEN_READ, 0);
		if (!$this->_send_sftp_packet(NET_SFTP_OPEN, $packet))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		switch ($this->packet_type)
		{
			case NET_SFTP_HANDLE:
				$handle = substr($response, 4);
				break;
			case NET_SFTP_STATUS: // presumably SSH_FX_NO_SUCH_FILE or SSH_FX_PERMISSION_DENIED
				return false;
			default:
				return false;
		}

		$packet = pack('Na*', strlen($handle), $handle);
		if (!$this->_send_sftp_packet(NET_SFTP_FSTAT, $packet))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		switch ($this->packet_type)
		{
			case NET_SFTP_ATTRS:
				$attrs = $this->_parse_attributes($response);
				break;
			case NET_SFTP_STATUS:
				return false;
			default:
				return false;
		}


		if ($local_file !== false)
		{
			$fp = fopen($local_file, 'w');
			if (!$fp)
			{
				return false;
			}
		}
		else
		{
			$content = '';
		}

		$read = 0;
		while ($read < $attrs['size'])
		{
			$packet = pack('Na*N3', strlen($handle), $handle, 0, $read, 100000); // 100000 is completely arbitrarily chosen
			if (!$this->_send_sftp_packet(NET_SFTP_READ, $packet))
			{
				return false;
			}

			$response = $this->_get_sftp_packet();
			switch ($this->packet_type)
			{
				case NET_SFTP_DATA:
					$temp = substr($response, 4);
					$read+= strlen($temp);
					if ($local_file === false)
					{
						$content.= $temp;
					}
					else
					{
						fputs($fp, $temp);
					}
					break;
				case NET_SFTP_STATUS:
					$this->_string_shift($response, 4);
					list(, $length) = unpack('N', $this->_string_shift($response, 4));
					$this->debug_info.= "\r\n\r\nSSH_FXP_STATUS:\r\n" . $this->_string_shift($response, $length);
					return false;
				default:
					return false;
			}
		}

		if (!$this->_send_sftp_packet(NET_SFTP_CLOSE, pack('Na*', strlen($handle), $handle)))
		{
			return false;
		}

		$this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		if (isset($content))
		{
			return $content;
		}

		fclose($fp);
		return true;
	}

	/**
	 * Deletes a file on the SFTP server.
	 *
	 * @param String $path
	 * @return Boolean
	 * @access public
	 */
	function delete($path)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$remote_file = $this->_realpath($path);
		if ($path === false)
		{
			return false;
		}

		// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.3
		if (!$this->_send_sftp_packet(NET_SFTP_REMOVE, pack('Na*', strlen($path), $path)))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		list(, $status) = unpack('N', $this->_string_shift($response, 4));

		// if $status isn't SSH_FX_OK it's probably SSH_FX_NO_SUCH_FILE or SSH_FX_PERMISSION_DENIED
		return $status == NET_SFTP_STATUS_OK;
	}

	/**
	 * Renames a file or a directory on the SFTP server
	 *
	 * @param String $oldname
	 * @param String $newname
	 * @return Boolean
	 * @access public
	 */
	function rename($oldname, $newname)
	{
		if (!($this->bitmap & NET_SSH2_MASK_LOGIN))
		{
			return false;
		}

		$oldname = $this->_realpath($oldname);
		$newname = $this->_realpath($newname);
		if ($oldname === false || $newname === false)
		{
			return false;
		}

		// http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-8.3
		$packet = pack('Na*Na*', strlen($oldname), $oldname, strlen($newname), $newname);
		if (!$this->_send_sftp_packet(NET_SFTP_RENAME, $packet))
		{
			return false;
		}

		$response = $this->_get_sftp_packet();
		if ($this->packet_type != NET_SFTP_STATUS)
		{
			return false;
		}

		list(, $status) = unpack('N', $this->_string_shift($response, 4));

		// if $status isn't SSH_FX_OK it's probably SSH_FX_NO_SUCH_FILE or SSH_FX_PERMISSION_DENIED
		return $status == NET_SFTP_STATUS_OK;
	}

	/**
	 * Parse Attributes
	 *
	 * See '7.  File Attributes' of draft-ietf-secsh-filexfer-13 for more info.
	 *
	 * @param String $response
	 * @return Array
	 * @access private
	 */
	function _parse_attributes(&$response)
	{
		$attr = array();
		list(, $flags) = unpack('N', $this->_string_shift($response, 4));
		// SFTPv4+ have a type field (a byte) that follows the above flag field
		foreach ($this->attributes as $key => $value)
		{
			switch ($flags & $key)
			{
				case NET_SFTP_ATTR_SIZE: // 0x00000001
					// size is represented by a 64-bit integer, so we perhaps ought to be doing the following:
					//$attr['size'] = new Math_BigInteger($this->_string_shift($response, 8), 256);
					// of course, you shouldn't be using sftp to transfer files that are in excess of 4GB
					// (0xFFFFFFFF bytes), anyway.  as such, we'll just represent all file sizes that are bigger than
					// 4GB as being 4GB.
					list(, $upper) = unpack('N', $this->_string_shift($response, 4));
					list(, $attr['size']) = unpack('N', $this->_string_shift($response, 4));
					if ($upper)
					{
						$attr['size'] = 0xFFFFFFFF;
					}
					break;
				case NET_SFTP_ATTR_UIDGID: // 0x00000002 (SFTPv3 only)
					list(, $attr['uid']) = unpack('N', $this->_string_shift($response, 4));
					list(, $attr['gid']) = unpack('N', $this->_string_shift($response, 4));
					break;
				case NET_SFTP_ATTR_PERMISSIONS: // 0x00000004
					list(, $attr['permissions']) = unpack('N', $this->_string_shift($response, 4));
					break;
				case NET_SFTP_ATTR_ACCESSTIME: // 0x00000008
					list(, $attr['atime']) = unpack('N', $this->_string_shift($response, 4));
					list(, $attr['mtime']) = unpack('N', $this->_string_shift($response, 4));
					break;
				case NET_SFTP_ATTR_EXTENDED: // 0x80000000
					list(, $count) = unpack('N', $this->_string_shift($response, 4));
					for ($i = 0; $i < $count; $i++)
					{
						list(, $length) = unpack('N', $this->_string_shift($response, 4));
						$key = $this->_string_shift($response, $length);
						list(, $length) = unpack('N', $this->_string_shift($response, 4));
						$attr[$key] = $this->_string_shift($response, $length);						
					}
			}
		}
		return $attr;
	}

	/**
	 * Sends SFTP Packets
	 *
	 * See '6. General Packet Format' of draft-ietf-secsh-filexfer-13 for more info.
	 *
	 * @param Integer $type
	 * @param String $data
	 * @see ssh2_sftp::_get_sftp_packet()
	 * @see ssh2_sftp::_send_channel_packet()
	 * @return Boolean
	 * @access private
	 */
	function _send_sftp_packet($type, $data)
	{
		$data = $this->request_id !== false ?
			pack('NCNa*', strlen($data) + 5, $type, $this->request_id, $data) :
			pack('NCa*',  strlen($data) + 1, $type, $data);

		if (defined('NET_SFTP_LOGGING'))
		{
			$this->packet_type_log[] = '-> ' . $this->packet_types[$type];
			$this->packet_log[] = $data;
		}

		return $this->_send_channel_packet($data);
	}

	/**
	 * Sends Channel Packets
	 *
	 * If this function were in Net_SSH2, there'd have to be an additional server channel parameter since the Net_SSH2 object
	 * doesn't currently have one.  Plus, it wouldn't actually make a lot of sense for Net_SSH2 even to have one - if it did
	 * and if Net_SSH2::exec() used it then ssh2_sftp::exec() would also use it (through inheritance) and you'd have the
	 * single server channel being overwritten and...  all in all, I think this is easier.
	 *
	 * @param Integer $type
	 * @param String $data
	 * @see ssh2_sftp::_send_sftp_packet()
	 * @return Boolean
	 * @access private
	 */
	function _send_channel_packet($data)
	{
		return $this->_send_binary_packet(pack('CN2a*', NET_SSH2_MSG_CHANNEL_DATA, $this->server_channel, strlen($data), $data));
	}

	/**
	 * Receives SFTP Packets
	 *
	 * See '6. General Packet Format' of draft-ietf-secsh-filexfer-13 for more info.
	 *
	 * @see ssh2_sftp::_send_sftp_packet()
	 * @return String
	 * @access private
	 */
	function _get_sftp_packet()
	{
		$packet = $this->_get_channel_packet();
		if (is_bool($packet))
		{
			$this->packet_type = false;
			return false;
		}
		/*
		 normally, strlen($packet) == $length, however, for really large packets, strlen($packet) < $length. what this means,
		 when it happens, is that the string is being spanned out across multiple SSH_MSG_CHANNEL_DATA messages and we'll
		 need to read them multiple times until we reach $length bytes.

		 presumably, strlen($packet) > $length would never happen unless you were trying to employ steganography or
		 something
		*/
		list(, $length) = unpack('N', $this->_string_shift($packet, 4));
		$this->packet_type = ord($this->_string_shift($packet));
		if ($this->request_id !== false)
		{
			$this->_string_shift($packet, 4); // remove the request id
			$length-= 5; // account for the request id and the packet type
		}
		else
		{
			$length-= 1; // account for the packet type
		}
		$packet = substr($packet, 0, $length); // just in case strlen($packet) > $length
		$length-= strlen($packet);
		while ($length > 0)
		{
			$temp = $this->_get_channel_packet();
			if (is_bool($temp))
			{
				$this->packet_type = false;
				return false;
			}
			$packet.= $temp;
			$length-= strlen($temp);
		}

		if (defined('NET_SFTP_LOGGING'))
		{
			$this->packet_type_log[] = '<- ' . $this->packet_types[$this->packet_type];
			$this->packet_log[] = $packet;
		}

		return $packet;
	}

	/**
	 * Returns a log of the packets that have been sent and received.
	 *
	 * $type can be either NET_SFTP_LOG_SIMPLE or NET_SFTP_LOG_COMPLEX.  Enable by defining NET_SSH2_LOGGING.
	 *
	 * @param Integer $type
	 * @access public
	 * @return String or Array
	 */
	function get_sftp_log($type = NET_SFTP_LOG_COMPLEX)
	{
		$message_number_log = $this->message_number_log;
		$message_log = $this->message_log;

		$this->message_number_log = $this->packet_type_log;
		$this->message_log = $this->packet_log;

		$return = $this->getLog($type);

		$this->message_number_log = $message_number_log;
		$this->message_log = $message_log;

		return $return;
	}

	/**
	 * Get supported SFTP versions
	 *
	 * @return Array
	 * @access public
	 */
	function get_supported_versions()
	{
		$temp = array('version' => $this->version);
		if (isset($this->extensions['versions']))
		{
			$temp['extensions'] = $this->extensions['versions'];
		}
		return $temp;
	}

	/**
	 * Disconnect
	 *
	 * @param Integer $reason
	 * @return Boolean
	 * @access private
	 */
	function _disconnect($reason)
	{
		$this->pwd = false;
		parent::_disconnect($reason);
	}
}

?>