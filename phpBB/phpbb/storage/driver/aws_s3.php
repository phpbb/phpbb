<?php
namespace rubencm\phpbb\storage\driver;

class aws_s3 extends adapter_common
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
}
