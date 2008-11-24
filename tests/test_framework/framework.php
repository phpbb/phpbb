<?php

// require at least PHPUnit 3.3.0
require_once 'PHPUnit/Runner/Version.php';
if (version_compare(PHPUnit_Runner_Version::id(), '3.3.0', '<'))
{
	trigger_error('PHPUnit >= 3.3.0 required');
}

require_once 'PHPUnit/Framework.php';
require_once 'test_framework/phpbb_test_case.php';