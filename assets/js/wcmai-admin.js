jQuery(function ($) {

    let mediaFrame = null;
    let defaultFrame = null;

    /**
     * Product edit: Upload per-channel image.
     */
    $(document).on('click', '.wcmai-btn-upload', function (e) {
        e.preventDefault();

        const $btn     = $(this);
        const channel  = $btn.data('channel');
        const $block   = $btn.closest('.wcmai-channel-block');
        const $input   = $block.find('.wcmai-channel-url[data-channel="' + channel + '"]');
        const $preview = $block.find('.wcmai-channel-preview[data-channel="' + channel + '"]');

        if (!mediaFrame) {
            mediaFrame = wp.media({
                title: 'Select image',
                button: { text: 'Use this image' },
                multiple: false
            });
        }

        mediaFrame.off('select').on('select', function () {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            $input.val(attachment.url);
            $preview.html('<img src="' + attachment.url + '" style="max-width:100%;height:auto;border:1px solid #ddd;">');
        });

        mediaFrame.open();
    });

    /**
     * Product edit: Reset channel image (set Not set).
     */
    $(document).on('click', '.wcmai-btn-reset', function (e) {
        e.preventDefault();
        const $btn     = $(this);
        const channel  = $btn.data('channel');
        const $block   = $btn.closest('.wcmai-channel-block');
        const $input   = $block.find('.wcmai-channel-url[data-channel="' + channel + '"]');
        const $preview = $block.find('.wcmai-channel-preview[data-channel="' + channel + '"]');

        $input.val('');
        $preview.html('<em style="color:#777;">No image selected. Featured image will be used as base for AI.</em>');
    });

    /**
     * Product edit: Generate AI clean image from featured image for a specific channel.
     */
    $(document).on('click', '.wcmai-btn-ai', function (e) {
        e.preventDefault();

        const $btn      = $(this);
        const productId = $btn.data('product-id');
        const channel   = $btn.data('channel');
        const $block    = $btn.closest('.wcmai-channel-block');
        const $preview  = $block.find('.wcmai-channel-preview[data-channel="' + channel + '"]');
        const $input    = $block.find('.wcmai-channel-url[data-channel="' + channel + '"]');
        const $spinner  = $block.find('.wcmai-ai-spinner');

        if (!productId) {
            return;
        }

        $btn.prop('disabled', true);
        $spinner.show();

        $.post(
            WCMai.ajaxUrl,
            {
                action: 'wcmai_remove_bg',
                nonce: WCMai.nonce,
                product_id: productId,
                channel: channel
            }
        ).done(function (response) {
            if (response && response.success && response.data && response.data.url) {
                $input.val(response.data.url);
                $preview.html(response.data.html);
            } else if (response && response.data && response.data.message) {
                alert(response.data.message);
            } else {
                alert(WCMai.error);
            }
        }).fail(function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                alert(xhr.responseJSON.data.message);
            } else {
                alert(WCMai.error);
            }
        }).always(function () {
            $btn.prop('disabled', false);
            $spinner.hide();
        });
    });

    /**
     * Settings page: select default channel images.
     */
    $(document).on('click', '.wcmai-select-default-image', function (e) {
        e.preventDefault();

        const $btn   = $(this);
        const $wrap  = $btn.closest('.wcmai-default-image-wrap');
        const $input = $wrap.find('.wcmai-default-image-id');
        const $prev  = $wrap.find('.wcmai-default-preview');

        if (!defaultFrame) {
            defaultFrame = wp.media({
                title: 'Select default image',
                button: { text: 'Use this image' },
                multiple: false
            });
        }

        defaultFrame.off('select').on('select', function () {
            const attachment = defaultFrame.state().get('selection').first().toJSON();
            $input.val(attachment.id);
            $prev.html('<img src="' + attachment.url + '" style="max-width:150px;height:auto;border:1px solid #ddd;">');
        });

        defaultFrame.open();
    });

    $(document).on('click', '.wcmai-clear-default-image', function (e) {
        e.preventDefault();
        const $wrap = $(this).closest('.wcmai-default-image-wrap');
        $wrap.find('.wcmai-default-image-id').val('');
        $wrap.find('.wcmai-default-preview').html('<em style="color:#777;">No image selected.</em>');
    });

    /**
     * Helper: open media frame for bulk cell.
     */
    function wcmaiOpenMediaForCell(callback) {
        if (!mediaFrame) {
            mediaFrame = wp.media({
                title: 'Select image',
                button: { text: 'Use this image' },
                multiple: false
            });
        }

        mediaFrame.off('select').on('select', function () {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            callback(attachment.url);
        });

        mediaFrame.open();
    }

    /**
     * Bulk editor: AI Remove BG button.
     */
    $(document).on('click', '.wcmai-bulk-ai', function (e) {
        e.preventDefault();

        const $btn      = $(this);
        const productId = $btn.data('product-id');
        const channel   = $btn.data('channel');
        const $cell     = $btn.closest('.wcmai-cell');
        const $preview  = $cell.find('.wcmai-cell-preview');
        const $spinner  = $cell.find('.wcmai-bulk-spinner');

        if (!productId) {
            return;
        }

        $btn.prop('disabled', true);
        $spinner.show();

        $.post(
            WCMai.ajaxUrl,
            {
                action: 'wcmai_remove_bg',
                nonce: WCMai.nonce,
                product_id: productId,
                channel: channel
            }
        ).done(function (response) {
            if (response && response.success && response.data && response.data.url) {
                const html = '<img src="' + response.data.url + '" style="max-width:80px;height:auto;border:1px solid #ddd;">' +
                             '<br><code>' + response.data.url + '</code>';
                $preview.html(html);
            } else if (response && response.data && response.data.message) {
                alert(response.data.message);
            } else {
                alert(WCMai.error);
            }
        }).fail(function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                alert(xhr.responseJSON.data.message);
            } else {
                alert(WCMai.error);
            }
        }).always(function () {
            $btn.prop('disabled', false);
            $spinner.hide();
        });
    });

    /**
     * Bulk editor: Upload Image button.
     */
    $(document).on('click', '.wcmai-bulk-upload', function (e) {
        e.preventDefault();

        const $btn      = $(this);
        const productId = $btn.data('product-id');
        const channel   = $btn.data('channel');
        const $cell     = $btn.closest('.wcmai-cell');
        const $preview  = $cell.find('.wcmai-cell-preview');
        const $spinner  = $cell.find('.wcmai-bulk-spinner');

        if (!productId) {
            return;
        }

        wcmaiOpenMediaForCell(function (imageUrl) {
            $spinner.show();
            $btn.prop('disabled', true);

            $.post(
                WCMai.ajaxUrl,
                {
                    action: 'wcmai_set_channel_image',
                    nonce: WCMai.nonce,
                    product_id: productId,
                    channel: channel,
                    image_url: imageUrl
                }
            ).done(function (response) {
                if (response && response.success && response.data && response.data.url) {
                    const html = '<img src="' + response.data.url + '" style="max-width:80px;height:auto;border:1px solid #ddd;">' +
                                 '<br><code>' + response.data.url + '</code>';
                    $preview.html(html);
                } else if (response && response.data && response.data.message) {
                    alert(response.data.message);
                } else {
                    alert(WCMai.error);
                }
            }).fail(function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    alert(xhr.responseJSON.data.message);
                } else {
                    alert(WCMai.error);
                }
            }).always(function () {
                $spinner.hide();
                $btn.prop('disabled', false);
            });
        });
    });

    /**
     * Bulk editor: Reset button.
     */
    $(document).on('click', '.wcmai-bulk-reset', function (e) {
        e.preventDefault();

        const $btn      = $(this);
        const productId = $btn.data('product-id');
        const channel   = $btn.data('channel');
        const $cell     = $btn.closest('.wcmai-cell');
        const $preview  = $cell.find('.wcmai-cell-preview');
        const $spinner  = $cell.find('.wcmai-bulk-spinner');

        if (!productId) {
            return;
        }

        $spinner.show();
        $btn.prop('disabled', true);

        $.post(
            WCMai.ajaxUrl,
            {
                action: 'wcmai_delete_channel_image',
                nonce: WCMai.nonce,
                product_id: productId,
                channel: channel
            }
        ).done(function (response) {
            if (response && response.success) {
                $preview.html('<em title="Not set (your exporter can fall back to the featured image or global default).">Not set</em>');
            } else if (response && response.data && response.data.message) {
                alert(response.data.message);
            } else {
                alert(WCMai.error);
            }
        }).fail(function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                alert(xhr.responseJSON.data.message);
            } else {
                alert(WCMai.error);
            }
        }).always(function () {
            $spinner.hide();
            $btn.prop('disabled', false);
        });
    });

});
