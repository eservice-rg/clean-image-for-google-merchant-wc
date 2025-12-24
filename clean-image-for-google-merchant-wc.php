<?php

/**
 * Plugin Name:       Clean Image for Google Merchant (WC)
 * Description:       Advanced product image management for Google Merchant and multi-channel feeds, with AI background removal (OpenAI), per-channel default images, bulk editor and CSV export.
 * Version:           1.3.1
 * Author:            Davide Puzzo
 * Author URI:        https://www.e-service-online.com
 * Text Domain:       clean-image-for-google-merchant-wc
 * Domain Path:       /languages
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'wmcaim_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wmcaim_fs() {
        global $wmcaim_fs;
        if ( !isset( $wmcaim_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
            $wmcaim_fs = fs_dynamic_init( array(
                'id'             => '22267',
                'slug'           => 'wc-multi-channel-ai-image-manager',
                'type'           => 'plugin',
                'public_key'     => 'pk_daac808a9da0da9742e90a4a9495b',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                    'support' => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $wmcaim_fs;
    }

    // Init Freemius.
    wmcaim_fs();
	// Signal that SDK was initiated.
	do_action( 'wmcaim_fs_loaded' );

}

/**
 * PRO upsell banner (shown only on the FREE version).
 *
 * NOTE: This must be outside the Freemius init wrapper, otherwise the notice
 * may not be hooked when a premium add-on loads first.
 */
if ( is_admin() ) {
	add_action( 'admin_notices', 'wmcaim_show_pro_upsell_notice' );
}

if ( ! function_exists( 'wmcaim_show_pro_upsell_notice' ) ) {
	function wmcaim_show_pro_upsell_notice() {
		if ( ! function_exists( 'wmcaim_fs' ) ) {
			return;
		}

		// If the customer can use premium code (or is paying), don't show upsell.
		try {
			if ( method_exists( wmcaim_fs(), 'can_use_premium_code' ) && wmcaim_fs()->can_use_premium_code() ) {
				return;
			}
		} catch ( Exception $e ) {
			// Fail silently.
		}

		if ( method_exists( wmcaim_fs(), 'is_not_paying' ) && ! wmcaim_fs()->is_not_paying() ) {
			return;
		}

		$upgrade_url = '';
		if ( method_exists( wmcaim_fs(), 'get_upgrade_url' ) ) {
			$upgrade_url = (string) wmcaim_fs()->get_upgrade_url();
		}
		if ( empty( $upgrade_url ) ) {
			// Fallback (direct checkout).
			$upgrade_url = 'https://checkout.freemius.com/plugin/22267/plan/37252/';
		}
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<strong>Boost your product images for all marketing channels!</strong><br>
				Unlock <strong>WC Multi-Channel AI Image Manager PRO</strong> to get:
			</p>
			<ul style="margin-left:20px;list-style:disc;">
				<li>AI Background Removal</li>
				<li>Automatic clean images for Google, Facebook, Pinterest, TikTok</li>
				<li>Bulk Image Editor + CSV Export</li>
				<li>Default fallback images per channel</li>
			</ul>
			<p>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer">
					Upgrade to Pro →
				</a>
			</p>
		</div>
		<?php
	}
}
class WC_Multichannel_AI_Image_Manager {
    const OPTION_API_KEY = 'wcmai_openai_api_key';

    const OPTION_DEFAULTS = 'wcmai_default_channel_images';

    const META_GM_IMAGE = '_gm_image_url';

    const META_FB_IMAGE = '_fb_image_url';

    const META_PT_IMAGE = '_pt_image_url';

    const META_TK_IMAGE = '_tk_image_url';

    public function __construct() {
        add_action( 'add_meta_boxes', [$this, 'add_product_metabox'] );
        add_action( 'save_post_product', [$this, 'save_product_meta'] );
        add_action( 'admin_enqueue_scripts', [$this, 'admin_assets'], 20 );
        // WooCommerce settings tab and global defaults: premium only.
        if ( function_exists( 'wmcaim_fs' ) && wmcaim_fs()->can_use_premium_code__premium_only() ) {
            add_filter( 'woocommerce_settings_tabs_array', [$this, 'add_settings_tab'], 50 );
            add_action( 'woocommerce_settings_tabs_wcmai_images', [$this, 'render_settings_tab'] );
            add_action( 'woocommerce_update_options_wcmai_images', [$this, 'save_settings_tab'] );
        }
        // Premium-only admin pages (Bulk Editor, CSV Export) and AI background removal.
        if ( function_exists( 'wmcaim_fs' ) && wmcaim_fs()->can_use_premium_code__premium_only() ) {
            add_action( 'admin_menu', [$this, 'register_admin_pages'] );
            add_action( 'admin_init', [$this, 'handle_csv_export'] );
            add_action( 'wp_ajax_wcmai_remove_bg', [$this, 'ajax_remove_background'] );
        }
        // These AJAX actions are useful for both free & pro (manual upload / reset).
        add_action( 'wp_ajax_wcmai_set_channel_image', [$this, 'ajax_set_channel_image'] );
        add_action( 'wp_ajax_wcmai_delete_channel_image', [$this, 'ajax_delete_channel_image'] );
    }

    /**
     * Map channel key to meta key.
     */
    protected function get_meta_key_for_channel( $channel ) {
        switch ( $channel ) {
            case 'fb':
                return self::META_FB_IMAGE;
            case 'pt':
                return self::META_PT_IMAGE;
            case 'tk':
                return self::META_TK_IMAGE;
            case 'gm':
            default:
                return self::META_GM_IMAGE;
        }
    }

    /**
     * Available channels definition.
     */
    protected function get_channels() {
        // Base free channel: Google Merchant only.
        $channels = [
            'gm' => [
                'label' => __( 'Google Merchant image', 'wc-multichannel-ai-image-manager' ),
                'meta'  => self::META_GM_IMAGE,
                'desc'  => __( 'WP All Export: use "_gm_image_url" as image_link for Google Merchant.', 'wc-multichannel-ai-image-manager' ),
            ],
        ];
        // Premium-only extra channels.
        if ( function_exists( 'wmcaim_fs' ) && wmcaim_fs()->can_use_premium_code__premium_only() ) {
            $channels['fb'] = [
                'label' => __( 'Facebook / Meta Shops image', 'wc-multichannel-ai-image-manager' ),
                'meta'  => self::META_FB_IMAGE,
                'desc'  => __( 'Use "_fb_image_url" in your Facebook catalog or feed plugin.', 'wc-multichannel-ai-image-manager' ),
            ];
            $channels['pt'] = [
                'label' => __( 'Pinterest image', 'wc-multichannel-ai-image-manager' ),
                'meta'  => self::META_PT_IMAGE,
                'desc'  => __( 'Use "_pt_image_url" for Pinterest product feeds.', 'wc-multichannel-ai-image-manager' ),
            ];
            $channels['tk'] = [
                'label' => __( 'TikTok Shop image', 'wc-multichannel-ai-image-manager' ),
                'meta'  => self::META_TK_IMAGE,
                'desc'  => __( 'Use "_tk_image_url" for TikTok Shop product feeds.', 'wc-multichannel-ai-image-manager' ),
            ];
        }
        return $channels;
    }

    /**
     * Metabox on product edit screen.
     */
    public function add_product_metabox() {
        add_meta_box(
            'wcmai_feed_images',
            __( 'Multi-Channel Feed Images (AI Ready)', 'wc-multichannel-ai-image-manager' ),
            [$this, 'render_product_metabox'],
            'product',
            'side',
            'default'
        );
    }

    public function render_product_metabox( $post ) {
        wp_nonce_field( 'wcmai_save_meta', 'wcmai_meta_nonce' );
        $thumb_id = get_post_thumbnail_id( $post->ID );
        $thumb_url = ( $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '' );
        $channels = $this->get_channels();
        foreach ( $channels as $key => $data ) {
            $meta_key = $data['meta'];
            $url = get_post_meta( $post->ID, $meta_key, true );
            $url = ( $url ? esc_url( $url ) : '' );
            ?>
            <div class="wcmai-channel-block" data-channel="<?php 
            echo esc_attr( $key );
            ?>" style="margin-bottom:16px;border-bottom:1px solid #eee;padding-bottom:12px;">
                <strong><?php 
            echo esc_html( $data['label'] );
            ?></strong>

                <p style="margin-top:6px;">
                    <input type="text"
                           class="widefat wcmai-channel-url"
                           data-channel="<?php 
            echo esc_attr( $key );
            ?>"
                           name="<?php 
            echo esc_attr( $meta_key );
            ?>"
                           readonly
                           value="<?php 
            echo $url;
            ?>">
                </p>

                <div class="wcmai-channel-preview" data-channel="<?php 
            echo esc_attr( $key );
            ?>" style="margin-bottom:8px;">
                    <?php 
            if ( $url ) {
                ?>
                        <img src="<?php 
                echo $url;
                ?>" style="max-width:100%;height:auto;border:1px solid #ddd;">
                    <?php 
            } elseif ( $thumb_url ) {
                ?>
                        <img src="<?php 
                echo esc_url( $thumb_url );
                ?>" style="max-width:100%;height:auto;border:1px dashed #ccc;">
                    <?php 
            } else {
                ?>
                        <em style="color:#777;"><?php 
                esc_html_e( 'No image selected. Featured image will be used as base for AI.', 'wc-multichannel-ai-image-manager' );
                ?></em>
                    <?php 
            }
            ?>
                </div>

                <p>
                    <button type="button"
                            class="button wcmai-btn-upload"
                            data-channel="<?php 
            echo esc_attr( $key );
            ?>">
                        <?php 
            esc_html_e( 'Select / Upload Image', 'wc-multichannel-ai-image-manager' );
            ?>
                    </button>
                </p>

                
                <?php 
            if ( function_exists( 'wmcaim_fs' ) && wmcaim_fs()->can_use_premium_code__premium_only() ) {
                ?>
<p>
                    <button type="button"
                            class="button button-primary wcmai-btn-ai"
                            data-product-id="<?php 
                echo esc_attr( $post->ID );
                ?>"
                            data-channel="<?php 
                echo esc_attr( $key );
                ?>">
                        <?php 
                esc_html_e( 'Generate Clean Image (AI)', 'wc-multichannel-ai-image-manager' );
                ?>
                    </button>
                    <span class="wcmai-ai-spinner" style="display:none;margin-left:6px;"><?php 
                esc_html_e( 'Processing…', 'wc-multichannel-ai-image-manager' );
                ?></span>
                </p>
                <?php 
            }
            ?>


                <p>
                    <button type="button"
                            class="button wcmai-btn-reset"
                            data-channel="<?php 
            echo esc_attr( $key );
            ?>">
                        <?php 
            esc_html_e( 'Remove (Not set)', 'wc-multichannel-ai-image-manager' );
            ?>
                    </button>
                </p>

                <p class="description" style="margin-top:4px;">
                    <?php 
            echo esc_html( $data['desc'] );
            ?>
                </p>
            </div>
            <?php 
        }
    }

    /**
     * Save product metadata for all channels.
     */
    public function save_product_meta( $post_id ) {
        if ( !isset( $_POST['wcmai_meta_nonce'] ) || !wp_verify_nonce( $_POST['wcmai_meta_nonce'], 'wcmai_save_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( !current_user_can( 'edit_product', $post_id ) ) {
            return;
        }
        $channels = $this->get_channels();
        foreach ( $channels as $key => $data ) {
            $meta_key = $data['meta'];
            if ( isset( $_POST[$meta_key] ) ) {
                $val = esc_url_raw( wp_unslash( $_POST[$meta_key] ) );
                // For Google Merchant free channel, mirror the behavior of the original free plugin:
                // if empty, fallback to featured image URL.
                if ( 'gm' === $key ) {
                    if ( empty( $val ) ) {
                        $thumb_id = get_post_thumbnail_id( $post_id );
                        if ( $thumb_id ) {
                            $derived = wp_get_attachment_url( $thumb_id );
                            if ( $derived ) {
                                $val = $derived;
                            }
                        }
                    }
                }
                if ( $val ) {
                    update_post_meta( $post_id, $meta_key, $val );
                } else {
                    delete_post_meta( $post_id, $meta_key );
                }
            }
        }
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function admin_assets( $hook ) {
        global $post_type;
        $allowed_hooks = [
            'post-new.php',
            'post.php',
            'product_page_wcmai-bulk-editor',
            'woocommerce_page_wc-settings',
            'tools_page_wcmai-export-csv'
        ];
        if ( !in_array( $hook, $allowed_hooks, true ) ) {
            return;
        }
        if ( ($hook === 'post-new.php' || $hook === 'post.php') && $post_type !== 'product' ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script(
            'wcmai-admin',
            plugins_url( 'assets/js/wcmai-admin.js', __FILE__ ),
            ['jquery'],
            '1.2.1',
            true
        );
        wp_localize_script( 'wcmai-admin', 'WCMai', [
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'wcmai_ai_nonce' ),
            'noThumb'    => __( 'This product has no featured image to process.', 'wc-multichannel-ai-image-manager' ),
            'processing' => __( 'Processing image, please wait…', 'wc-multichannel-ai-image-manager' ),
            'error'      => __( 'AI processing error. Please check logs or your OpenAI API key and try again.', 'wc-multichannel-ai-image-manager' ),
        ] );
    }

    /**
     * AJAX: AI background removal (single and bulk) for a specific channel.
     */
    public function ajax_remove_background() {
        check_ajax_referer( 'wcmai_ai_nonce', 'nonce' );
        if ( !current_user_can( 'edit_products' ) ) {
            wp_send_json_error( [
                'message' => 'Permission denied',
            ], 403 );
        }
        $product_id = ( isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0 );
        $channel = ( isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : 'gm' );
        if ( !$product_id ) {
            wp_send_json_error( [
                'message' => 'Invalid product ID',
            ], 400 );
        }
        $api_key = get_option( self::OPTION_API_KEY );
        if ( !$api_key ) {
            wp_send_json_error( [
                'message' => 'Missing OpenAI API key in settings.',
            ], 400 );
        }
        $thumb_id = get_post_thumbnail_id( $product_id );
        if ( !$thumb_id ) {
            wp_send_json_error( [
                'message' => 'Product has no featured image.',
                'code'    => 'no_thumb',
            ], 400 );
        }
        $file_path = get_attached_file( $thumb_id );
        if ( !$file_path || !file_exists( $file_path ) ) {
            wp_send_json_error( [
                'message' => 'Original image file not found.',
            ], 400 );
        }
        $image_data = $this->call_openai_background_removal( $api_key, $file_path );
        if ( is_wp_error( $image_data ) ) {
            error_log( 'WCMAI OpenAI error: ' . $image_data->get_error_message() );
            wp_send_json_error( [
                'message' => $image_data->get_error_message(),
            ], 500 );
        }
        if ( !$image_data ) {
            error_log( 'WCMAI OpenAI error: empty image data returned.' );
            wp_send_json_error( [
                'message' => 'Empty image data returned from OpenAI.',
            ], 500 );
        }
        $upload = wp_upload_bits( 'wcmai-ai-' . basename( $file_path ), null, $image_data );
        if ( !empty( $upload['error'] ) ) {
            wp_send_json_error( [
                'message' => 'Error saving generated image.',
            ], 500 );
        }
        $filetype = wp_check_filetype( $upload['file'], null );
        $attachment = [
            'post_mime_type' => $filetype['type'],
            'post_title'     => 'AI Clean Image – Product ' . $product_id . ' – ' . strtoupper( $channel ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];
        $attach_id = wp_insert_attachment( $attachment, $upload['file'], $product_id );
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        $url = wp_get_attachment_url( $attach_id );
        $meta_key = $this->get_meta_key_for_channel( $channel );
        update_post_meta( $product_id, $meta_key, esc_url_raw( $url ) );
        wp_send_json_success( [
            'url'  => $url,
            'html' => '<img src="' . esc_url( $url ) . '" style="max-width:100%;height:auto;border:1px solid #ddd;">',
        ] );
    }

    /**
     * AJAX: manually set image for a specific channel (bulk editor).
     */
    public function ajax_set_channel_image() {
        check_ajax_referer( 'wcmai_ai_nonce', 'nonce' );
        if ( !current_user_can( 'edit_products' ) ) {
            wp_send_json_error( [
                'message' => 'Permission denied',
            ], 403 );
        }
        $product_id = ( isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0 );
        $channel = ( isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : 'gm' );
        $image_url = ( isset( $_POST['image_url'] ) ? esc_url_raw( wp_unslash( $_POST['image_url'] ) ) : '' );
        if ( !$product_id || !$image_url ) {
            wp_send_json_error( [
                'message' => 'Missing product ID or image URL.',
            ], 400 );
        }
        $meta_key = $this->get_meta_key_for_channel( $channel );
        update_post_meta( $product_id, $meta_key, $image_url );
        wp_send_json_success( [
            'url' => $image_url,
        ] );
    }

    /**
     * AJAX: delete channel image meta (no file deletion).
     */
    public function ajax_delete_channel_image() {
        check_ajax_referer( 'wcmai_ai_nonce', 'nonce' );
        if ( !current_user_can( 'edit_products' ) ) {
            wp_send_json_error( [
                'message' => 'Permission denied',
            ], 403 );
        }
        $product_id = ( isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0 );
        $channel = ( isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : 'gm' );
        if ( !$product_id ) {
            wp_send_json_error( [
                'message' => 'Invalid product ID',
            ], 400 );
        }
        $meta_key = $this->get_meta_key_for_channel( $channel );
        delete_post_meta( $product_id, $meta_key );
        wp_send_json_success();
    }

    /**
     * Call OpenAI Images Edit API (gpt-image-1) to remove background.
     */
    protected function call_openai_background_removal( $api_key, $file_path ) {
        if ( !function_exists( 'curl_init' ) ) {
            return new WP_Error('wcmai_no_curl', 'PHP cURL extension is not enabled on this server.');
        }
        if ( !file_exists( $file_path ) ) {
            return new WP_Error('wcmai_file_missing', 'Original image file not found on disk.');
        }
        $fileinfo = wp_check_filetype( $file_path );
        $mime = ( isset( $fileinfo['type'] ) ? $fileinfo['type'] : '' );
        if ( !in_array( $mime, ['image/jpeg', 'image/png', 'image/webp'], true ) ) {
            $ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
            if ( in_array( $ext, ['jpg', 'jpeg'], true ) ) {
                $mime = 'image/jpeg';
            } elseif ( $ext === 'png' ) {
                $mime = 'image/png';
            } elseif ( $ext === 'webp' ) {
                $mime = 'image/webp';
            } else {
                return new WP_Error('wcmai_bad_mime', 'Unsupported image type for OpenAI. Allowed: image/jpeg, image/png, image/webp.');
            }
        }
        $image_file = new CURLFile($file_path, $mime, basename( $file_path ));
        $endpoint = 'https://api.openai.com/v1/images/edits';
        $post_fields = [
            'model'         => 'gpt-image-1',
            'prompt'        => 'Remove the background from this product image and return a PNG with a transparent background optimized for e-commerce product feeds.',
            'image'         => $image_file,
            'background'    => 'transparent',
            'output_format' => 'png',
            'size'          => '1024x1024',
            'quality'       => 'medium',
        ];
        $ch = curl_init( $endpoint );
        curl_setopt_array( $ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $api_key],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post_fields,
            CURLOPT_TIMEOUT        => 120,
        ] );
        $response = curl_exec( $ch );
        $err = curl_error( $ch );
        $httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        if ( $err ) {
            return new WP_Error('wcmai_curl_error', 'cURL error: ' . $err);
        }
        if ( !$response ) {
            return new WP_Error('wcmai_empty_response', 'Empty response from OpenAI API.');
        }
        $json = json_decode( $response, true );
        if ( isset( $json['error']['message'] ) ) {
            return new WP_Error('wcmai_openai_error', 'OpenAI API error (' . $httpcode . '): ' . $json['error']['message']);
        }
        if ( !isset( $json['data'][0]['b64_json'] ) ) {
            return new WP_Error('wcmai_unexpected_response', 'Unexpected OpenAI response: ' . substr( $response, 0, 400 ));
        }
        $binary = base64_decode( $json['data'][0]['b64_json'] );
        if ( !$binary ) {
            return new WP_Error('wcmai_decode_error', 'Unable to decode base64 image data from OpenAI.');
        }
        return $binary;
    }

    /**
     * WooCommerce settings tab.
     */
    public function add_settings_tab( $tabs ) {
        $tabs['wcmai_images'] = __( 'AI Images', 'wc-multichannel-ai-image-manager' );
        return $tabs;
    }

    public function render_settings_tab() {
        $api_key = get_option( self::OPTION_API_KEY, '' );
        $defaults = get_option( self::OPTION_DEFAULTS, [] );
        $channels = $this->get_channels();
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wcmai_openai_api_key"><?php 
        esc_html_e( 'OpenAI API Key', 'wc-multichannel-ai-image-manager' );
        ?></label>
                </th>
                <td>
                    <input type="password" name="wcmai_openai_api_key" id="wcmai_openai_api_key"
                           value="<?php 
        echo esc_attr( $api_key );
        ?>" class="regular-text">
                    <p class="description">
                        <?php 
        esc_html_e( 'Your OpenAI secret key. Required for AI background removal.', 'wc-multichannel-ai-image-manager' );
        ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php 
        esc_html_e( 'AI Mode', 'wc-multichannel-ai-image-manager' );
        ?>
                </th>
                <td>
                    <strong><?php 
        esc_html_e( 'AI Clean Feed Image (Optimized ~0.05$ per image)', 'wc-multichannel-ai-image-manager' );
        ?></strong>
                    <p class="description">
                        <?php 
        esc_html_e( 'Fixed 1024x1024 PNG with transparent background, tuned for product feeds and stable pricing using gpt-image-1.', 'wc-multichannel-ai-image-manager' );
        ?>
                    </p>
                </td>
            </tr>

            <?php 
        foreach ( $channels as $key => $data ) {
            $image_id = ( isset( $defaults[$key] ) ? absint( $defaults[$key] ) : 0 );
            $image_url = ( $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '' );
            ?>
                <tr>
                    <th scope="row"><?php 
            echo esc_html( $data['label'] );
            ?></th>
                    <td>
                        <div class="wcmai-default-image-wrap">
                            <input type="hidden" name="wcmai_default_channel_images[<?php 
            echo esc_attr( $key );
            ?>]"
                                   class="wcmai-default-image-id"
                                   value="<?php 
            echo $image_id;
            ?>">
                            <div class="wcmai-default-preview" style="margin-bottom:8px;">
                                <?php 
            if ( $image_url ) {
                ?>
                                    <img src="<?php 
                echo esc_url( $image_url );
                ?>" style="max-width:150px;height:auto;border:1px solid #ddd;">
                                <?php 
            } else {
                ?>
                                    <em style="color:#777;"><?php 
                esc_html_e( 'No image selected.', 'wc-multichannel-ai-image-manager' );
                ?></em>
                                <?php 
            }
            ?>
                            </div>
                            <button type="button" class="button wcmai-select-default-image">
                                <?php 
            esc_html_e( 'Select / Upload', 'wc-multichannel-ai-image-manager' );
            ?>
                            </button>
                            <button type="button" class="button wcmai-clear-default-image">
                                <?php 
            esc_html_e( 'Remove', 'wc-multichannel-ai-image-manager' );
            ?>
                            </button>
                            <p class="description" style="margin-top:4px;">
                                <?php 
            esc_html_e( 'Used as a fallback image per channel when your feed/export tool is configured to read these meta keys and no product-level image is set.', 'wc-multichannel-ai-image-manager' );
            ?>
                            </p>
                        </div>
                    </td>
                </tr>
            <?php 
        }
        ?>
        </table>
        <?php 
    }

    public function save_settings_tab() {
        if ( isset( $_POST['wcmai_openai_api_key'] ) ) {
            update_option( self::OPTION_API_KEY, trim( sanitize_text_field( wp_unslash( $_POST['wcmai_openai_api_key'] ) ) ) );
        }
        if ( isset( $_POST['wcmai_default_channel_images'] ) && is_array( $_POST['wcmai_default_channel_images'] ) ) {
            $clean = [];
            foreach ( $_POST['wcmai_default_channel_images'] as $key => $val ) {
                $id = absint( $val );
                if ( $id > 0 ) {
                    $clean[$key] = $id;
                }
            }
            update_option( self::OPTION_DEFAULTS, $clean );
        } else {
            delete_option( self::OPTION_DEFAULTS );
        }
    }

    /**
     * Admin menu pages: bulk editor + CSV export.
     */
    public function register_admin_pages() {
        add_submenu_page(
            'edit.php?post_type=product',
            __( 'Feed Image Bulk Editor', 'wc-multichannel-ai-image-manager' ),
            __( 'Feed Image Bulk Editor', 'wc-multichannel-ai-image-manager' ),
            'manage_woocommerce',
            'wcmai-bulk-editor',
            [$this, 'render_bulk_editor_page']
        );
        add_management_page(
            __( 'Export Multi-Channel Image CSV', 'wc-multichannel-ai-image-manager' ),
            __( 'Export Multi-Channel Image CSV', 'wc-multichannel-ai-image-manager' ),
            'manage_woocommerce',
            'wcmai-export-csv',
            [$this, 'render_csv_export_page']
        );
    }

    public function render_bulk_editor_page() {
        if ( !current_user_can( 'manage_woocommerce' ) ) {
            return;
        }
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
        ];
        $products = get_posts( $args );
        $channels = $this->get_channels();
        ?>
        <div class="wrap">
            <h1><?php 
        esc_html_e( 'Feed Image Bulk Editor', 'wc-multichannel-ai-image-manager' );
        ?></h1>
            <p class="description">
                <?php 
        esc_html_e( 'Quickly manage multi-channel feed images. For each channel you can upload a custom image, generate an AI Clean Image from the featured image, or reset it to "Not set" (your exporter can then fall back to the featured image or global defaults).', 'wc-multichannel-ai-image-manager' );
        ?>
            </p>

            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php 
        esc_html_e( 'Product', 'wc-multichannel-ai-image-manager' );
        ?></th>
                        <?php 
        foreach ( $channels as $key => $data ) {
            ?>
                            <th><?php 
            echo esc_html( $data['label'] );
            ?></th>
                        <?php 
        }
        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
        if ( $products ) {
            ?>
                        <?php 
            foreach ( $products as $product ) {
                $thumb_id = get_post_thumbnail_id( $product->ID );
                $thumb_url = ( $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '' );
                ?>
                            <tr>
                                <td>
                                    <a href="<?php 
                echo esc_url( get_edit_post_link( $product->ID ) );
                ?>">
                                        <?php 
                echo esc_html( $product->post_title );
                ?>
                                    </a>
                                    <?php 
                if ( $thumb_url ) {
                    ?>
                                        <br>
                                        <img src="<?php 
                    echo esc_url( $thumb_url );
                    ?>" style="max-width:80px;height:auto;margin-top:4px;border:1px solid #ddd;">
                                    <?php 
                } else {
                    ?>
                                        <br><em><?php 
                    esc_html_e( 'No featured image', 'wc-multichannel-ai-image-manager' );
                    ?></em>
                                    <?php 
                }
                ?>
                                </td>
                                <?php 
                foreach ( $channels as $key => $data ) {
                    $meta_key = $data['meta'];
                    $url = get_post_meta( $product->ID, $meta_key, true );
                    $url = ( $url ? esc_url( $url ) : '' );
                    ?>
                                    <td class="wcmai-cell" data-channel="<?php 
                    echo esc_attr( $key );
                    ?>">
                                        <div class="wcmai-cell-preview">
                                            <?php 
                    if ( $url ) {
                        ?>
                                                <img src="<?php 
                        echo esc_url( $url );
                        ?>" style="max-width:80px;height:auto;border:1px solid #ddd;">
                                                <br><code><?php 
                        echo esc_html( $url );
                        ?></code>
                                            <?php 
                    } else {
                        ?>
                                                <em title="<?php 
                        esc_attr_e( 'Not set (your exporter can fall back to the featured image or global default).', 'wc-multichannel-ai-image-manager' );
                        ?>"><?php 
                        esc_html_e( 'Not set', 'wc-multichannel-ai-image-manager' );
                        ?></em>
                                            <?php 
                    }
                    ?>
                                        </div>

                                        <div class="wcmai-cell-actions" style="margin-top:6px;">
                                            <button type="button"
                                                    class="button button-primary wcmai-bulk-ai"
                                                    data-product-id="<?php 
                    echo esc_attr( $product->ID );
                    ?>"
                                                    data-channel="<?php 
                    echo esc_attr( $key );
                    ?>">
                                                <?php 
                    esc_html_e( 'AI Remove BG', 'wc-multichannel-ai-image-manager' );
                    ?>
                                            </button>
                                            <br>
                                            <button type="button"
                                                    class="button wcmai-bulk-upload"
                                                    data-product-id="<?php 
                    echo esc_attr( $product->ID );
                    ?>"
                                                    data-channel="<?php 
                    echo esc_attr( $key );
                    ?>">
                                                <?php 
                    esc_html_e( 'Upload Image', 'wc-multichannel-ai-image-manager' );
                    ?>
                                            </button>
                                            <br>
                                            <button type="button"
                                                    class="button wcmai-bulk-reset"
                                                    data-product-id="<?php 
                    echo esc_attr( $product->ID );
                    ?>"
                                                    data-channel="<?php 
                    echo esc_attr( $key );
                    ?>">
                                                <?php 
                    esc_html_e( 'Reset', 'wc-multichannel-ai-image-manager' );
                    ?>
                                            </button>
                                            <span class="wcmai-bulk-spinner" style="display:none;margin-left:6px;"><?php 
                    esc_html_e( 'Processing…', 'wc-multichannel-ai-image-manager' );
                    ?></span>
                                        </div>
                                    </td>
                                <?php 
                }
                ?>
                            </tr>
                        <?php 
            }
            ?>
                    <?php 
        } else {
            ?>
                        <tr>
                            <td colspan="<?php 
            echo 1 + count( $channels );
            ?>"><?php 
            esc_html_e( 'No products found.', 'wc-multichannel-ai-image-manager' );
            ?></td>
                        </tr>
                    <?php 
        }
        ?>
                </tbody>
            </table>
        </div>
        <?php 
    }

    public function render_csv_export_page() {
        if ( !current_user_can( 'manage_woocommerce' ) ) {
            return;
        }
        $url = wp_nonce_url( add_query_arg( [
            'wcmai_export_csv' => 1,
        ], admin_url( 'tools.php?page=wcmai-export-csv' ) ), 'wcmai_export_csv', 'wcmai_nonce' );
        ?>
        <div class="wrap">
            <h1><?php 
        esc_html_e( 'Export Multi-Channel Image CSV', 'wc-multichannel-ai-image-manager' );
        ?></h1>
            <p class="description">
                <?php 
        esc_html_e( 'Generate a CSV file with product IDs, titles, and all multi-channel image meta fields.', 'wc-multichannel-ai-image-manager' );
        ?>
            </p>

            <p>
                <a href="<?php 
        echo esc_url( $url );
        ?>" class="button button-primary">
                    <?php 
        esc_html_e( 'Download CSV', 'wc-multichannel-ai-image-manager' );
        ?>
                </a>
            </p>
        </div>
        <?php 
    }

    public function handle_csv_export() {
        if ( !isset( $_GET['wcmai_export_csv'], $_GET['wcmai_nonce'] ) ) {
            return;
        }
        if ( !wp_verify_nonce( $_GET['wcmai_nonce'], 'wcmai_export_csv' ) ) {
            return;
        }
        if ( !current_user_can( 'manage_woocommerce' ) ) {
            return;
        }
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ];
        $products = get_posts( $args );
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=wcmai-multichannel-images-' . date( 'Ymd-His' ) . '.csv' );
        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, [
            'product_id',
            'title',
            '_gm_image_url',
            '_fb_image_url',
            '_pt_image_url',
            '_tk_image_url'
        ] );
        foreach ( $products as $id ) {
            $gm = get_post_meta( $id, self::META_GM_IMAGE, true );
            $fb = get_post_meta( $id, self::META_FB_IMAGE, true );
            $pt = get_post_meta( $id, self::META_PT_IMAGE, true );
            $tk = get_post_meta( $id, self::META_TK_IMAGE, true );
            $title = get_the_title( $id );
            fputcsv( $output, [
                $id,
                $title,
                $gm,
                $fb,
                $pt,
                $tk
            ] );
        }
        fclose( $output );
        exit;
    }

}

new WC_Multichannel_AI_Image_Manager();