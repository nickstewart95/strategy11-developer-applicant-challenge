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
		// Setup custom REST endpoint
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

		// Setup custom CLI command
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

		// Enqueue scripts and styles
		add_action('admin_enqueue_scripts', function () {
			$style = 'resources/build/global.css';

			wp_enqueue_style(
				'challenge-admin-styles',
				plugin_dir_url(__FILE__) . $style,
				[],
				filemtime(plugin_dir_path(__FILE__) . $style),
				false,
			);
		});

		// Setup custom admin page
		add_action('admin_menu', [$this, 'admin_settings_page']);

		// Refresh data and redirect from the admin page
		add_action('admin_init', [$this, 'refresh_redirect_settings_page']);

		// Setup shortcode that returns data table
		add_shortcode('applicant-challenge', [$this, 'data_shortcode']);
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

			/**
			 * Sanitization Notes
			 * 	sanitize_text_field strips all tags
			 * 	sanitize_email strips out all characters not allowed in an email
			 * 	filter_var in this case is returning only postive numbers
			 * 
			 * Result should be clean data that can be stored and displayed
			 */

			$clean = [];
			$clean['timestamp'] = time();
			$clean['data']['headers'] = [];
			$clean['data']['rows'] = [];

			$clean['title'] = sanitize_text_field($data->title);

			foreach ($data->data->headers as $header) {
				$clean['data']['headers'][] = sanitize_text_field($header);
			}

			foreach ($data->data->rows as $key => $row) {
				$tmp = [];
				$tmp['id'] = filter_var($row->id, FILTER_SANITIZE_NUMBER_INT, ['options' => ['min_range' => 1]]);
				$tmp['fname'] = sanitize_text_field($row->fname);
				$tmp['lname'] = sanitize_text_field($row->lname);
				$tmp['email'] = sanitize_email($row->email);

				$date = filter_var($row->date, FILTER_SANITIZE_NUMBER_INT, ['options' => ['min_range' => 1]]);
				$tmp['date'] = date('m/d/Y', $date);
			
				$key = sanitize_key($key);
				$clean['data']['rows'][$key] = $tmp;
			}

			$data = (object) $clean;

			// Cache lives for one hour
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

	/**
	 * Add the plugin settings page
	 */
	public function admin_settings_page(): void {
		add_options_page(
			'Applicant Challenge',
			'Applicant Challenge',
			'activate_plugins',
			'challenge',
			[$this, 'create_settings_page'],
		);
	}

	/**
	 * Create the actual plugin settings page
	 */
	public function create_settings_page(): void {
		$blade = $GLOBALS['blade'];
		$data = $this->api_people_request();
		$message = isset($_GET['message']) ? 'Data refreshed' : false;

		echo $blade->render('admin.settings', [
			'data' => $data,
			'message' => $message,
		]);
	}

	/**
	 * Delete data and redirect in the admin
	 */
	public function refresh_redirect_settings_page(): void {
		if (isset($_GET['action']) && $_GET['action'] == 'refresh') {
			delete_transient(self::TRANSIENT_KEY);
			wp_redirect(
				'/wp-admin/options-general.php?page=challenge&message=refreshed',
			);
			die();
		}
	}

	/**
	 * Returns a table of the data
	 */
	public function data_shortcode($atts, $content): string {
		// Including the JS vs checking each posts for the shortcode and enqueuing, went with including since the other seemed like overkill (for this plugin)

		ob_start();

		echo '<script src="' .
			plugin_dir_url(__FILE__) .
			'resources/build/global-min.js' .
			'"></script>';
		echo '<table id="applicant-challenge-table"></table>';

		$content = ob_get_clean();
		return $content;
	}
}
