<?php
/**
 *
 * @package phpBB3
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * Simple and fast prototype to test the API
 *
 */

class client
{
	protected $api_url = 'http://localhost/phpBB/phpBB/app.php';
	protected $key_store = 'keys.txt';

	public function get_auth_link()
	{
		$json = file_get_contents($this->api_url . '/api/auth/generate_keys');

		echo $json;

		$response = json_decode($json);

		$key_file = fopen($this->key_store, 'a');
		fwrite($key_file, $response->exchange_key . '|null|false|1');
		fclose($key_file);

		return $this->api_url . '/api/auth/authorize/' . $response->exchange_key;
	}

	public function exchange() {
		$keys = file_get_contents($this->key_store);
		$keyarr = explode('|', $keys);

		$json = file_get_contents($this->api_url . '/api/auth/exchange_key/' . $keyarr[0]);
		$response = json_decode($json);

		$keyarr[0] = $response->data->auth_key;
		$keyarr[1] = $response->data->sign_key;

		$keys = implode('|', $keyarr);

		$keys_file = fopen($this->key_store, 'w');
		fwrite($keys_file, $keys);
		fclose($keys_file);
	}

	public function verify()
	{
		$json = $this->request('auth/verify', array());

		echo $json;

		$response = json_decode($json);

		if($response->data->valid === true)
		{
			$keys = file_get_contents($this->key_store);
			$keyarr = explode('|', $keys);

			$keyarr[2] = 'true';

			$keys = implode('|', $keyarr);

			$keys_file = fopen($this->key_store, 'w');
			fwrite($keys_file, $keys);
			fclose($keys_file);

			return true;
		}

		return false;
	}

	public function request($method, $params)
	{
		$request = '/api/' . $method;
		foreach ($params as $param)
		{
			if (empty($param))
			{
				continue;
			}
			$request .= '/' . $param;
		}

		if (!isset($_POST['guest']))
		{
			$keys = file_get_contents($this->key_store);
			$keyarr = explode('|', $keys);

			$requesttohash = $request . 'auth_key=' . $keyarr[0] . '&serial=' . $keyarr[3];

			echo "Client hashing:" . $requesttohash . "<br />";

			$hash = hash_hmac('sha256', $requesttohash, $keyarr[1]);

			$request .= '?auth_key=' . $keyarr[0] . '&serial=' . $keyarr[3];

			$request .= '&hash=' . $hash;

			$keyarr[3] = $keyarr[3] + 1;

			$keys = implode('|', $keyarr);

			$keys_file = fopen($this->key_store, 'w');
			fwrite($keys_file, $keys);
			fclose($keys_file);
		}

		echo 'Made Request: ' . $this->api_url . $request . '<br /><br />';

		$context = stream_context_create(array('http' => array('ignore_errors' => true)));
		return file_get_contents($this->api_url . $request, false, $context);
	}
}
