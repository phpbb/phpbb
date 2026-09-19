<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

use phpbb\config\config;

class phpbb_email_smtpmail_test extends phpbb_test_case
{
	/** @var array Servers started by start_smtp_server() */
	protected $smtp_servers = [];

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx, $config, $user;

		if (!function_exists('smtpmail'))
		{
			include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		}

		$config = new config([
			'smtp_host' 				=> '127.0.0.1',
			'smtp_port' 				=> 25,
			'smtp_username' 			=> '',
			'smtp_password' 			=> '',
			'smtp_auth_method' 			=> 'PLAIN',
			'smtp_verify_peer' 			=> true,
			'smtp_verify_peer_name'		=> true,
			'smtp_allow_self_signed'	=> true,
			'board_email' 				=> 'nobody@example.com',
		]);

		$user = new phpbb_mock_user;

		$this->smtp_servers = [];
	}

	protected function tearDown(): void
	{
		foreach ($this->smtp_servers as $server)
		{
			// Reap any child process that has not been collected by stop_smtp_server() yet.
			if (pcntl_waitpid($server['pid'], $status, WNOHANG) === 0)
			{
				pcntl_waitpid($server['pid'], $status);
			}

			if (file_exists($server['log_file']))
			{
				@unlink($server['log_file']);
			}
		}

		parent::tearDown();
	}

	/**
	 * Start a scripted SMTP server in a forked child process.
	 *
	 * The child accepts a single connection, responds to the SMTP commands sent
	 * by smtpmail() and logs every line the client sends to a temporary file.
	 *
	 * @param array $options	Server behaviour options
	 *							greeting			Banner sent on connect
	 *							reject_pattern		Regex matched against RCPT TO lines that get a 550 response
	 *							advertise_auth		Whether to advertise AUTH PLAIN LOGIN via EHLO
	 * @return array			Array containing the child process id and the log file path
	 */
	protected function start_smtp_server(array $options = array())
	{
		global $config;

		if (!function_exists('pcntl_fork'))
		{
			$this->markTestSkipped('The pcntl extension is not available.');
		}

		$options = array_merge(array(
			'greeting'		=> '220 test.example.com ESMTP ready',
			'reject_pattern'	=> null,
			'advertise_auth'	=> false,
		), $options);

		$server = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
		$this->assertNotFalse($server, 'Unable to create test SMTP server socket: ' . $errstr);

		$port = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);
		$log_file = tempnam(sys_get_temp_dir(), 'phpbb_smtp_');

		$pid = pcntl_fork();

		if ($pid === -1)
		{
			@fclose($server);
			@unlink($log_file);
			$this->markTestSkipped('Unable to fork a child process for the test SMTP server.');
		}

		if ($pid === 0)
		{
			$this->run_smtp_server($server, $log_file, $options);
			exit(0);
		}

		$config['smtp_host'] = '127.0.0.1';
		$config['smtp_port'] = $port;

		$this->smtp_servers[] = array(
			'pid'		=> $pid,
			'log_file'	=> $log_file,
		);

		return array($pid, $log_file);
	}

	/**
	 * Wait for the forked SMTP server to finish and return the captured
	 * client dialogue.
	 *
	 * @param int	$pid		Child process id returned by start_smtp_server()
	 * @param string $log_file	Log file path returned by start_smtp_server()
	 * @return string			Every line sent by the client to the server
	 */
	protected function stop_smtp_server($pid, $log_file)
	{
		pcntl_waitpid($pid, $status);
		$log = file_get_contents($log_file);
		@unlink($log_file);

		foreach ($this->smtp_servers as $key => $server)
		{
			if ($server['pid'] === $pid)
			{
				unset($this->smtp_servers[$key]);
			}
		}

		return $log;
	}

	/**
	 * SMTP server code running in the forked child process.
	 *
	 * @param resource	$server		Listening socket created by the parent process
	 * @param string	$log_file	File to write every received client line to
	 * @param array		$options	Server behaviour options gathered in start_smtp_server()
	 */
	protected function run_smtp_server($server, $log_file, array $options)
	{
		$conn = @stream_socket_accept($server, 5);

		if (!$conn)
		{
			return;
		}

		$log = @fopen($log_file, 'wb');
		@fwrite($conn, $options['greeting'] . "\r\n");

		$in_data = false;
		$expect_auth_data = false;

		while (($line = fgets($conn, 4096)) !== false)
		{
			@fwrite($log, $line);
			$cmd = trim($line);

			if ($in_data)
			{
				if ($cmd === '.')
				{
					@fwrite($conn, "250 2.0.0 OK: queued\r\n");
					$in_data = false;
				}
				continue;
			}

			if ($expect_auth_data)
			{
				$expect_auth_data = false;
				@fwrite($conn, "235 2.7.0 Authentication successful\r\n");
			}
			elseif (preg_match('#^EHLO#i', $cmd))
			{
				$auth = ($options['advertise_auth']) ? '250 AUTH PLAIN LOGIN' : '250 OK';
				@fwrite($conn, "250-test.example.com\r\n" . $auth . "\r\n");
			}
			elseif (preg_match('#^HELO#i', $cmd))
			{
				@fwrite($conn, "250 test.example.com\r\n");
			}
			elseif (preg_match('#^AUTH#i', $cmd))
			{
				$expect_auth_data = true;
				@fwrite($conn, "334 \r\n");
			}
			elseif (preg_match('#^MAIL FROM:#i', $cmd))
			{
				@fwrite($conn, "250 2.1.0 OK\r\n");
			}
			elseif (preg_match('#^RCPT TO:#i', $cmd))
			{
				if ($options['reject_pattern'] && preg_match($options['reject_pattern'], $cmd))
				{
					@fwrite($conn, "550 5.1.1 No such user\r\n");
				}
				else
				{
					@fwrite($conn, "250 2.1.5 OK\r\n");
				}
			}
			elseif (preg_match('#^DATA#i', $cmd))
			{
				$in_data = true;
				@fwrite($conn, "354 End data with <CR><LF>.<CR><LF>\r\n");
			}
			elseif (preg_match('#^QUIT#i', $cmd))
			{
				@fwrite($conn, "221 2.0.0 Bye\r\n");
				break;
			}
			else
			{
				@fwrite($conn, "250 OK\r\n");
			}
		}

		if ($log)
		{
			@fclose($log);
		}

		@fclose($conn);
		@fclose($server);
	}

	public function empty_subject_data(): array
	{
		return [
			[''],
			['   '],
			["\t\n"],
		];
	}

	/**
	 * @dataProvider empty_subject_data
	 */
	public function test_empty_subject($subject)
	{
		$addresses = [
			'to' => [
				['email' => 'test@example.com', 'name' => ''],
			],
		];

		$err_msg = '';

		$this->assertFalse(smtpmail($addresses, $subject, 'Email message', $err_msg));
		$this->assertStringContainsString('No email subject specified', $err_msg);
	}

	public function test_empty_message()
	{
		$addresses = [
			'to' => [
				['email' => 'test@example.com', 'name' => ''],
			],
		];

		$err_msg = '';

		$this->assertFalse(smtpmail($addresses, 'A subject', '   ', $err_msg));
		$this->assertStringContainsString('Email message was blank', $err_msg);
	}

	public function test_connect_failure()
	{
		global $config;

		// Reserve an ephemeral port and close it again so that connecting to it fails immediately.
		$server = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
		$this->assertNotFalse($server, 'Unable to create test socket: ' . $errstr);
		$config['smtp_port'] = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);
		fclose($server);

		$addresses = [
			'to' => [
				['email' => 'test@example.com', 'name' => ''],
			],
		];

		$err_msg = '';

		$this->assertFalse(smtpmail($addresses, 'A subject', 'A message', $err_msg));
		$this->assertStringContainsString('Could not connect to smtp host', $err_msg);
	}

	public function test_greeting_failure()
	{
		list($pid, $log_file) = $this->start_smtp_server(['greeting' => '554 No SMTP service here']);

		$addresses = [
			'to' => [
				['email' => 'test@example.com', 'name' => ''],
			],
		];

		$err_msg = '';

		$result = smtpmail($addresses, 'A subject', 'A message', $err_msg);
		$this->stop_smtp_server($pid, $log_file);

		$this->assertFalse($result);
		$this->assertStringContainsString('Ran into problems sending Mail', $err_msg);
	}

	public function test_send_mail()
	{
		list($pid, $log_file) = $this->start_smtp_server();

		$addresses = [
			'to' => [
				['email' => 'john@example.com', 'name' => 'John'],
				['email' => 'tim@example.com', 'name' => ''],
			],
			'bcc' => [
				['email' => 'secret@example.com', 'name' => 'Secret'],
			],
			'cc' => [
				['email' => 'copy@example.com', 'name' => 'Copy'],
			],
		];

		$headers = [
			'From: Test <test@example.com>',
			'Reply-To: test@example.com',
			'Cc: cc-strip@example.com',
			'Bcc: bcc-strip@example.com',
			'X-Custom: yes',
		];

		$err_msg = '';

		$result = smtpmail($addresses, 'Test subject', "First line\nSecond line\n.dot begins\nFourth", $err_msg, $headers);
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertTrue($result);

		$this->assertStringContainsString('EHLO ', $log);
		$this->assertStringContainsString('MAIL FROM:<nobody@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<john@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<tim@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<secret@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<copy@example.com>', $log);

		$this->assertStringContainsString('DATA', $log);
		$this->assertStringContainsString('Subject: Test subject', $log);
		$this->assertStringContainsString('=?US-ASCII?Q?John?= <john@example.com>', $log);
		$this->assertStringContainsString('CC: =?US-ASCII?Q?Copy?= <copy@example.com>', $log);

		$this->assertStringContainsString('X-Custom: yes', $log);
		$this->assertStringContainsString('Reply-To: test@example.com', $log);
		$this->assertStringNotContainsString('Bcc:', $log);
		$this->assertStringNotContainsString('cc-strip@example.com', $log);
		$this->assertStringNotContainsString('bcc-strip@example.com', $log);

		// Bare line feeds are converted to CRLF and leading dots are escaped
		$this->assertStringContainsString("Second line\r\n..dot begins\r\nFourth", $log);

		// The message is terminated by a lone dot followed by QUIT
		$this->assertStringContainsString(".\r\nQUIT", $log);
	}

	public function test_send_mail_with_string_headers()
	{
		list($pid, $log_file) = $this->start_smtp_server();

		$addresses = [
			'to' => [
				['email' => 'test@example.com', 'name' => ''],
			],
		];

		$headers = "From: Test <test@example.com>\nBcc: should-not-appear@example.com\nX-String-Header: ok";

		$err_msg = '';

		$result = smtpmail($addresses, 'A subject', 'A message', $err_msg, $headers);
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertTrue($result);
		$this->assertStringContainsString('X-String-Header: ok', $log);
		$this->assertStringNotContainsString('should-not-appear@example.com', $log);
	}

	public function test_rcpt_550_does_not_abort()
	{
		list($pid, $log_file) = $this->start_smtp_server(['reject_pattern' => '#reject@#']);

		$addresses = [
			'to' => [
				['email' => 'good@example.com', 'name' => ''],
				['email' => 'reject@example.com', 'name' => ''],
			],
		];

		$err_msg = '';

		$result = smtpmail($addresses, 'A subject', 'A message', $err_msg);
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertTrue($result);
		$this->assertStringContainsString('RCPT TO:<good@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<reject@example.com>', $log);
	}

	public function test_all_rcpt_rejected_returns_error()
	{
		global $user;

		$user = $this->getMockBuilder('phpbb_mock_user')
			->setMethods(['session_begin'])
			->getMock();

		list($pid, $log_file) = $this->start_smtp_server(['reject_pattern' => '#test@example.com#']);

		$addresses = [
			'to' => [
				['email' => 'test@example.com', 'name' => ''],
			],
		];

		$err_msg = '';

		$result = smtpmail($addresses, 'A subject', 'A message', $err_msg);
		$this->stop_smtp_server($pid, $log_file);

		$this->assertFalse($result);
		$this->assertStringContainsString('possibly an invalid email address', $err_msg);
	}

	public function test_smtp_auth_plain()
	{
		global $config;

		$config['smtp_username'] = 'testuser';
		$config['smtp_password'] = 'testpass';

		list($pid, $log_file) = $this->start_smtp_server(['advertise_auth' => true]);

		$addresses = [
			'to' => [
				['email' => 'test@example.com', 'name' => ''],
			],
		];

		$err_msg = '';

		$result = smtpmail($addresses, 'A subject', 'A message', $err_msg);
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertTrue($result);
		$this->assertStringContainsString('AUTH PLAIN', $log);
		$this->assertStringContainsString(base64_encode("\0testuser\0testpass"), $log);
	}
}
