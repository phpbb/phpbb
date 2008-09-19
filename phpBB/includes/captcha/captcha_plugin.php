<?

interface phpbb_captcha_plugin
{
	/**
	* Initiates the CAPTCHA to validate codes. 
	* @param int $type the type as defined in constants.php
	*/
	function init($type);


	/**
	* Returns true if the captcha will work on the current install
	*/
	static function is_available();
	
	/**
	* Returns the translated pretty name of the captcha.
	*/
	static function get_name();
	
	/**
	* Returns the class name of the captcha.
	*/
	static function get_class_name();
	
	/**
	* Returns an instance; does not have to be the same instance twice.
	*/
	static function get_instance();

	/**
	* Returns the HTML needed to embed the captcha in another template
	*/
	function get_template();
	
	
	/**
	* Delivers the image of image based captchas; not required for text/remote etc CAPTCHAs
	*/
	function execute();
	
	/**
	* Returns the HTML needed to display a demo of the captcha
	*/
	function get_demo_template($id);
	
	
	/**
	* Delivers the demo image of image based captchas; not required for text/remote etc CAPTCHAs
	*/
	function execute_demo();
	
	/**
	* Clears leftover entries in the database. 
	*/
	static function garbage_collect($type);
	
	
	/**
	* Clears all entries from the database if the CAPTCHA is replaced
	*/
	function uninstall();
	
	/**
	* Sets up the CAPTCHA when it is selected in the ACP.
	*/
	function install();
	
	
	/**
	* Checks the captcha; returns false if the code was correct; a translated error string otherwise
	*/
	function validate();
	
	/**
	* Prepares the captcha to ask a new question; required call on failed answers
	*/
	function reset();
	
	/**
	* Displays the configuration options in the ACP
	*/
	function acp_page($id, &$module);
		
	/**
	* Returns the entries for the hidden field array needed to preserve the current state.
	*/
	function get_hidden_fields();
	
	
	/**
	* Returns the number of solving attempts of the current user 
	*/
	function get_attempt_count();

}