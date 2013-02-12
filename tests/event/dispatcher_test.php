<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_event_dispatcher_test extends phpbb_test_case
{
    public function test_trigger_event()
    {
        $dispatcher = new phpbb_event_dispatcher(new phpbb_mock_container_builder());

        $dispatcher->addListener('core.test_event', function (phpbb_event_data $event) {
            $event['foo'] = $event['foo'] . '2';
            $event['bar'] = $event['bar'] . '2';
        });

        $foo = 'foo';
        $bar = 'bar';

        $vars = array('foo', 'bar');
        $result = $dispatcher->trigger_event('core.test_event', compact($vars));

        $this->assertSame(array('foo' => 'foo2', 'bar' => 'bar2'), $result);
    }
}
