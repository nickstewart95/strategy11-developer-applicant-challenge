<?php

namespace Nickstewart\Challenge;

use Jenssegers\Blade\Blade;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Loader {
	/**
	 * Run the plugin
	 */
	public function setup(): void {
		$this->init_actions();
		$this->init_filters();
	}

	/**
	 * Setup Blade templating
	 */
	public function init_blade_views(): Blade {
		$views = __DIR__ . '/resources/pages';
		$cache = __DIR__ . '/cache';

		return new Blade($views, $cache);
	}

	/**
	 * Setup actions
	 */
	public function init_actions(): void {
		// Setup the REST endpoint
		add_action('rest_api_init', function () {
			register_rest_route('challenge/v1', '/1', [
				'methods' => 'GET',
				'callback' => [$this, 'api_people_request'],
				'permission_callback' => function ($request) {
					// No permissions required
					return true;
				},
			]);
		});
	}

	/**
	 * Setup filters
	 */
	public function init_filters(): void {
		//
	}

	/**
	 * The people API endpoint
	 */
	public function api_people_request(): object {
		// Check cache before request
		$transientKey = 'challenge_data';
		$transientData = get_transient($transientKey);

		if (!empty($transientData)) {
			return $transientData;
		}

		$client = new Client([
			'base_uri' => 'https://api.strategy11.com/wp-json/challenge/v1/',
		]);

		try {
			$response = $client->request('GET', '1');

			if ($response->getStatusCode() !== 200) {
				return $this->helper_bad_request(
					$response->getStatusCode(),
					'There was an error',
				);
			}

			$data = json_decode($response->getBody(), false);

			if (empty($data)) {
				return $this->helper_bad_request(500, 'There was an error');
			}

			// Cache lives for one hour
			set_transient($transientKey, $data, HOUR_IN_SECONDS);

			return $data;
		} catch (ClientException $e) {
			return $this->helper_bad_request($e->getCode(), $e->getMessage());
		} catch (\Exception $e) {
			return $this->helper_bad_request(500, $e->getMessage());
		}
	}

	/**
	 * Helper function for request errors
	 */
	public function helper_bad_request($code, $message): object {
		return new \WP_Error('error', $message, [
			'status' => $code,
		]);
	}
}
