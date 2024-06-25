<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

class phpbb_event_dispatcher_test extends phpbb_test_case
{
    public function test_trigger_event()
    {
        $dispatcher = new \phpbb\event\dispatcher();

        $dispatcher->addListener('core.test_event', function (\phpbb\event\data $event) {
            $event['foo'] = $event['foo'] . '2';
            $event['bar'] = $event['bar'] . '2';
        });

        $foo = 'foo';
        $bar = 'bar';

        $vars = array('foo', 'bar');
        $result = $dispatcher->trigger_event('core.test_event', compact($vars));

        $this->assertSame(array('foo' => 'foo2', 'bar' => 'bar2'), $result);

        // Test migrating events
		$dispatcher->addListener('core.foo_br', function(\phpbb\event\data $event) {
			$event['pi'] = '3.14159';
		});
		$dispatcher->addListener('core.foo_bar', function(\phpbb\event\data $event) {
			$event['pi'] = '3.1';
		});


		$pi = '3';

		$vars = array('pi');
		$result = $dispatcher->trigger_event(['core.foo_bar', 'core.foo_br'], compact($vars));

		$this->assertSame(array('pi' => '3.14159'), $result);
    }
}
