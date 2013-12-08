<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

/**
* Form builder
*
* Uses Symfony Forms
*
* @package phpBB3
*/
class form
{
	/** @var phpbb\config\config */
	protected $phpbb_config;

	/** @var phpbb\user */
	protected $phpbb_user;

	/**
	* Constructor
	*
	* @param phpbb\config\config $phpbb_config
	* @param phpbb\user $phpbb_user
	*/
	public function __construct(\phpbb\config\config $phpbb_config, \phpbb\user $phpbb_user)
	{
		$this->phpbb_config = $phpbb_config;
		$this->phpbb_user = $phpbb_user;
	}

	/**
	* Get a Symfony Form Builder
	*
	* @return Symfony\Component\Form\FormBuilder
	*/
	public function get_builder()
	{
		$token_sid = ($this->phpbb_user->data['user_id'] == ANONYMOUS && !empty($this->phpbb_config['form_token_sid_guests'])) ? $this->phpbb_user->session_id : '';
		$secret = $this->phpbb_user->data['user_form_salt'] . $token_sid;

		return Forms::createFormFactoryBuilder()
			->addExtension(
				new CsrfExtension(
					new DefaultCsrfProvider($secret)
				)
			)
			->addExtension(
				new ValidatorExtension(Validation::createValidator())
			)
			->getFormFactory()
			->createBuilder()
			->setRequestHandler(new HttpFoundationRequestHandler());
	}

	public function handle_request($form, $request, $symfony_request)
	{
		$request->enable_super_globals();

		$form->handleRequest($symfony_request);

		$request->disable_super_globals();
	}
}
