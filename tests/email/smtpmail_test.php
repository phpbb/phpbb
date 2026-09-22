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
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\messenger\method\email;
use phpbb\messenger\queue;
use phpbb\path_helper;
use phpbb\symfony_request;
use phpbb\template\assets_bag;

class phpbb_email_smtpmail_test extends phpbb_test_case
{
	/** @var array Servers started by start_smtp_server() */
	protected $smtp_servers = [];

	/** @var string */
	protected $cache_path;

	/** @var config */
	protected $config;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var string */
	protected $email_templates_path;

	/** @var language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var email */
	protected $method_email;

	/** @var path_helper */
	protected $path_helper;

	/** @var queue */
	protected $queue;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\di\service_collection */
	protected $twig_extensions_collection;

	/** @var \phpbb\template\twig\lexer */
	protected $twig_lexer;

	/** @var \phpbb\user */
	protected $user;

	protected function setUp(): void
	{
		global $config, $request, $symfony_request, $user, $phpbb_root_path, $phpEx;

		$this->config = new config([
			'force_server_vars'			=> false,
			// Sending via the queue silently drops the email and would make these tests useless.
			'email_package_size'		=> 0,
			'smtp_delivery'				=> true,
			'smtp_host' 				=> '127.0.0.1',
			'smtp_port' 				=> 25,
			'smtp_username' 			=> '',
			'smtp_password' 			=> '',
			'smtp_verify_peer' 			=> true,
			'smtp_verify_peer_name'		=> true,
			'smtp_allow_self_signed'	=> true,
			'board_email' 				=> 'nobody@example.com',
			'board_contact'				=> 'nobody@example.com',
			'board_contact_name'		=> '',
			'board_email_sig'			=> '-- Thanks, The Management',
			'sitename'					=> 'yourdomain.com',
			'default_lang'				=> 'en',
		]);
		$config = $this->config;

		$this->cache_path = $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/twig';
		$this->email_templates_path = __DIR__ . '/templates';
		$this->smtp_servers = [];

		$this->dispatcher = $this->getMockBuilder('\phpbb\event\dispatcher')
			->disableOriginalConstructor()
			->getMock();
		$this->dispatcher->method('trigger_event')
			->willReturnCallback(function($event_name, $value_array) {
				return $value_array;
			});

		$this->language = new language(new language_file_loader($phpbb_root_path, $phpEx));
		$this->queue = $this->createMock(queue::class);

		$this->request = new phpbb_mock_request;
		$request = $this->request;
		$symfony_request = new symfony_request(new phpbb_mock_request);

		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$user = $this->user;
		$user->page['root_script_path'] = 'phpbb/';
		$this->user->host = 'yourdomain.com';

		// Data required by \phpbb\messenger\method\base::error()
		$this->user->data['user_id'] = 2;
		$this->user->session_id = 'abcdef';
		$this->user->ip = '127.0.0.1';

		$this->path_helper = new path_helper(
			$symfony_request,
			$this->request,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_container = new phpbb_mock_container_builder;
		$this->twig_extensions_collection = new \phpbb\di\service_collection($phpbb_container);
		$assets_bag = new assets_bag();
		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$this->config,
			new \phpbb\filesystem\filesystem(),
			$this->path_helper,
			$this->cache_path,
			null,
			new \phpbb\template\twig\loader(''),
			$this->dispatcher,
			[
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			]
		);
		$this->twig_lexer = new \phpbb\template\twig\lexer($twig);
		$this->log = $this->createMock(\phpbb\log\log_interface::class);

		$this->method_email = new email(
			$assets_bag,
			$this->config,
			$this->dispatcher,
			$this->language,
			$this->queue,
			$this->path_helper,
			$this->request,
			$this->twig_extensions_collection,
			$this->twig_lexer,
			$this->user,
			$phpbb_root_path,
			$this->cache_path,
			new phpbb_mock_extension_manager(
				__DIR__ . '/',
				[
					'vendor2/foo' => [
						'ext_name'	=> 'vendor2/foo',
						'ext_active'	=> '1',
						'ext_path'	=> 'ext/vendor2/foo/',
					],
				]
			),
			$this->log
		);
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
	 * by the Symfony Mailer SmtpTransport and logs every line the client sends
	 * to a temporary file.
	 *
	 * @param array $options	Server behaviour options
	 *							greeting			Banner sent on connect
	 *							reject_pattern		Regex matched against RCPT TO lines that get a 550 response
	 *							advertise_auth		Whether to advertise AUTH PLAIN via EHLO
	 * @return array			Array containing the child process id, the log file path and the port
	 */
	protected function start_smtp_server(array $options = [])
	{
		if (!function_exists('pcntl_fork'))
		{
			$this->markTestSkipped('The pcntl extension is not available.');
		}

		$options = array_merge([
			'greeting'			=> '220 test.example.com ESMTP ready',
			'reject_pattern'	=> null,
			'advertise_auth'	=> false,
		], $options);

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

		$this->smtp_servers[] = [
			'pid'		=> $pid,
			'log_file'	=> $log_file,
		];

		return [$pid, $log_file, $port];
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
		$this->shutdown_transport();

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
	 * Point the Symfony Mailer transport at the scripted SMTP server.
	 *
	 * @param int	$port	Port the scripted SMTP server is listening on
	 * @param string $dsn	Optional userinfo part for the DSN (e.g. testuser:secret)
	 */
	protected function connect_transport($port, $dsn = '')
	{
		$credentials = ($dsn) ? $dsn . '@' : '';
		$this->method_email->set_dsn("smtp://$credentials" . "127.0.0.1:$port");
		$this->method_email->set_transport();
	}

	/**
	 * Close the connection kept open by the Symfony Mailer transport.
	 *
	 * A QUIT command is sent first if the session was started, otherwise the
	 * underlying stream is terminated, so that the scripted SMTP server sees
	 * the end of the client dialogue and can be collected.
	 */
	protected function shutdown_transport()
	{
		$transport = $this->method_email->get_transport();

		if (method_exists($transport, 'stop'))
		{
			try
			{
				$transport->stop();
			}
			catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e)
			{
				// The server may already have closed the connection
			}
		}

		if (method_exists($transport, 'getStream'))
		{
			$transport->getStream()->terminate();
		}
	}

	/**
	 * Capture the error messages written to the log by the mailer.
	 *
	 * @param array $errors	Array the messages are appended to
	 */
	protected function register_error_log(&$errors)
	{
		$this->log->method('add')
			->willReturnCallback(function($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = []) use (&$errors) {
				$errors[] = $additional_data[0];
			});
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

		stream_set_timeout($conn, 2);
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
				$auth = ($options['advertise_auth']) ? '250 AUTH PLAIN' : '250 OK';
				@fwrite($conn, "250-test.example.com\r\n" . $auth . "\r\n");
			}
			elseif (preg_match('#^HELO#i', $cmd))
			{
				@fwrite($conn, "250 test.example.com\r\n");
			}
			elseif (preg_match('#^AUTH#i', $cmd))
			{
				// AUTH PLAIN <base64> authenticates in a single command
				if (count(preg_split('#\s+#', $cmd, 3)) === 3)
				{
					@fwrite($conn, "235 2.7.0 Authentication successful\r\n");
				}
				else
				{
					$expect_auth_data = true;
					@fwrite($conn, "334 \r\n");
				}
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

	public static function empty_subject_data(): array
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
		[$pid, $log_file, $port] = $this->start_smtp_server();

		$this->method_email->init();
		$this->connect_transport($port);

		$this->method_email->to('test@example.com');
		$this->method_email->subject($subject);
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$result = $this->method_email->send();
		$log = $this->stop_smtp_server($pid, $log_file);

		// An empty subject no longer causes an error, the mailer falls back
		// to the language string for the missing subject.
		$this->assertTrue($result);
		$this->assertStringContainsString('Subject: ' . $this->language->lang('NO_EMAIL_SUBJECT'), $log);
	}

	public function test_connect_failure()
	{
		// Reserve an ephemeral port and close it again so that connecting to it fails immediately.
		$server = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
		$this->assertNotFalse($server, 'Unable to create test socket: ' . $errstr);
		$port = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);
		fclose($server);

		$this->method_email->init();
		$this->connect_transport($port);

		$this->method_email->to('test@example.com');
		$this->method_email->subject('A subject');
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$errors = [];
		$this->register_error_log($errors);

		$this->assertFalse($this->method_email->send());
		$this->assertNotEmpty($errors);
		$this->assertStringContainsString('EMAIL', $errors[0]);
	}

	public function test_greeting_failure()
	{
		list($pid, $log_file, $port) = $this->start_smtp_server(['greeting' => '554 No SMTP service here']);

		$this->method_email->init();
		$this->connect_transport($port);

		$this->method_email->to('test@example.com');
		$this->method_email->subject('A subject');
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$errors = [];
		$this->register_error_log($errors);

		$result = $this->method_email->send();
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertFalse($result);
		$this->assertNotEmpty($errors);
		$this->assertStringContainsString('554', $errors[0]);
	}

	public function test_send_mail()
	{
		[$pid, $log_file, $port] = $this->start_smtp_server();

		$this->method_email->init();
		$this->connect_transport($port);

		$this->method_email->to('john@example.com', 'John');
		$this->method_email->to('tim@example.com');
		$this->method_email->bcc('secret@example.com', 'Secret');
		$this->method_email->cc('copy@example.com', 'Copy');
		$this->method_email->reply_to('test@example.com');
		$this->method_email->header('X-Custom', 'yes');
		$this->method_email->subject('Test subject');
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$result = $this->method_email->send();
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertTrue($result);

		$this->assertStringContainsString('EHLO ', $log);
		$this->assertStringContainsString('MAIL FROM:<nobody@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<john@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<tim@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<copy@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<secret@example.com>', $log);

		$this->assertStringContainsString('DATA', $log);
		$this->assertStringContainsString('Subject: Test subject', $log);
		$this->assertStringContainsString('John <john@example.com>', $log);
		$this->assertStringContainsString('Cc: Copy <copy@example.com>', $log);

		$this->assertStringContainsString('X-Custom: yes', $log);
		$this->assertStringContainsString('Reply-To: test@example.com', $log);
		$this->assertStringNotContainsString('Bcc:', $log);
		$this->assertStringNotContainsString('Secret', $log);

		// Bare line feeds are converted to CRLF and leading dots are escaped
		$this->assertStringContainsString("First line\r\nSecond line\r\n..dot begins\r\nFourth", $log);

		// The message is terminated by a lone dot followed by QUIT
		$this->assertStringContainsString(".\r\nQUIT", $log);
	}

	public function test_custom_headers()
	{
		[$pid, $log_file, $port] = $this->start_smtp_server();

		$this->method_email->init();
		$this->connect_transport($port);

		$this->method_email->to('test@example.com');
		$this->method_email->header('X-String-Header', 'ok');
		$this->method_email->subject('A subject');
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$result = $this->method_email->send();
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertTrue($result);
		$this->assertStringContainsString('X-String-Header: ok', $log);
	}

	public function test_rcpt_rejected_returns_error()
	{
		[$pid, $log_file, $port] = $this->start_smtp_server(['reject_pattern' => '#reject@#']);

		$this->method_email->init();
		$this->connect_transport($port);

		$this->method_email->to('good@example.com');
		$this->method_email->to('reject@example.com');
		$this->method_email->subject('A subject');
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$errors = [];
		$this->register_error_log($errors);

		$result = $this->method_email->send();
		$log = $this->stop_smtp_server($pid, $log_file);

		// A single rejected recipient now aborts the whole message.
		$this->assertFalse($result);
		$this->assertNotEmpty($errors);
		$this->assertStringContainsString('RCPT TO:<good@example.com>', $log);
		$this->assertStringContainsString('RCPT TO:<reject@example.com>', $log);
		$this->assertStringContainsString('550', $errors[0]);
	}

	public function test_all_rcpt_rejected_returns_error()
	{
		[$pid, $log_file, $port] = $this->start_smtp_server(['reject_pattern' => '#test@example.com#']);

		$this->method_email->init();
		$this->connect_transport($port);

		$this->method_email->to('test@example.com');
		$this->method_email->subject('A subject');
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$errors = [];
		$this->register_error_log($errors);

		$result = $this->method_email->send();
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertFalse($result);
		$this->assertNotEmpty($errors);
		$this->assertStringContainsString('RCPT TO:<test@example.com>', $log);
		$this->assertStringContainsString('550', $errors[0]);
	}

	public function test_smtp_auth_plain()
	{
		[$pid, $log_file, $port] = $this->start_smtp_server(['advertise_auth' => true]);

		$this->method_email->init();
		$this->connect_transport($port, 'testuser:testpass');

		$this->method_email->to('test@example.com');
		$this->method_email->subject('A subject');
		$this->method_email->template('smtp_body', 'en', $this->email_templates_path);

		$result = $this->method_email->send();
		$log = $this->stop_smtp_server($pid, $log_file);

		$this->assertTrue($result);
		$this->assertStringContainsString('AUTH PLAIN', $log);
		$this->assertStringContainsString(base64_encode("testuser\0testuser\0testpass"), $log);
	}
}
