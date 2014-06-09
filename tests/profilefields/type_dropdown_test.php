<?php
/**
 * @package testing
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class phpbb_profilefield_type_dropdown_test extends phpbb_test_case
{
    protected $cp;
    protected $field_options = array();
    protected $dropdown_options = array();

    /**
     * Sets up basic test objects
     *
     * @access public
     * @return void
     */
    public function setUp()
    {
        global $request, $user, $cache, $db, $table_prefix;

        require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
        require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
        require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

        $user = $this->getMock('\phpbb\user');
        $cache = new phpbb_mock_cache;
        $user->expects($this->any())
            ->method('lang')
            ->will($this->returnCallback(array($this, 'return_callback_implode')));

        $request = $this->getMock('\phpbb\request\request');
        $template = $this->getMock('\phpbb\template\template');

        $lang = $this->getMock('\phpbb\profilefields\lang_helper', array(), array($db, $table_prefix . 'profile_fields_lang'));

        $lang->expects($this->any())
             ->method('get_options_lang');

        $lang->expects($this->any())
             ->method('is_set')
             ->will($this->returnCallback(array($this, 'is_set_callback')));

        $lang->expects($this->any())
             ->method('get')
             ->will($this->returnCallback(array($this, 'get')));

        $this->cp = new \phpbb\profilefields\type\type_dropdown(
            $lang,
            $request,
            $template,
            $user
        );

        $this->field_options = array(
            'field_type'       => '\phpbb\profilefields\type\type_dropdown',
            'field_name' 	   => 'field',
            'field_id'	 	   => 1,
            'lang_id'	 	   => 1,
            'lang_name'        => 'field',
            'field_required'   => false,
            'field_validation' => '.*',
            'field_novalue'    => 0,
        );

        $this->dropdown_options = array(
            0 => '<No Value>',
            1 => 'Option 1',
            2 => 'Option 2',
            3 => 'Option 3',
            4 => 'Option 4',
        );
    }

    public function get_validate_profile_field_data()
    {
        return array(
                array(
                    7,
                    array(),
                    'FIELD_INVALID_VALUE-field',
                    'Invalid value should throw error',
                ),
                array(
                    2,
                    array(),
                    false,
                    'Valid value should not throw error'
                ),
                array(
                    0,
                    array(),
                    false,
                    'Empty value should be acceptible',
                ),
                array(
                    0,
                    array('field_required' => true),
                    'FIELD_REQUIRED-field',
                    'Required field should not accept empty value',
                ),
        );
    }

    /**
     * @dataProvider get_validate_profile_field_data
     */
    public function test_validate_profile_field($value, $field_options, $expected, $description)
    {
        $field_options = array_merge($this->field_options, $field_options);

        $result = $this->cp->validate_profile_field($value, $field_options);

        $this->assertSame($expected, $result, $description);
    }

    public function get_profile_value_data()
    {
        return array(
            array(
                1,
                array('field_show_novalue' => true),
                'Option 1',
                'Field should output the given value',
            ),
            array(
                4,
                array('field_show_novalue' => false),
                'Option 4',
                'Field should output the given value',
            ),
            array(
                '',
                array('field_show_novalue' => true),
                '<No Value>',
                'Field should output nothing for empty value',
            ),
            array(
                '',
                array('field_show_novalue' => false),
                null,
                'Field should simply output null for empty value',
            ),
        );
    }


    /**
     * @dataProvider get_profile_value_data
     */
    public function test_get_profile_value($value, $field_options, $expected, $description)
    {
        $field_options = array_merge($this->field_options, $field_options);

        $result = $this->cp->get_profile_value($value, $field_options);

        $this->assertSame($expected, $result, $description);
    }

    public function is_set_callback($field_id, $lang_id, $field_value)
    {
        return isset($this->dropdown_options[$field_value]);
    }

    public function get($field_id, $lang_id, $field_value)
    {
        return $this->dropdown_options[$field_value];
    }

    public function return_callback_implode()
    {
        return implode('-', func_get_args());
    }
}