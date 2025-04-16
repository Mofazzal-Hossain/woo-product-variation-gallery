<?php

/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

defined('ABSPATH') || exit;

global $product;


$attribute_keys  = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="variations_form cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. 
																																																																						?>">
	<?php do_action('woocommerce_before_variations_form'); ?>
	<?php if (empty($available_variations) && false !== $available_variations) : ?>
		<p class="stock out-of-stock"><?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?></p>
	<?php else : ?>
		<div class="variations" cellspacing="0" role="presentation">
			
				<?php foreach ($attributes as $attribute_name => $options) : ?>
					<div class="variation">
						<div class="label"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); ?></label></div>
						<div class="value">
							<?php
							if ($attribute_name === 'pa_product-size') {
								// Size attribute
								echo '<div class="size-attribute-wrapper">';
								echo '<p class="size-description">Width × Depth × Height in MM</p>';

								wc_dropdown_variation_attribute_options([
									'options'   => $options,
									'attribute' => $attribute_name,
									'product'   => $product,
									'show_option_none' => __('Choose a size', 'woocommerce')
								]);

								echo '</div>';
							}
							if ($attribute_name === 'pa_color') {
								$terms = wc_get_product_terms($product->get_id(), $attribute_name, array('fields' => 'all'));

								echo '<div class="color-swatches-wrapper" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute_name)) . '">';
								foreach ($terms as $term) {
									if (in_array($term->slug, $options)) {
										$color = get_term_meta($term->term_id, 'color_value', true);
										echo '<div class="color-swatch-option">';
										echo '<input type="radio" name="' . esc_attr($attribute_name) . '" id="' . esc_attr($attribute_name . '_' . $term->slug) . '" value="' . esc_attr($term->slug) . '" disabled="disabled">';
										echo '<label for="' . esc_attr($attribute_name . '_' . $term->slug) . '" style="background-color:' . esc_attr($color) . '" title="' . esc_attr($term->name) . '"></label>';
										echo '</div>';
									}
								}
								echo '</div>';

								// Add a hidden select element for WooCommerce's JS
								echo '<select name="' . esc_attr($attribute_name) . '" id="' . esc_attr(sanitize_title($attribute_name)) . '" class="hidden-select" style="display:none" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute_name)) . '">';
								foreach ($options as $option) {
									echo '<option value="' . esc_attr($option) . '"></option>';
								}
								echo '</select>';
							} 

							// Reset variations link
							echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters(
								'woocommerce_reset_variations_link',
								'<a class="reset_variations" href="#" aria-label="' . esc_attr__('Clear options', 'woocommerce') . '">' .
									esc_html__('Clear', 'woocommerce') . '</a>'
							)) : '';
							?>
						</div>
					</div>
				<?php endforeach; ?>
		</div>
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action('woocommerce_after_variations_table'); ?>

		<div class="single_variation_wrap">
			<?php
			/**
			 * Hook: woocommerce_before_single_variation.
			 */
			do_action('woocommerce_before_single_variation');

			/**
			 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
			 *
			 * @since 2.4.0
			 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
			 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
			 */
			do_action('woocommerce_single_variation');

			/**
			 * Hook: woocommerce_after_single_variation.
			 */
			do_action('woocommerce_after_single_variation');
			?>
		</div>
	<?php endif; ?>

	<?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php
do_action('woocommerce_after_add_to_cart_form');
