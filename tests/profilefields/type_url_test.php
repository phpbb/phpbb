<?php
/**
 * @package testing
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_profilefield_type_url_test extends phpbb_test_case
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
        $user = $this->getMock('\phpbb\user');
        $user->expects($this->any())
            ->method('lang')
            ->will($this->returnCallback(array($this, 'return_callback_implode')));

        $request = $this->getMock('\phpbb\request\request');
        $template = $this->getMock('\phpbb\template\template');

        $this->cp = new \phpbb\profilefields\type\type_url(
            $request,
            $template,
            $user
        );

        $this->field_options = array(
            'field_type'     => '\phpbb\profilefields\type\type_url',
            'field_name' 	 => 'field',
            'field_id'	 	 => 1,
            'lang_id'	 	 => 1,
            'lang_name'      => 'field',
            'field_required' => false,
        );
    }

    public function get_validate_profile_field_data()
    {
        return array(
            array(
                '',
                array('field_required' => true),
                'FIELD_INVALID_URL-field',
                'Field should reject empty field that is required',
            ),
            array(
                'invalidURL',
                array(),
                'FIELD_INVALID_URL-field',
                'Field should reject invalid input',
            ),
            array(
                'http://onetwthree.aol.io',
                array(),
                false,
                'Field should accept valid URL',
            ),
            array(
                'http://example.com/index.html?param1=test&param2=awesome',
                array(),
                false,
                'Field should accept valid URL',
            ),
            array(
                'http://example.com/index.html/test/path?document=get',
                array(),
                false,
                'Field should accept valid URL',
            ),
            array(
                'http://example.com/index.html/test/path?document[]=DocType%20test&document[]=AnotherDoc',
                array(),
                'FIELD_INVALID_URL-field',
                'Field should reject invalid URL having multi value parameters',
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

    public function return_callback_implode()
    {
        return implode('-', func_get_args());
    }
}
