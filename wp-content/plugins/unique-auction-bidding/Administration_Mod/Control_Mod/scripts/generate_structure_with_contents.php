<?php
if (!defined('ABSPATH')) {
    exit;
}

function generate_directory_tree_with_contents($dir, $prefix = '') {
    $tree = '';
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . '/' . $file;
            $tree .= $prefix . $file . "\n";
            if (is_dir($path)) {
                $tree .= generate_directory_tree_with_contents($path, $prefix . '  ');
            } else {
                $content = file_get_contents($path);
                $tree .= $prefix . '  Content: ' . (empty($content) ? '(empty)' : '[content omitted for brevity, see file]') . "\n";
            }
        }
    }
    return $tree;
}

$plugin_dir = ABSPATH . 'wp-content/plugins/unique-auction-bidding';
$theme_dir = ABSPATH . 'wp-content/themes/hello-child';
$output_file = WP_CONTENT_DIR . '/uploads/wc-logs/structure_with_contents.txt';

$tree = "Plugin Directory Tree with Contents:\n" . generate_directory_tree_with_contents($plugin_dir) . "\n";
$tree .= "Theme Directory Tree with Contents:\n" . generate_directory_tree_with_contents($theme_dir);

if (file_put_contents($output_file, $tree) === false) {
    wp_die('Failed to write structure_with_contents.txt. Check permissions for ' . $output_file);
} else {
    wp_die('Structure with contents generated. Check ' . $output_file);
}