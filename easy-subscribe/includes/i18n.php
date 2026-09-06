<?php

namespace Devnet\EasySubscribe\Includes;


if (!defined('ABSPATH')) {
	exit;
}


class i18n
{

	public function __construct()
	{
		add_action('init', [$this, 'load_plugin_textdomain']);
	}

	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'easy-subscribe',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
