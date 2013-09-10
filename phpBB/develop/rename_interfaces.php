<?php

$code_dir = realpath(__DIR__ . '/../');
$test_dir = realpath(__DIR__ . '/../../tests/');
$iterator = new \AppendIterator();
$iterator->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($code_dir)));
$iterator->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($test_dir)));

$map = array(
	'phpbb\request\request_interface' => 'phpbb\request\request_interface',
	'phpbb\auth\provider\provider_interface' => 'phpbb\auth\provider\provider_interface',
	'phpbb\avatar\driver\driver_interface' => 'phpbb\avatar\driver\driver_interface',
	'phpbb\cache\driver\driver_interface' => 'phpbb\cache\driver\driver_interface',
	'phpbb\db\migration\tool\tool_interface' => 'phpbb\db\migration\tool\tool_interface',
	'phpbb\extension\extension_interface' => 'phpbb\extension\extension_interface',
	'phpbb\groupposition\groupposition_interface' => 'phpbb\groupposition\groupposition_interface',
	'phpbb\log\log_interface' => 'phpbb\log\log_interface',
	'phpbb\notification\method\method_interface' => 'phpbb\notification\method\method_interface',
	'phpbb\notification\type\type_interface' => 'phpbb\notification\type\type_interface',
	'phpbb\request\request_interface' => 'phpbb\request\request_interface',
	'phpbb\tree\tree_interface' => 'phpbb\tree\tree_interface',
);

foreach ($iterator as $file)
{
	if ($file->getExtension() == 'php')
	{
		$code = file_get_contents($file->getPathname());

		foreach ($map as $orig => $new)
		{
			$code = preg_replace("#([^a-z0-9_\$])$orig([^a-z0-9_])#i", '\\1' . $new . '\\2', $code);
		}
		file_put_contents($file->getPathname(), $code);
	}
}
