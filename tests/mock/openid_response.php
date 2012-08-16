<?php
/**
 *
 * @package testing
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class phpbb_mock_openid_response extends \Zend\Http\Response
{
    private $_canSendHeaders;

    public function __construct($canSendHeaders)
    {
        $this->_canSendHeaders = $canSendHeaders;
    }

    public function canSendHeaders($throw = false)
    {
        return $this->_canSendHeaders;
    }

    public function sendResponse()
    {
    }
}
