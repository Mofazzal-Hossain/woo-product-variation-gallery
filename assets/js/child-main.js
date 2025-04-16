jQuery(document).ready(function ($) {


    // Initially disable color swatches
    function updateColorSwatchesState() {
        var sizeSelected = $('select[name="attribute_pa_product-size"]').val() && $('select[name="attribute_pa_product-size"]').val() !== '';
        $('.color-swatches-wrapper input[type="radio"]').prop('disabled', !sizeSelected);
        $('.color-swatches-wrapper label').toggleClass('disabled-label', !sizeSelected);
        $('select[name="attribute_pa_color"]').prop('disabled', !sizeSelected);
    }

    // Run on page load
    updateColorSwatchesState();

    // Run when size selection changes
    $('select[name="attribute_pa_product-size"]').on('change', function () {
        updateColorSwatchesState();

        // Clear color selection when size changes
        $('.color-swatches-wrapper input[type="radio"]').prop('checked', false);
        $('select[name="attribute_pa_color"]').val('');
    });

    // Handle color swatch clicks
    $('.color-swatches-wrapper').on('click', 'input[type="radio"]', function () {
        if (!$(this).prop('disabled')) {
            var value = $(this).val();
            $('select[name="attribute_pa_color"]').val(value).trigger('change');
        }
    });

    // Handle color swatch selection
    $('.color-swatches-wrapper').on('change', 'input[type="radio"]', function () {
        var attributeName = $(this).closest('.color-swatches-wrapper').data('attribute_name');
        var value = $(this).val();
        var attributeTitle = $(this).parent().find('label').attr('title');

        $(".variation label[for='pa_color']").html("COLOR: " + '<span>' + attributeTitle + '</span>');

        // Update the hidden select
        $('select[name="' + attributeName.replace('attribute_', '') + '"]').val(value).trigger('change');

        // Trigger WooCommerce's variation detection
        $('form.variations_form').trigger('woocommerce_variation_select_change');
        $('form.variations_form').trigger('check_variations');
    });

    // Initialize selected state
    $('.color-swatches-wrapper input[type="radio"]:checked').each(function () {
        $(this).trigger('change');
    });

    $('.reset_variations').on('click', function () {
        $('.color-swatches-wrapper input[type="radio"]').prop('checked', false);
        $('select[name="attribute_pa_color"]').val('').trigger('change');
        $('form.variations_form').trigger('woocommerce_variation_select_change');
        $('form.variations_form').trigger('check_variations');
        $(".variation label[for='pa_color']").html("COLOR");
    });

    $('.qty-plus, .qty-minus').on('click', function (e) {

        console.log('event');
        // Get the current quantity input
        var qtyInput = $(".woocommerce-quantity-wrapper input.qty");
        var currentVal = parseFloat(qtyInput.val());
        console.log(currentVal);
        var max = parseFloat(qtyInput.attr('max'));
        var min = parseFloat(qtyInput.attr('min'));
        var step = parseFloat(qtyInput.attr('step')) || 1;

        // Handle plus button
        if ($(this).hasClass('qty-plus')) {
            if (max && currentVal >= max) {
                qtyInput.val(max);
            } else {
                qtyInput.val(currentVal + step);
            }
        }
        // Handle minus button
        else {
            if (min && currentVal <= min) {
                qtyInput.val(min);
            } else if (currentVal > 0) {
                qtyInput.val(currentVal - step);
            }
        }

        // Trigger change event in case other scripts are listening
        qtyInput.trigger('change');
    });

    // Ensure quantity doesn't go below min when manually changed
    $(document).on('change', '.qty', function () {
        var min = parseFloat($(this).attr('min'));
        var currentVal = parseFloat($(this).val());

        if (min && currentVal < min) {
            $(this).val(min);
        }
    });

    $('form.variations_form').on('found_variation', function (event, variation) {
        var variationId = variation.variation_id;
        var selectedColor = $('input[name^="pa_color"]:checked').val();

        if (!selectedColor) return;
        // You can optionally check the attribute name or value here if needed
        console.log('Selected variation ID:', variationId);
    
        $.ajax({
            url: woocommerce_params.ajax_url,
            method: 'POST',
            data: {
                action: 'get_variation_gallery',
                variation_id: variationId
            },
            success: function (response) {
                if (response.success && response.data.image_urls.length > 0) {
                    var imageHtml = '';
                    var imageUrls = response.data.image_urls;
                    var $galleryWrapper = $('.woocommerce-product-gallery');

                    // Clear current gallery
                    $galleryWrapper.empty();

                    // Featured image
                    var featuredSrc = imageUrls[0];


                    imageHtml += `
                        <div class="woocommerce-product-gallery__image">
                            <a href="${featuredSrc}" data-fancybox="gallery">
                                <img src="${featuredSrc}" />
                            </a>
                        </div>
                    `;

                    // Thumbnails
                    imageHtml += '<div class="woocommerce-product-gallery__thumbs">';
                    imageUrls.forEach(function (src, index) {
                        const isActive = index === 0 ? 'active' : '';
                        imageHtml += `
                            <div class="product-thumbs">
                                <a href="${src}" class="${isActive}">
                                    <img src="${src}" />
                                </a>
                            </div>
                        `;
                    });
                    imageHtml += '</div>';

                    $galleryWrapper.append(imageHtml);
                    $('[data-fancybox="gallery"]').fancybox({
                        loop: true,
                        buttons: [
                            "zoom",
                            "slideShow",
                            "fullScreen",
                            "close"
                        ],
                    });

                    // Delegate thumb click
                    $galleryWrapper.on('click', '.woocommerce-product-gallery__thumbs a', function (e) {
                        e.preventDefault();

                        var newSrc = $(this).attr('href');

                        // Update featured image src and href
                        $galleryWrapper.find('.woocommerce-product-gallery__image a').attr('href', newSrc);
                        $galleryWrapper.find('.woocommerce-product-gallery__image img').attr('src', newSrc);

                        // Update active class
                        $galleryWrapper.find('.woocommerce-product-gallery__thumbs a').removeClass('active');
                        $(this).addClass('active');
                    });
                }
            }
        });
    });

});
