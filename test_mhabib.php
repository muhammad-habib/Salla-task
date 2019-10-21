<?php
// Order Data
$order                        = new stdClass();
$order->total_cart            = 175;
$order->shipping_cost         = 50;
$order->customer_id           = 101;
$order->cart_content          = [
    ['product_id' => 1, 'product_name' => 'Book', 'product_price' => 5, 'category_id' => 10],
    ['product_id' => 2, 'product_name' => 'Pen', 'product_price' => 1, 'category_id' => 20],
    ['product_id' => 3, 'product_name' => 'Bag', 'product_price' => 120, 'category_id' => 30],
    ['product_id' => 4, 'product_name' => 'Notebook', 'product_price' => 35, 'category_id' => 40],
    ['product_id' => 5, 'product_name' => 'Pencil Case', 'product_price' => 14, 'category_id' => 50]
];
// Coupon Data
$coupon                        = new stdClass();
$coupon->type                  = 'percentage';         // fixed, percentage
$coupon->amount                = 20;                   // Based on type
$coupon->end_date              = strtotime("+1 day");
$coupon->minimum_amount        = 100;
$coupon->free_shipping         = false;                // true, false
$coupon->included_categories   = [10, 20];
$coupon->excluded_categories   = [50];
$coupon->included_products     = [3];
$coupon->excluded_products     = [4, 1];

function calculate_coupon_discount($order, $coupon) {
    if (is_expired_coupon($coupon->end_date))
        return 0;
    $valid_products_amount = calculate_valid_products_amount($order->cart_content, $coupon);
    if ($coupon->minimum_amount > $valid_products_amount)
        return 0;
    return calculate_coupon_amount_based_on_type($valid_products_amount, $coupon->type, $coupon->amount) + get_shipping_amount($coupon->free_shipping, $order->shipping_cost);
}

/**
 * check if Coupon Time is Expired
 *
 * @param $coupon_time int coupon time in TimeStamp
 * @return bool
 */
function is_expired_coupon($coupon_time) {
    return time() >= $coupon_time ? true : false;
}

/**
 * calculate price of products with included in coupon products or categories
 *
 * @param $products array of products
 * @param $coupon stdClass object of Coupon
 * @return float amount
 */
function calculate_valid_products_amount($products, $coupon) {
    $valid_products_amount = 0;
    foreach ($products as $product) {
        if (
            (in_array($product['category_id'],$coupon->included_categories)
            && !in_array($product['product_id'],$coupon->excluded_products))
            ||
            (in_array($product['category_id'],$coupon->excluded_categories)
            && in_array($product['product_id'],$coupon->included_products))
        ) {
            $valid_products_amount += $product['product_price'];
        }
    }
    return $valid_products_amount;
}

/**
 * calculate amount based on type of Coupon Percentage or fixed amount
 *
 * @param $valid_amount
 * @param $coupon_type
 * @param $coupon_amount
 * @return float|int
 */
function calculate_coupon_amount_based_on_type($valid_amount, $coupon_type, $coupon_amount) {
    return $coupon_type == 'percentage' ? $valid_amount * ($coupon_amount/100) : $coupon_amount;
}

/**
 * @param $free_shipping
 * @param $shipping_cost
 * @return int
 */
function get_shipping_amount($free_shipping, $shipping_cost) {
    return $free_shipping ? $shipping_cost : 0;
}
