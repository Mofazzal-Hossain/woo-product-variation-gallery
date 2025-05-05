<?php

/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0');

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        [
            'hello-elementor-theme-style',
        ],
        HELLO_ELEMENTOR_CHILD_VERSION
    );
   
    // wp_enqueue_style('child-fancybox-style', get_stylesheet_directory_uri() . '/assets/css/jquery.fancybox.min.css', array());
    // wp_enqueue_script('child-fancybox-js', get_stylesheet_directory_uri() . '/assets/js/jquery.fancybox.min.js', array(), time(), true);
    wp_enqueue_style('child-main-style', get_stylesheet_directory_uri() . '/assets/css/child-main.css', array(), time());
    wp_enqueue_script('child-main-js', get_stylesheet_directory_uri() . '/assets/js/child-main.js', array('jquery'), time(), true);
   
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20);


// Enqueue color picker in admin
add_action('admin_enqueue_scripts', 'hello_child_admin_enqueue');
function hello_child_admin_enqueue()
{
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_media();


    wp_enqueue_style('child-admin-css', get_stylesheet_directory_uri() . '/assets/css/child-admin.css', array(), time());
    wp_enqueue_script('child-color-js', get_stylesheet_directory_uri() . '/assets/js/child-color.js', array('jquery', 'wp-color-picker'), time(), true);
    wp_enqueue_script('child-media-js', get_stylesheet_directory_uri() . '/assets/js/child-media.js', array('jquery', 'wp-util'), time(), true);

    wp_localize_script('child-media-js', 'variationGallery', array(
        'i18n' => array(
            'deleteConfirm' => __('Are you sure you want to delete this image?', 'woocommerce'),
            'deleteFailed' => __('Could not delete the image.', 'woocommerce'),
        ),
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));

}

// Add color field to attribute terms
add_action('pa_color_add_form_fields', 'add_color_attribute_field');
add_action('pa_color_edit_form_fields', 'edit_color_attribute_field', 10, 2);

function add_color_attribute_field()
{
?>
    <div class="form-field">
        <label for="term_meta[color_value]"><?php _e('Color', 'woocommerce'); ?></label>
        <input type="text" name="term_meta[color_value]" id="term_meta[color_value]" class="color-picker" value="#ffffff" />
        <p class="description"><?php _e('Select a color for this term', 'woocommerce'); ?></p>
    </div>
<?php
}

function edit_color_attribute_field($term, $taxonomy)
{
    $color_value = get_term_meta($term->term_id, 'color_value', true);
?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="term_meta[color_value]"><?php _e('Color', 'woocommerce'); ?></label>
        </th>
        <td>
            <input type="text" name="term_meta[color_value]" id="term_meta[color_value]" class="color-picker" value="<?php echo esc_attr($color_value) ? esc_attr($color_value) : '#ffffff'; ?>" />
            <p class="description"><?php _e('Select a color for this term', 'woocommerce'); ?></p>
        </td>
    </tr>
<?php
}

// Save color field
add_action('created_pa_color', 'save_color_attribute_field', 10, 2);
add_action('edited_pa_color', 'save_color_attribute_field', 10, 2);

function save_color_attribute_field($term_id, $tt_id)
{
    if (isset($_POST['term_meta'])) {
        $term_meta = get_term_meta($term_id, 'color_value', true);
        $cat_keys = array_keys($_POST['term_meta']);
        foreach ($cat_keys as $key) {
            if (isset($_POST['term_meta'][$key])) {
                $term_meta = $_POST['term_meta'][$key];
                update_term_meta($term_id, 'color_value', $term_meta);
            }
        }
    }
}


// Force variation matching
add_filter('woocommerce_find_matching_product_variation', function ($match, $data) {
    if (!$match) {
        global $product;
        $variations = $product->get_available_variations();

        foreach ($variations as $variation) {
            $match = true;
            foreach ($data as $key => $value) {
                if ($variation['attributes'][$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $variation['variation_id'];
            }
        }
    }
    return $match;
}, 10, 2);



// Add gallery field to variations
add_action('woocommerce_variation_options', 'add_variation_gallery_field', 10, 3);
function add_variation_gallery_field($loop, $variation_data, $variation)
{
    $image_id_string = get_post_meta($variation->ID, '_variation_image_gallery', true);
    ?>
    <div class="form-row form-row-full variation-gallery-wrapper">
        <label><?php _e('Variation Gallery Images', 'woocommerce'); ?></label>
        <div class="variation-gallery-container">
            <ul class="variation-gallery-images">
                <?php
                if ($image_id_string) {
                    $image_ids = explode(',', $image_id_string);
                    foreach ($image_ids as $img_id) {
                        $image = wp_get_attachment_image_src($img_id);
                        if ($image) {
                            echo '<li class="image" data-attachment_id="' . esc_attr($img_id) . '">
                                <img src="' . esc_url($image[0]) . '" />
                                <a href="#" class="delete remove-variation-gallery-image">×</a>
                            </li>';
                        }
                    }
                }
                ?>
            </ul>
            <input type="hidden" class="variation-gallery-ids" name="variation_image_gallery[<?php echo $loop; ?>]" value="<?php echo esc_attr($image_id_string); ?>" />
            <button type="button" class="upload-variation-gallery button"><?php _e('Add images', 'woocommerce'); ?></button>
            <button type="button" class="clear-variation-gallery button"><?php _e('Clear', 'woocommerce'); ?></button>
        </div>
    </div>
    <?php
}

// Save variation gallery images
add_action('woocommerce_save_product_variation', 'save_variation_gallery', 10, 2);
function save_variation_gallery($variation_id, $loop)
{
    if (isset($_POST['variation_image_gallery'][$loop])) {
        update_post_meta($variation_id, '_variation_image_gallery', sanitize_text_field($_POST['variation_image_gallery'][$loop]));
    } 
}


// Add AJAX handler for getting variation galleries
add_action('wp_ajax_get_variation_gallery', 'get_variation_gallery');
add_action('wp_ajax_nopriv_get_variation_gallery', 'get_variation_gallery');
function get_variation_gallery() {
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;

    if ($variation_id) {
        $gallery_ids = get_post_meta($variation_id, '_variation_image_gallery', true);
        $image_ids = array();

        // Get featured image
        $variation = wc_get_product($variation_id);

        // Add gallery images
        if ($gallery_ids) {
            $gallery_ids = explode(',', $gallery_ids);
            $image_ids = array_merge($image_ids, $gallery_ids);
        }

        // Convert image IDs to URLs
        $image_urls = array();
        foreach ($image_ids as $id) {
            $image_urls[] = wp_get_attachment_image_url($id, 'full');
        }

        if (!empty($image_urls)) {
            wp_send_json_success(array('image_urls' => $image_urls));
        }
    }

    wp_send_json_error();
}


// Redirect to checkout
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout');

function redirect_to_checkout($url)
{
    return esc_url(home_url('/inquiry-checkout'));
}

add_shortcode('enquiry_checkout', 'mooqs_enquiry_checkout');

function mooqs_enquiry_checkout()
{
    ob_start();
?>
    <div class="mooqs-inquiry-wrapper">
        <!-- Selected Items Section -->
        <div class="mooqs-selected-items">

            <?php if (WC()->cart->is_empty()) : ?>
                <h3 class="cart-empty-message"><?php esc_html_e('Your cart is currently empty.', 'woocommerce'); ?></h3>
            <?php else : ?>
                <h2 class="mooqs-inquiry-title"><?php esc_html_e('Selected Items', 'woocommerce'); ?></h2>
                <table class="mooqs-cart-items">
                    <tbody>
                        <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :


                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) :
                        ?>
                                <tr class="mooqs-cart-item">
                                    <!-- Product Image -->
                                    <td>
                                        <div class="product-info">
                                            <div class="product-thumbnail">
                                                <?php
                                                $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                                                echo $thumbnail;
                                                ?>
                                            </div>
                                            <div class="product-name">
                                                <?php
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_name', esc_html($_product->get_name()), $cart_item, $cart_item_key));
                                                ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="product-meta">
                                        <?php
                                        if (isset($cart_item['variation']['attribute_pa_product-size'])) { ?>
                                            <div class="product-size">
                                                <h3 class="product-size-label td-table">
                                                    <?php echo esc_html__('Size', 'woocommerce') ?>
                                                </h3>
                                                <div class="product-size-value">
                                                    <?php echo esc_html($cart_item['variation']['attribute_pa_product-size']); ?>
                                                </div>
                                            </div>
                                        <?php }
                                        ?>
                                    </td>

                                    <td class="product-meta">
                                        <?php
                                        if (isset($cart_item['variation']['attribute_pa_color'])) { ?>
                                            <div class="product-color">
                                                <h3 class="product-color-label td-table">
                                                    <?php echo esc_html__('Color', 'woocommerce') ?>
                                                </h3>
                                                <div class="product-color-value">
                                                    <?php
                                                    $color_slug = $cart_item['variation']['attribute_pa_color'];

                                                    if ($color_slug) {
                                                        // Get the color term
                                                        $color_term = get_term_by('slug', $color_slug, 'pa_color');

                                                        if ($color_term) {
                                                         
                                                            $color_code = get_term_meta($color_term->term_id, 'color_value', true);

                                                            if ($color_code) {
                                                                echo '<span class="color-swatch" style="background-color: ' . esc_attr($color_code) . '"></span>';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </td>

                                    <!-- Quantity -->
                                    <td class="product-quantity">
                                        <h3 class="quantity-label td-table">
                                            <?php echo esc_html__('Quantity', 'woocommerce'); ?>
                                        </h3>
                                        <div class="quantity-value">
                                            <?php echo esc_html($cart_item['quantity']); ?>
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <?php if(is_user_logged_in()): ?>
                                        <td class="product-price">
                                            <h3 class="price-label td-table">
                                                <?php echo esc_html__('Price', 'woocommerce'); ?>
                                            </h3>
                                            <div class="price-value">
                                                <span class="currency-symbol">
                                                    <?php echo esc_html(get_woocommerce_currency_symbol()); ?>
                                                </span>
                                                <?php echo esc_html($cart_item['line_total']); ?>
                                            </div>
                                        </td>    
                                    <?php endif; ?>

                                    <!-- Remove -->
                                    <td class="product-remove">
                                        <div class="remove">
                                            <?php
                                            echo apply_filters('woocommerce_cart_item_remove_link', sprintf(
                                                '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><img src="%s"></a>',
                                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                esc_html__('Remove this item', 'woocommerce'),
                                                esc_attr($product_id),
                                                esc_attr($_product->get_sku()),
                                                esc_url(get_stylesheet_directory_uri() . '/assets/images/remove.svg')
                                            ), $cart_item_key);
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="mooqs-inquiry-container">
            <h2 class="mooqs-inquiry-title"><?php echo esc_html_e('Inquiry details', 'woocommerce'); ?></h2>
            <form class="woocommerce-checkout" method="post">
                <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>

                <!-- Name Field -->
                <div class="form-row">
                    <label for="inquiry_name"><?php echo esc_html_e('Name', 'woocommerce'); ?></label>
                    <input type="text" class="input-text" name="inquiry_name" id="inquiry_name" required value="<?php echo esc_attr($_POST['inquiry_name'] ?? ''); ?>" placeholder="<?php echo esc_attr_e('Enter your name', 'woocommerce'); ?>">
                </div>

                <!-- Company Name Field -->
                <div class="form-row">
                    <label for="inquiry_company"><?php echo esc_html_e('Company Name', 'woocommerce'); ?></label>
                    <input type="text" class="input-text" name="inquiry_company" id="inquiry_company" value="<?php echo esc_attr($_POST['inquiry_company'] ?? ''); ?>" placeholder="<?php echo esc_attr_e('Enter your company name', 'woocommerce'); ?>">
                </div>

                <!-- Address Field -->
                <div class="form-row">
                    <label for="inquiry_address"><?php echo esc_html_e('Address', 'woocommerce'); ?></label>
                    <input type="text" class="input-text" name="inquiry_address" id="inquiry_address" value="<?php echo esc_attr($_POST['inquiry_address'] ?? ''); ?>" placeholder="<?php echo esc_attr_e('Enter your address', 'woocommerce'); ?>">
                </div>

                <!-- Email Field -->
                <div class="form-row">
                    <label for="inquiry_email"><?php echo esc_html_e('Email', 'woocommerce'); ?></label>
                    <input type="email" class="input-text" name="inquiry_email" id="inquiry_email" required value="<?php echo esc_attr($_POST['inquiry_email'] ?? ''); ?>" placeholder="<?php echo esc_attr_e('Enter your email address', 'woocommerce'); ?>">
                </div>

                <!-- Phone Number Field -->
                <div class="form-row">
                    <label for="inquiry_phone"><?php echo esc_html_e('Phone Number', 'woocommerce'); ?></label>
                    <input type="tel" class="input-text" name="inquiry_phone" id="inquiry_phone" value="<?php echo esc_attr($_POST['inquiry_phone'] ?? ''); ?>" placeholder="<?php echo esc_attr_e('Enter your phone number', 'woocommerce'); ?>">
                </div>

                <!-- VAT Number Field -->
                <div class="form-row">
                    <label for="inquiry_vat"><?php echo esc_html_e('VAT Number', 'woocommerce'); ?></label>
                    <input type="text" class="input-text" name="inquiry_vat" id="inquiry_vat" value="<?php echo esc_attr($_POST['inquiry_vat'] ?? ''); ?>" placeholder="<?php echo esc_attr_e('Enter your VAT number', 'woocommerce'); ?>">
                </div>

                <!-- Password Field (for account creation) -->
                <div class="form-row">
                    <label for="inquiry_password"><?php echo esc_html_e('Password', 'woocommerce'); ?></label>
                    <input type="password" class="input-text" name="inquiry_password" id="inquiry_password" required value="" placeholder="<?php echo esc_attr_e('Enter your password', 'woocommerce'); ?>">
                </div>

                <!-- Postcode/ZIP Field -->
                <div class="form-row">
                    <label for="inquiry_postcode"><?php echo esc_html_e('Postcode/ZIP', 'woocommerce'); ?></label>
                    <input type="text" class="input-text" name="inquiry_postcode" id="inquiry_postcode" value="<?php echo esc_attr($_POST['inquiry_postcode'] ?? ''); ?>" placeholder="<?php echo esc_attr_e('Enter your postcode/ZIP', 'woocommerce'); ?>">
                </div>

                <div class="inquiry-form-bottom">
                    <div class="form-row privacy-policy">
                        <p><?php echo esc_html_e('Your personal data will be used to process your article, support your experience throughout this website, and for other purposes described in our', 'woocommerce'); ?> <a href="<?php echo esc_url(get_privacy_policy_url()); ?>" target="_blank"><?php echo esc_html_e('Privacy Policy', 'woocommerce'); ?></a>.</p>
                    </div>

                    <button type="submit" class="submit-btn alt" name="woocommerce_checkout_place_order" id="place_inquiry">
                        <?php echo esc_html_e('Place Inquiry', 'woocommerce'); ?>
                    </button>
                </div>
            </form>
        </div>
    <?php
    return ob_get_clean();
}


// Handle the custom inquiry form submission
add_action('template_redirect', 'handle_inquiry_checkout');
function handle_inquiry_checkout()
{

    if (is_page('inquiry-checkout') && isset($_POST['woocommerce_checkout_place_order'])) {


        // Verify nonce
        if (!isset($_POST['woocommerce-process-checkout-nonce']) || !wp_verify_nonce($_POST['woocommerce-process-checkout-nonce'], 'woocommerce-process_checkout')) {
            wc_add_notice('Nonce verification failed. Please try again.', 'error');
            return;
        }
        $_POST['payment_method'] = 'bacs'; // Dummy payment method
        $_POST['terms'] = 1;

        // Validate required fields
        $required_fields = array(
            'inquiry_name' => 'Name',
            'inquiry_email' => 'Email',
            'inquiry_password' => 'Password'
        );

        foreach ($required_fields as $field_key => $field_name) {
            if (empty($_POST[$field_key])) {
                wc_add_notice(sprintf('%s is a required field.', $field_name), 'error');
            }
        }

        // Validate email
        if (!empty($_POST['inquiry_email']) && !is_email($_POST['inquiry_email'])) {
            wc_add_notice('Please enter a valid email address.', 'error');
        }

        // If there are errors, stop processing
        if (wc_notice_count('error') > 0) {
            return;
        }

        // Check if cart is empty
        if (WC()->cart->is_empty()) {
            wc_add_notice('Your cart is empty. Please add products before placing an inquiry.', 'error');
            return;
        }

        // Process user registration
        $email = sanitize_email($_POST['inquiry_email']);
        $username = sanitize_user($_POST['inquiry_email']);
        $password = $_POST['inquiry_password'];

        // Check if user already exists
        if (email_exists($email)) {
            $user = get_user_by('email', $email);
        } else {
            // Create new user
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                wc_add_notice($user_id->get_error_message(), 'error');
                return;
            }

            $user = get_user_by('id', $user_id);

            // Set additional user meta
            update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['inquiry_name']));
            update_user_meta($user_id, 'billing_company', sanitize_text_field($_POST['inquiry_company']));
            update_user_meta($user_id, 'billing_address_1', sanitize_text_field($_POST['inquiry_address']));
            update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['inquiry_phone']));
            update_user_meta($user_id, 'billing_vat', sanitize_text_field($_POST['inquiry_vat']));
            update_user_meta($user_id, 'billing_postcode', sanitize_text_field($_POST['inquiry_postcode']));
        }

        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);

        // Create WooCommerce order
        $order = wc_create_order(array(
            'customer_id' => $user->ID,
            'status' => 'pending' // or 'on-hold' for inquiries
        ));

        // Add cart items to order
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $order->add_product(
                $product,
                $cart_item['quantity'],
                array(
                    'variation' => $cart_item['variation'],
                    'totals' => array(
                        'subtotal' => $cart_item['line_subtotal'],
                        'total' => $cart_item['line_total']
                    )
                )
            );
        }

        // Set order meta
        $order->set_address(array(
            'first_name' => sanitize_text_field($_POST['inquiry_name']),
            'company'    => sanitize_text_field($_POST['inquiry_company']),
            'address_1'  => sanitize_text_field($_POST['inquiry_address']),
            'phone'      => sanitize_text_field($_POST['inquiry_phone']),
            'email'      => sanitize_email($_POST['inquiry_email'])
        ), 'billing');

        // Set order notes
        $order->add_order_note('VAT Number: ' . sanitize_text_field($_POST['inquiry_vat']));
        $order->add_order_note('This is a product inquiry order.');

        // Calculate totals and save
        $order->calculate_totals();
        $order->save();

        // Send inquiry email to admin
        send_inquiry_email($user, $order);

        // Empty the cart
        WC()->cart->empty_cart();

        // Redirect to order received page
        wp_redirect($order->get_checkout_order_received_url());
        exit;
    }
}

// Updated email function to include order details
function send_inquiry_email($user, $order)
{
    $to = get_option('admin_email');
    $subject = 'New Product Inquiry - Order #' . $order->get_order_number();

    $message = "You have received a new product inquiry:\n\n";
    $message .= "Order Number: #" . $order->get_order_number() . "\n";
    $message .= "Customer Name: " . $user->first_name . "\n";
    $message .= "Company: " . get_user_meta($user->ID, 'billing_company', true) . "\n";
    $message .= "Email: " . $user->user_email . "\n";
    $message .= "Phone: " . get_user_meta($user->ID, 'billing_phone', true) . "\n";
    $message .= "VAT: " . get_user_meta($user->ID, 'billing_vat', true) . "\n\n";
    $message .= "Products Inquired:\n";

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $message .= "- " . $item->get_name() . " (Qty: " . $item->get_quantity() . ")\n";
    }

    $message .= "\nOrder Total: " . $order->get_formatted_order_total() . "\n";
    $message .= "View Order: " . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . "\n";

    wp_mail($to, $subject, $message);

    // Also send email to customer
    $customer_subject = 'Your Product Inquiry - Order #' . $order->get_order_number();
    $customer_message = "Thank you for your inquiry. Here are your details:\n\n";
    $customer_message .= "Order Number: #" . $order->get_order_number() . "\n";
    $customer_message .= "Products Inquired:\n";

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $customer_message .= "- " . $item->get_name() . " (Qty: " . $item->get_quantity() . ")\n";
    }

    $customer_message .= "\nWe'll contact you shortly regarding your inquiry.\n";
    $customer_message .= "You can view this inquiry in your account at any time.\n";

    wp_mail($user->user_email, $customer_subject, $customer_message);
}

// Completely bypass checkout validation for our form
add_action('woocommerce_after_checkout_validation', 'bypass_checkout_validation', 10, 2);
function bypass_checkout_validation($data, $errors)
{
    if (is_page('inquiry-checkout')) {
        // Clear all errors - we'll handle our own validation
        $errors->errors = array();
    }
}

add_action('init', 'register_inquiry_order_status');
function register_inquiry_order_status()
{
    register_post_status('wc-inquiry', array(
        'label' => 'Inquiry',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Inquiry <span class="count">(%s)</span>', 'Inquiries <span class="count">(%s)</span>')
    ));
}

add_filter('wc_order_statuses', 'add_inquiry_to_order_statuses');
function add_inquiry_to_order_statuses($order_statuses)
{
    $order_statuses['wc-inquiry'] = 'Inquiry';
    return $order_statuses;
}



add_filter('wp_prepare_attachment_for_js', function($response, $attachment, $meta) {
    if ($response && isset($response['url'])) {
        $response['url'] = wp_get_attachment_url($attachment->ID);
    }
    return $response;
}, 10, 3);


// Hide all WooCommerce prices for non-logged-in users
add_filter('woocommerce_get_price_html', 'custom_hide_price_for_non_logged_in_users', 10, 2);
add_filter('woocommerce_variable_price_html', 'custom_hide_price_for_non_logged_in_users', 10, 2);

function custom_hide_price_for_non_logged_in_users($price, $product) {
    if (!is_user_logged_in()) {
        return;
    }
    return $price;
}
