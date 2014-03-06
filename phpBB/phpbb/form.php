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
	/** @var \phpbb\config\config */
	protected $phpbb_config;

	/** @var \phpbb\event\dispatcher */
	protected $phpbb_dispatcher;

	/** @var \phpbb\request\request */
	protected $phpbb_request;

	/** @var \phpbb\user */
	protected $phpbb_user;

	/** @var \phpbb\symfony_request */
	protected $symfony_request;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $phpbb_config
	* @param \phpbb\event\dispatcher $phpbb_dispatcher
	* @param \phpbb\request\request $phpbb_request
	* @param \phpbb\user $phpbb_user
	* @param \phpbb\symfony_request $symfony_request
	*/
	public function __construct(
		\phpbb\config\config $phpbb_config,
		\phpbb\event\dispatcher $phpbb_dispatcher,
		\phpbb\request\request $phpbb_request,
		\phpbb\user $phpbb_user,
		\phpbb\symfony_request $symfony_request)
	{
		$this->phpbb_config = $phpbb_config;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->phpbb_request = $phpbb_request;
		$this->phpbb_user = $phpbb_user;
		$this->symfony_request = $symfony_request;

		$this->phpbb_user->add_lang('form');
	}

	/**
	* Get a Symfony Form Builder
	*
	* @param string $form_name The name of the form
	* @return \Symfony\Component\Form\FormBuilder
	*/
	public function get_builder($form_name)
	{
		$token_sid = ($this->phpbb_user->data['user_id'] == ANONYMOUS && !empty($this->phpbb_config['form_token_sid_guests'])) ? $this->phpbb_user->session_id : '';
		$secret = $this->phpbb_user->data['user_form_salt'] . $token_sid;

		$validator = Validation::createValidatorBuilder()
			->setTranslator(new \phpbb\validator_translator($this->phpbb_user))
			->getValidator()
		;
		$validator_extension = new ValidatorExtension($validator);

		return Forms::createFormFactoryBuilder()
			// CSRF Extension
			->addExtension(
				new CsrfExtension(
					new DefaultCsrfProvider($secret)
				)
			)

			// Validator Extension
			->addExtension($validator_extension)

			->getFormFactory()

			// Create the form builder
			->createNamedBuilder($form_name, 'form', null, array(
				'intention' => $form_name,
			))

			// Set the request Handler to use Symfony's request
			->setRequestHandler(new HttpFoundationRequestHandler())

			// Set fields to NOT be required by default
			->setRequired(false)
		;
	}

	/**
	* Get a constraint
	*
	* A helper that makes it a little easier to add constraints (validation)
	*
	* @param string $name The constraint name
	*                         http://symfony.com/doc/2.3/book/validation.html#constraints
	* @param array|null $options The options for the constraint
	* @return \Symfony\Component\Validator\Constraint
	*/
	public function get_constraint($name, $options = null)
	{
		$class = '\Symfony\Component\Validator\Constraints\\' . $name;

		return new $class($options);
	}

	/**
	* Prepare the form
	*
	* This function creates an event to allow modifying the form (form.form_name)
	* This also gets the form and handles the request
	*
	* @param \Symfony\Component\Form\FormBuilder $form
	* @return $this
	*/
	public function prepare_form(\Symfony\Component\Form\FormBuilder &$form)
	{
		/**
		* This event is used to modify forms
		*
		* @event core.prepare_form
		* @var  Symfony\Component\Form\FormBuilder form
		* @var  string                             form_name The form name (read only)
		* @since 3.1-A3
		*/
		$form_name = $form->getName();
		$vars = array('form', 'form_name');
		extract($this->phpbb_dispatcher->trigger_event('core.prepare_form', compact($vars)));

		// Get the form
		$form = $form->getForm();

		// Must enable super globals for Symfony's Validators
		$this->phpbb_request->enable_super_globals();

		// Handle the request
		$form->handleRequest($this->symfony_request);

		// Disable super globals again
		$this->phpbb_request->disable_super_globals();

		return $this;
	}
}
