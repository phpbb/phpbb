<?php
/**
 * @package testing
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class phpbb_profilefield_type_string_test extends phpbb_test_case
{
    protected $cp;
    protected $field_options;

    /**
     * Sets up basic test objects
     *
     * @access public
     * @return null
     */
    public function setUp()
    {
        global $request, $user, $cache;

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

        $this->cp = new \phpbb\profilefields\type\type_string(
            $request,
            $template,
            $user
        );

        $this->field_options = array(
            'field_type'       => '\phpbb\profilefields\type\type_string',
            'field_name' 	   => 'field',
            'field_id'	 	   => 1,
            'lang_id'	 	   => 1,
            'lang_name'        => 'field',
            'field_required'   => false,
            'field_validation' => '.*',
        );
    }

    public function get_validate_profile_field_data()
    {
        return array(
            array(
                    '',
                    array('field_required' => true),
                    'FIELD_REQUIRED-field',
                    'Field should not accept empty values for required fields',
            ),
            array(
                    'tas',
                    array('field_minlen' => 2, 'field_maxlen' => 5),
                    false,
                    'Field should accept value of correct length',
            ),
            array(
                    't',
                    array('field_minlen' => 2, 'field_maxlen' => 5),
                    'FIELD_TOO_SHORT-2-field',
                    'Field should reject value of incorrect length',
            ),
            array(
                    'this is a long string',
                    array('field_minlen' => 2, 'field_maxlen' => 5),
                    'FIELD_TOO_LONG-5-field',
                    'Field should reject value of incorrect length',
            ),
            array(
                    'H3110',
                    array('field_validation' => '[0-9]+'),
                    'FIELD_INVALID_CHARS_NUMBERS_ONLY-field',
                    'Required field should reject characters in a numbers-only field',
            ),
            array(
                    '&lt;&gt;&quot;&amp;%&amp;&gt;&lt;&gt;',
                    array('field_maxlen' => 10, 'field_minlen' => 2),
                    false,
                    'Optional field should accept html entities',
            ),
            array(
                    'ö ä ü ß',
                    array(),
                    false,
                    'Required field should accept UTF-8 string',
            ),
            array(
                    'This ö ä string has to b',
                    array('field_maxlen' => 10),
                    'FIELD_TOO_LONG-10-field',
                    'Required field should reject an UTF-8 string which is too long',
            ),
            array(
                    'ö äö äö ä',
                    array('field_validation' => '[\w]+'),
                    'FIELD_INVALID_CHARS_ALPHA_ONLY-field',
                    'Required field should reject UTF-8 in alpha only field',
            ),
            array(
                    'Hello',
                    array('field_validation' => '[\w]+'),
                    false,
                    'Required field should accept a characters only field',
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
                    'test',
                    array('field_show_novalue' => true),
                    'test',
                    'Field should output the given value',
            ),
            array(
                    'test',
                    array('field_show_novalue' => false),
                    'test',
                    'Field should output the given value',
            ),
            array(
                    '',
                    array('field_show_novalue' => true),
                    '',
                    'Field should output nothing for empty value',
            ),
            array(
                    '',
                    array('field_show_novalue' => false),
                    null,
                    'Field should simply output null for empty vlaue',
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

    public function return_callback_implode()
    {
        return implode('-', func_get_args());
    }
}
