<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb;

use Symfony\Component\Validator\Exception\BadMethodCallException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translator for the Symfony Validator
 *
 * Example usage:
 *
 *     $translator = new validator_translator();
 *
 *     echo $translator->trans(
 *         'This is a {{ var }}.',
 *         array('{{ var }}' => 'donkey')
 *     );
 *
 *     // -> This is a donkey.
 *
 *     echo $translator->transChoice(
 *         'This is {{ count }} donkey.|These are {{ count }} donkeys.',
 *         3,
 *         array('{{ count }}' => 'three')
 *     );
 *
 *     // -> These are three donkeys.
 */
class validator_translator implements TranslatorInterface
{
	/** @var \phpbb\user */
	protected $phpbb_user;

	/**
	* @var array Validator string to phpBB language string conversion table
	*/
	protected $lang_string_converter = array(
		'This value should be false.' => 'VALIDATE_FALSE',
		'This value should be true.' => 'VALIDATE_TRUE',
		'This value should be of type {{ type }}.' => 'VALIDATE_TYPE',
		'This value should be blank.' => 'VALIDATE_BLANK',
		'The value you selected is not a valid choice.' => 'VALIDATE_SELECTED_INVALID',
		'You must select at least {{ limit }} choice.|You must select at least {{ limit }} choices.' => 'VALIDATE_SELECT_ATLEAST',
		'You must select at most {{ limit }} choice.|You must select at most {{ limit }} choices.' => 'VALIDATE_SELECT_ATMOST',
		'One or more of the given values is invalid.' => 'VALIDATE_VALUES_INVALID',
		'The fields {{ fields }} were not expected.' => 'VALIDATE_UNEXPECTED',
		'The fields {{ fields }} are missing.' => 'VALIDATE_MISSING',
		'This value is not a valid date.' => 'VALIDATE_DATE',
		'This value is not a valid datetime.' => 'VALIDATE_DATETIME',
		'This value is not a valid email address.' => 'VALIDATE_EMAIL',
		'The file could not be found.' => 'VALIDATE_FILE_NOT_FOUND',
		'The file is not readable.' => 'VALIDATE_FILE_NOT_READABLE',
		'The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.' => 'VALIDATE_FILE_TOO_LARGE',
		'The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.' => 'VALIDATE_FILE_MIMETYPE',
		'This value should be {{ limit }} or less.' => 'VALIDATE_TOO_LARGE',
		'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.' => 'VALIDATE_TOO_LONG',
		'This value should be {{ limit }} or more.' => 'VALIDATE_TOO_SMALL',
		'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.' => 'VALIDATE_TOO_SHORT',
		'This value should not be blank.' => 'VALIDATE_NOT_BLANK',
		'This value should not be null.' => 'VALIDATE_NOT_NULL',
		'This value should be null.' => 'VALIDATE_NULL',
		'This value is not valid.' => 'VALIDATE_NOT_VALID',
		'This value is not a valid time.' => 'VALIDATE_NOT_VALID_TIME',
		'This value is not a valid URL.' => 'VALIDATE_NOT_VALID_URL',
		'The two values should be equal.' => 'VALIDATE_EQUAL',
		'The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.' => 'VALIDATE_FILE_TOO_LARGE',
		'The file is too large.' => 'VALIDATE_FILE_TOO_LARGE',
		'The file could not be uploaded.' => 'VALIDATE_FILE_UPLOAD_ERROR',
		'This value should be a valid number.' => 'VALIDATE_NUMBER',
		'This file is not a valid image.' => 'VALIDATE_IMAGE',
		'This is not a valid IP address.' => 'VALIDATE_IP_ADDRESS',
		'This value is not a valid language.' => 'VALIDATE_LANGUAGE',
		'This value is not a valid locale.' => 'VALIDATE_LOCALE',
		'This value is not a valid country.' => 'VALIDATE_COUNTRY',
		'This value is already used.' => 'VALIDATE_DUPLICATE',
		'The size of the image could not be detected.' => 'VALIDATE_IMAGE_SIZE',
		'The image width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px.' => 'VALIDATE_IMAGE_WIDTH_LARGE',
		'The image width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px.' => 'VALIDATE_IMAGE_WIDTH_SMALL',
		'The image height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px.' => 'VALIDATE_IMAGE_HEIGHT_LARGE',
		'The image height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px.' => 'VALIDATE_IMAGE_HEIGHT_SMALL',
		'This value should be the user current password.' => 'VALIDATE_PASSWORD_MATCH',
		'This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.' => 'VALIDATE_LENGTH_EXACT',
		'The file was only partially uploaded.' => 'VALIDATE_FILE_UPLOAD_PARTIAL',
		'No file was uploaded.' => 'VALIDATE_FILE_UPLOAD_EMPTY',
		'No temporary folder was configured in php.ini.' => 'VALIDATE_FILE_UPLOAD_ERROR',
		'Cannot write temporary file to disk.' => 'VALIDATE_FILE_UPLOAD_ERROR',
		'A PHP extension caused the upload to fail.' => 'VALIDATE_FILE_UPLOAD_ERROR',
		'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.' => 'VALIDATE_COLLECTION_SMALL',
		'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.' => 'VALIDATE_COLLECTION_LARGE',
		'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.' => 'VALIDATE_COLLECTION_EXACT',
		'Invalid card number.' => 'VALIDATE_CARD',
		'Unsupported card type or invalid card number.' => 'VALIDATE_CARD_TYPE',
	);

	/**
	* Constructor
	*
	* @param \phpbb\user $phpbb_user
	*/
	public function __construct($phpbb_user)
	{
		$this->phpbb_user = $phpbb_user;
	}

    /**
     * Interpolates the given message.
     *
     * Parameters are replaced in the message in the same manner that
     * {@link strtr()} uses.
     *
     * Example usage:
     *
     *     $translator = new DefaultTranslator();
     *
     *     echo $translator->trans(
     *         'This is a {{ var }}.',
     *         array('{{ var }}' => 'donkey')
     *     );
     *
     *     // -> This is a donkey.
     *
     * @param string $id         The message id
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     Ignored
     * @param string $locale     Ignored
     *
     * @return string The interpolated string
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
		$id = (isset($this->lang_string_converter[$id])) ? $this->lang_string_converter[$id] : $id;

		array_unshift($parameters, $id);

		return call_user_func_array(array($this->phpbb_user, 'lang'), $parameters);
    }

    /**
     * Interpolates the given choice message by choosing a variant according to a number.
     *
     * The variants are passed in the message ID using the format
     * "<singular>|<plural>". "<singular>" is chosen if the passed $number is
     * exactly 1. "<plural>" is chosen otherwise.
     *
     * This format is consistent with the format supported by
     * {@link \Symfony\Component\Translation\Translator}, but it does not
     * have the same expressiveness. While Translator supports intervals in
     * message translations, which are needed for languages other than English,
     * this translator does not. You should use Translator or a custom
     * implementation of {@link TranslatorInterface} if you need this or similar
     * functionality.
     *
     * Example usage:
     *
     *     echo $translator->transChoice(
     *         'This is {{ count }} donkey.|These are {{ count }} donkeys.',
     *         0,
     *         array('{{ count }}' => 0)
     *     );
     *
     *     // -> These are 0 donkeys.
     *
     *     echo $translator->transChoice(
     *         'This is {{ count }} donkey.|These are {{ count }} donkeys.',
     *         1,
     *         array('{{ count }}' => 1)
     *     );
     *
     *     // -> This is 1 donkey.
     *
     *     echo $translator->transChoice(
     *         'This is {{ count }} donkey.|These are {{ count }} donkeys.',
     *         3,
     *         array('{{ count }}' => 3)
     *     );
     *
     *     // -> These are 3 donkeys.
     *
     * @param string  $id         The message id
     * @param integer $number     The number to use to find the index of the message
     * @param array   $parameters An array of parameters for the message
     * @param string  $domain     Ignored
     * @param string  $locale     Ignored
     *
     * @return string The translated string
     *
     * @throws InvalidArgumentException If the message id does not have the format
     *                                  "singular|plural".
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
		$id = (isset($this->lang_string_converter[$id])) ? $this->lang_string_converter[$id] : $id;

		array_unshift($parameters, $id);

		return call_user_func_array(array($this->phpbb_user, 'lang'), $parameters);
    }

    /**
     * Not supported.
     *
     * @param string $locale The locale
     *
     * @throws BadMethodCallException
     */
    public function setLocale($locale)
    {
        throw new BadMethodCallException('Unsupported method.');
    }

    /**
     * Returns the locale of the translator.
     *
     * @return string Always returns 'en'
     */
    public function getLocale()
    {
        return '';
    }
}
