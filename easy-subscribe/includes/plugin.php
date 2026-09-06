<?php

namespace Devnet\EasySubscribe\Includes;

use Devnet\EasySubscribe\Admin\EasySubscribe_Admin;
use Devnet\EasySubscribe\Frontend\EasySubscribe_Public;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Plugin {
    protected $plugin_name;

    protected $version;

    public function __construct() {
        if ( defined( 'DEVNET_ESUB_VERSION' ) ) {
            $this->version = DEVNET_ESUB_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'easy-subscribe';
        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/defaults.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/notifier.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helper.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/options.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings-api.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/public.php';
    }

    public function run() {
        new EasySubscribe_Admin($this->get_plugin_name(), $this->get_version());
        new EasySubscribe_Public($this->get_plugin_name(), $this->get_version());
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

}
