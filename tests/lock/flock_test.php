<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_lock_flock_test extends phpbb_test_case
{
	public function test_lock()
	{
		$path = __DIR__ . '/../tmp/precious';

		$lock = new phpbb_lock_flock($path);
		$ok = $lock->acquire();
		$this->assertTrue($ok);
		$lock->release();
	}

	public function test_consecutive_locking()
	{
		$path = __DIR__ . '/../tmp/precious';

		$lock = new phpbb_lock_flock($path);
		$ok = $lock->acquire();
		$this->assertTrue($ok);
		$this->assertTrue($lock->owns_lock());
		$lock->release();
		$this->assertFalse($lock->owns_lock());

		$ok = $lock->acquire();
		$this->assertTrue($ok);
		$this->assertTrue($lock->owns_lock());
		$lock->release();
		$this->assertFalse($lock->owns_lock());

		$ok = $lock->acquire();
		$this->assertTrue($ok);
		$this->assertTrue($lock->owns_lock());
		$lock->release();
		$this->assertFalse($lock->owns_lock());
	}

	/* This hangs the process.
	public function test_concurrent_locking_fail()
	{
		$path = __DIR__ . '/../tmp/precious';

		$lock1 = new phpbb_lock_flock($path);
		$ok = $lock1->acquire();
		$this->assertTrue($ok);

		$lock2 = new phpbb_lock_flock($path);
		$ok = $lock2->acquire();
		$this->assertFalse($ok);

		$lock->release();
		$ok = $lock2->acquire();
		$this->assertTrue($ok);
	}
	*/

	public function test_concurrent_locking()
	{
		if (!function_exists('pcntl_fork'))
		{
			$this->markTestSkipped('pcntl extension and pcntl_fork are required for this test');
		}

		$path = __DIR__ . '/../tmp/precious';

		$pid = pcntl_fork();
		if ($pid)
		{
			// parent
			// wait 0.5 s, acquire the lock, note how long it took
			sleep(1);

			$lock = new phpbb_lock_flock($path);
			$start = time();
			$ok = $lock->acquire();
			$delta = time() - $start;
			$this->assertTrue($ok);
			$this->assertTrue($lock->owns_lock());
			$this->assertGreaterThan(0.5, $delta, 'First lock acquired too soon');

			$lock->release();
			$this->assertFalse($lock->owns_lock());

			// acquire again, this should be instantaneous
			$start = time();
			$ok = $lock->acquire();
			$delta = time() - $start;
			$this->assertTrue($ok);
			$this->assertTrue($lock->owns_lock());
			$this->assertLessThan(0.1, $delta, 'Second lock not acquired instantaneously');

			// reap the child
			$status = null;
			pcntl_waitpid($pid, $status);
		}
		else
		{
			// child
			// immediately acquire the lock and sleep for 2 s
			$lock = new phpbb_lock_flock($path);
			$ok = $lock->acquire();
			$this->assertTrue($ok);
			$this->assertTrue($lock->owns_lock());
			sleep(2);
			$lock->release();
			$this->assertFalse($lock->owns_lock());

			// and go away silently
			pcntl_exec('/usr/bin/env', array('true'));
		}
	}
}
