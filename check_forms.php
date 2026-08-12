<?php
require 'wp-load.php';
$f1 = get_post_meta(1264);
$f2 = get_post_meta(1265);
echo "FORM 1264 META:\n";
print_r($f1);
echo "\nFORM 1265 META:\n";
print_r($f2);

// Let's also set the prices if they are empty
if (empty($f1['_simpay_price_options'])) {
    $price_options = array(
        'prices' => array(
            'default' => array(
                'amount' => 150,
                'currency' => 'USD',
                'recurring' => 'yes',
                'billing_period' => 'month',
                'billing_interval' => 1,
            )
        )
    );
    update_post_meta(1264, '_simpay_price_options', $price_options);
    update_post_meta(1264, '_simpay_amount', 150);
    echo "Updated form 1264 prices.\n";
}

if (empty($f2['_simpay_price_options'])) {
    $price_options = array(
        'prices' => array(
            'default' => array(
                'amount' => 299,
                'currency' => 'USD',
                'recurring' => 'yes',
                'billing_period' => 'month',
                'billing_interval' => 1,
            )
        )
    );
    update_post_meta(1265, '_simpay_price_options', $price_options);
    update_post_meta(1265, '_simpay_amount', 299);
    echo "Updated form 1265 prices.\n";
}
