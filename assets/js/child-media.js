jQuery(document).ready(function ($) {
    // Track original values
    var originalGalleryValues = {};
    $('.variation-gallery-ids').each(function () {
        originalGalleryValues[$(this).attr('name')] = $(this).val();
    });

    // Enable save button when changes detected
    function checkForChanges() {
        var hasChanges = false;
        $('.variation-gallery-ids').each(function () {
            if ($(this).val() !== originalGalleryValues[$(this).attr('name')]) {
                hasChanges = true;
                return false;
            }
        });

        if (hasChanges) {
            $('.save-variation-changes')
                .prop('disabled', false)
                .removeClass('disabled')
                .addClass('button-primary');

            $('.variations_form').trigger('change');
            $('.variation-gallery-ids').trigger('change'); 
        }
    }


    // Image uploader
    $('body').on('click', '.upload-variation-gallery', function (e) {
        e.preventDefault();
        var $container = $(this).closest('.variation-gallery-container');
        var $input = $container.find('.variation-gallery-ids');

        var gallery = wp.media({
            title: 'Add Images to Variation Gallery',
            multiple: true,
            library: { type: 'image' },
            button: { text: 'Add to Gallery' }
        });

        gallery.on('select', function () {
            console.log('gallery select');
            var selection = gallery.state().get('selection');
            var attachment_ids = $input.val();

            selection.map(function (attachment) {
                attachment = attachment.toJSON();
                if (attachment.id) {
                    attachment_ids = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
                    $container.find('.variation-gallery-images').append(
                        '<li class="image" data-attachment_id="' + attachment.id + '">' +
                        '<img src="' + attachment.url + '" />' +
                        '<a href="#" class="delete remove-variation-gallery-image">×</a>' +
                        '</li>'
                    );
                }
            });

            $input.val(attachment_ids);
            checkForChanges();
        });

        gallery.open();
    });

    // Remove image
    $('body').on('click', '.remove-variation-gallery-image', function (e) {
        e.preventDefault();
        var $li = $(this).closest('li');
        var $input = $(this).closest('.variation-gallery-container').find('.variation-gallery-ids');
        var attachment_ids = $input.val().split(',').filter(Boolean);
        var index = attachment_ids.indexOf($li.data('attachment_id').toString());

        if (index !== -1) {
            attachment_ids.splice(index, 1);
            $input.val(attachment_ids.join(','));
            checkForChanges();
        }

        $li.remove();
    });

    // Clear gallery
    $('body').on('click', '.clear-variation-gallery', function (e) {
        e.preventDefault();
        var $container = $(this).closest('.variation-gallery-container');
        $container.find('.variation-gallery-images').empty();
        $container.find('.variation-gallery-ids').val('');
        checkForChanges();
    });

    // Update original values when saved
    $('body').on('click', '.save-variation-changes', function () {
        $('.variation-gallery-ids').each(function () {
            originalGalleryValues[$(this).attr('name')] = $(this).val();
        });
    });

    // Initialize
    checkForChanges();
});