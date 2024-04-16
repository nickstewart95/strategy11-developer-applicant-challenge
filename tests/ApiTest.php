<?php

namespace Nickstewart\Challenge\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ApiTest extends TestCase {
	public object $client;

	protected function setUp(): void {
		$site_url = get_site_url() . '/wp-json/challenge/v1/';

		$this->client = new Client([
			'base_uri' => $site_url,
			'verify' => false, // my testing environment
		]);
	}

	public function testApiCache() {
		$response = $this->client->request('GET', '1');
		$data = json_decode($response->getBody());

		// Test that timestamp exists
		$this->assertObjectHasProperty(
			'timestamp',
			$data,
			'Timestamp property missing',
		);

		// Test to see that the timestamp is in the hour window
		$cacheTime = $data->timestamp;
		$now = time();
		$difference = ($now - $cacheTime) / 60;
		$this->assertLessThan(61, $difference, 'API cache window failing');
	}

	public function testApiOutput() {
		$response = $this->client->request('GET', '1');

		// Test status
		$this->assertEquals(200, $response->getStatusCode(), 'API status fail');

		// Test content type
		$contentType = $response->getHeaders()['Content-Type'][0];
		$this->assertEquals(
			'application/json; charset=UTF-8',
			$contentType,
			'Invalid content type',
		);

		// Test the output
		$data = json_decode($response->getBody());
		$this->assertNotEmpty($data, 'Response is empty');

		// - Test for main properties
		$this->assertObjectHasProperty(
			'title',
			$data,
			'Title property missing',
		);
		$this->assertObjectHasProperty('data', $data, 'Data property missing');

		// - Test for headers existing
		$this->assertObjectHasProperty(
			'headers',
			$data->data,
			'Headers property missing',
		);
		$this->assertNotEmpty($data->data->headers, 'Headers are empty');

		// - Test for rows existing
		$this->assertObjectHasProperty(
			'rows',
			$data->data,
			'Rows property missing',
		);
		$this->assertNotEmpty($data->data->rows, 'Rows are empty');

		// - Test the first item to confirm it has the correct properties
		$example = reset($data->data->rows);
		$this->assertObjectHasProperty('id', $example, 'ID property missing');
		$this->assertObjectHasProperty(
			'fname',
			$example,
			'Fname property missing',
		);
		$this->assertObjectHasProperty(
			'lname',
			$example,
			'Lname property missing',
		);
		$this->assertObjectHasProperty(
			'email',
			$example,
			'Email property missing',
		);
		$this->assertObjectHasProperty(
			'date',
			$example,
			'Date property missing',
		);
	}
}
