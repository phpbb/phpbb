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

namespace phpbb\webpush\subscription;

use Base64Url\Base64Url;
use Brick\Math\BigInteger;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Ecc\NistCurve;
use Jose\Component\Signature\Algorithm\ES256;
use Minishlink\WebPush\Utils;
use Minishlink\WebPush\VAPID;

/**
 * Web Push subscription key validator
 */
class validator
{
	/**
	 * Validate VAPID identification keys
	 *
	 * @param string $public_key VAPID public key
	 * @param string $private_key VAPID private key
	 * @return bool
	 */
	public static function is_valid_vapid_keys(string $public_key, string $private_key): bool
	{
		try
		{
			$vapid = VAPID::validate([
				'subject' => 'https://example.com',
				'publicKey' => $public_key,
				'privateKey' => $private_key,
			]);

			if (!self::is_valid($public_key, Base64Url::encode(substr($vapid['privateKey'], 0, 16))))
			{
				return false;
			}

			[$x, $y] = Utils::unserializePublicKey($vapid['publicKey']);
			$key = new JWK([
				'kty' => 'EC',
				'crv' => 'P-256',
				'x' => Base64Url::encode($x),
				'y' => Base64Url::encode($y),
				'd' => Base64Url::encode($vapid['privateKey']),
			]);
			$algorithm = new ES256();
			$input = 'phpBB VAPID key pair validation';
			$signature = $algorithm->sign($key, $input);

			return $algorithm->verify($key, $input, $signature);
		}
		catch (\Throwable $exception)
		{
			return false;
		}
	}

	/**
	 * Check that an endpoint is a plain HTTPS URL with a well-formed hostname
	 *
	 * @param string $endpoint Push subscription endpoint URL
	 * @return bool
	 */
	public static function is_valid_endpoint_url(string $endpoint): bool
	{
		$parts = parse_url($endpoint);

		return is_array($parts)
			&& strtolower($parts['scheme'] ?? '') === 'https'
			&& !isset($parts['user'])
			&& !isset($parts['pass'])
			&& (!isset($parts['port']) || $parts['port'] === 443)
			&& is_string($parts['host'] ?? null)
			&& filter_var($parts['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
	}

	/**
	 * Validate Web Push subscription keys
	 *
	 * @param string $public_key P-256 public key encoded with Base64URL
	 * @param string $auth_token Authentication secret encoded with Base64URL
	 * @return bool
	 */
	public static function is_valid(string $public_key, string $auth_token): bool
	{
		try
		{
			$decoded_public_key = self::decode_base64url($public_key);
			$decoded_auth_token = self::decode_base64url($auth_token);

			if (Utils::safeStrlen($decoded_public_key) !== 65
				|| $decoded_public_key[0] !== "\x04"
				|| Utils::safeStrlen($decoded_auth_token) !== 16)
			{
				return false;
			}

			[$x, $y] = Utils::unserializePublicKey($decoded_public_key);
			NistCurve::curve256()->getPublicKeyFrom(
				BigInteger::fromBase(bin2hex($x), 16),
				BigInteger::fromBase(bin2hex($y), 16)
			);

			return true;
		}
		catch (\InvalidArgumentException|\RuntimeException $exception)
		{
			return false;
		}
	}

	/**
	 * Strictly decode a Base64URL value
	 *
	 * @param string $value Base64URL encoded value
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	private static function decode_base64url(string $value): string
	{
		if (!preg_match('/^[A-Za-z0-9_-]+={0,2}$/D', $value))
		{
			throw new \InvalidArgumentException('Invalid Base64URL value');
		}

		$decoded = Base64Url::decode($value);
		if (Base64Url::encode($decoded) !== rtrim($value, '='))
		{
			throw new \InvalidArgumentException('Non-canonical Base64URL value');
		}

		return $decoded;
	}
}
