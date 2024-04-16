<?php

namespace Nickstewart\Challenge;

use Jenssegers\Blade\Blade;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Loader {
	const TRANSIENT_KEY = 'challenge_data';

	/**
	 * Run the plugin
	 */
	public function setup(): void {
		$this->init_rest();
		$this->init_cli();
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
	 * Setup custom REST endpoints
	 */
	public function init_rest(): void {
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
	 * Setup custom CLI commands
	 */
	public function init_cli(): void {
		// WP CLI Command to delete the transient
		add_action('cli_init', function () {
			\WP_CLI::add_command(
				'challenge-delete',
				[$this, 'cli_delete_transient'],
				[
					'shortdesc' =>
						'Deletes the transients holding the challenge data',
				],
			);
		});
	}

	/**
	 * The people API endpoint
	 */
	public function api_people_request(): object {
		// Check cache before request
		$transientData = get_transient(self::TRANSIENT_KEY);

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
			$data->timestamp = time();
			set_transient(self::TRANSIENT_KEY, $data, HOUR_IN_SECONDS);

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
	public function helper_bad_request($code, $message): \WP_Error {
		return new \WP_Error('error', $message, [
			'status' => $code,
		]);
	}

	/**
	 * Command that deletes the transient data
	 */
	public function cli_delete_transient($args): void {
		if (delete_transient(self::TRANSIENT_KEY)) {
			\WP_CLI::success('Deleted transient');
		} else {
			\WP_CLI::error('There was an error deleting the transient');
		}
	}
}
