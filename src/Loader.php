<?php

namespace Nickstewart\Challenge;

use Jenssegers\Blade\Blade;

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
		//
	}

	/**
	 * Setup filters
	 */
	public function init_filters(): void {
		//
	}
}
