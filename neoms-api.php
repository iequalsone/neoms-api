<?php
/*
 * Plugin Name: NEOMS Custom Rest Routes
 * Description: Custom endpoints for post types
 * Version: 1.0
 * Author: Jon
 */

include plugin_dir_path(__FILE__) . 'events/common/index.php';
include plugin_dir_path(__FILE__) . 'events/v1/website.php';
include plugin_dir_path(__FILE__) . 'events/v1/app.php';
include plugin_dir_path(__FILE__) . 'events/v2/website.php';
include plugin_dir_path(__FILE__) . 'events/v2/app.php';

include plugin_dir_path(__FILE__) . 'news/common/index.php';
include plugin_dir_path(__FILE__) . 'news/v1/web.php';

include plugin_dir_path(__FILE__) . 'opportunities/common/index.php';
include plugin_dir_path(__FILE__) . 'opportunities/v1/web.php';
