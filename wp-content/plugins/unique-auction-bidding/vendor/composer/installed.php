<?php return array(
    'root' => array(
        'name' => 'unique-auction-bidding/plugin',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'stripe/stripe-php' => array(
            'pretty_version' => 'v17.3.0',
            'version' => '17.3.0.0',
            'reference' => 'cfe8244f7e5f910b7fdb5c2cf77428c0acbb9f7c',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'unique-auction-bidding/plugin' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
