<?php

/**
 * Single variation cart button
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

global $product;

?>
<div class="woocommerce-variation-add-to-cart variations_button">
	<?php do_action('woocommerce_before_add_to_cart_button'); ?>

	<?php
	do_action('woocommerce_before_add_to_cart_quantity');
	?>

	<div class="quantity-wrapper woocommerce-quantity-wrapper">
		<div class="label"><label for="quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></label></div>
		<div class="quantity-input-wrapper">
			<button type="button" class="qty-minus">-</button>
			<?php
			woocommerce_quantity_input(
				array(
					'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
					'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
					'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
				)
			);
			?>
			<button type="button" class="qty-plus">+</button>
		</div>
		
	</div>

	<?php
	do_action('woocommerce_after_add_to_cart_quantity');
	?>

	<button type="submit" data-product-id="<?php echo absint($product->get_id()); ?>" id="woo-add-to-cart" class="single_add_to_cart_button button alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>

	<?php do_action('woocommerce_after_add_to_cart_button'); ?>
	<div class="product-info d-flex">
		<p><?php echo esc_html_e('If you need custom Product,', 'woocommerce'); ?></p>
		<a href="<?php echo esc_url(home_url('/contact-us')); ?>"><?php echo esc_html_e('contact us.', 'woocommerce'); ?></a>
	</div>
	<input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>