<?php
namespace rubencm\phpbb\storage\driver;

class aws_s3 extends driver
{
	public function __construct($params)
	{
		$client = S3Client([
			'credentials' => [
				'key' => $params['key'],
				'secret' => $params['secret'],
			],
			'region' => $params['region'],
			'version' => $params['version'],
		]);

		$adapter = new AwsS3Adapter($client, $params['bucket']);
		$flysystemfs = new Filesystem($adapter);

		$this->filesystem =  new \phpbb\storage\adapter\flysystem($flysystemfs);
	}

	public function get_name()
	{
		return 'AWSS3';
	}

	public function get_params()
	{
		return array();
	}
}
