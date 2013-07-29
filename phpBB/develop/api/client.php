<?php
/**
 *
 * @package phpBB3
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * This file creates new schema files for every database.
 * The filenames will be prefixed with an underscore to not overwrite the current schema files.
 *
 * If you overwrite the original schema files please make sure you save the file with UNIX linefeeds.
 */

class client
{
	protected $api_url = 'http://localhost/phpBB/phpBB/app.php?controller=';
	protected $key_store = 'keys.txt';

	public function get_auth_link()
	{
		$json = file_get_contents($this->api_url . 'api/auth/generatekeys');
		$response = json_decode($json);

		$key_file = fopen($this->key_store, 'a');
		fwrite($key_file, $response->data->auth_key . '|' . $response->data->sign_key . '|false|0');
		fclose($key_file);

		return $this->api_url . 'api/auth/' . $response->data->auth_key . '/' . $response->data->sign_key;
	}

	public function verify()
	{
		$json = $this->request('auth/verify', array());

		$response = json_decode($json);

		if($response->data->valid == true)
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
		$request = 'api/' . $method;
		foreach ($params as $param)
		{
			if (empty($param))
			{
				continue;
			}
			$request .= '/' . $param;
		}

		$keys = file_get_contents($this->key_store);
		$keyarr = explode('|', $keys);

		$request .=  '&auth_key=' . $keyarr[0] . '&serial=' . $keyarr[3];

		$hash = hash_hmac('sha256', $request, $keyarr[1]);

		$request .= '&hash=' . $hash;

		$keyarr[3] = $keyarr[3] + 1;

		$keys = implode('|', $keyarr);

		$keys_file = fopen($this->key_store, 'w');
		fwrite($keys_file, $keys);
		fclose($keys_file);

		echo 'Made Request: ' . $this->api_url . $request . '<br /><br />';

		$context = stream_context_create(array('http' => array('ignore_errors' => true)));
		return file_get_contents($this->api_url . $request, false, $context);
	}
}
