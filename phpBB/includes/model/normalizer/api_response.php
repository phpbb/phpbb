<?php
/**
 *
 * @package normalizer
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Api response enitity normalizer
 * @package phpBB3
 */
class phpbb_model_normalizer_api_response implements NormalizerInterface
{
	public function normalize($response, $format = null)
	{
		$normalized_response = array(
			'status' => $response->get('status'),
			'data' => $response->get('data'),
		);
		return $normalized_response;
	}

	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof phpbb_model_entity_api_response;
	}
}
