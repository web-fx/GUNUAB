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
$output_file = plugin_dir_path(__FILE__) . '../../structure.txt';

$tree = "Plugin Directory Tree:\n" . generate_directory_tree($plugin_dir) . "\n";
$tree .= "Theme Directory Tree:\n" . generate_directory_tree($theme_dir);

file_put_contents($output_file, $tree);
echo "Directory tree saved to $output_file";
