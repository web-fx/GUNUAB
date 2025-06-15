<?php
if (!defined('ABSPATH')) {
    exit;
}

function generate_directory_tree($dir, $prefix = '') {
    $tree = '';
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . '/' . $file;
            $tree .= $prefix . $file . "\n";
            if (is_dir($path)) {
                $tree .= generate_directory_tree($path, $prefix . '  ');
            }
        }
    }
    return $tree;
}

$plugin_dir = ABSPATH . 'wp-content/plugins/unique-auction-bidding';
$theme_dir = ABSPATH . 'wp-content/themes/hello-child';
$output_file = WP_CONTENT_DIR . '/uploads/wc-logs/structure.txt';

$tree = "Plugin Directory Tree:\n" . generate_directory_tree($plugin_dir) . "\n";
$tree .= "Theme Directory Tree:\n" . generate_directory_tree($theme_dir);

if (file_put_contents($output_file, $tree) === false) {
    wp_die('Failed to write structure.txt. Check permissions for ' . $output_file);
} else {
    wp_die('Structure generated. Check ' . $output_file);
}