<?php
/*
Plugin Name: Focos TV Advertising Administration
Description: Administra y carga los artes para visualizarlos en el sitio web, configurable desde el panel de administración.
Version: 1.1
Author: Manuel Espinoza
*/

// Agregar menú en el panel de administración
function focostv_advertising_add_admin_menu()
{
    add_menu_page(
        'FOCOS Advertising Administration',
        'FOCOS Advertising Admin',
        'manage_options',
        'focostv-advertising-admin',
        'focostv_advertising_admin_page',
        'dashicons-schedule'
    );
}
add_action('admin_menu', 'focostv_advertising_add_admin_menu');

// Registrar configuraciones
function focostv_advertising_register_settings()
{
    register_setting('focostv_advertising_options', 'focostv_advertising_images', 'focostv_advertising_sanitize_images');
}
add_action('admin_init', 'focostv_advertising_register_settings');

// Sanitizar las imágenes
function focostv_advertising_sanitize_images($input)
{
    $valid_keys = array('mobile_page', 'mobile_posts', 'desktop_page', 'desktop_posts');
    $new_input = array();
    foreach ($valid_keys as $key) {
        if (isset($input[$key])) {
            $new_input[$key] = absint($input[$key]);
        }
    }
    return $new_input;
}

// Página de administración
function focostv_advertising_admin_page()
{
    wp_enqueue_media();
    $images = get_option('focostv_advertising_images', array());
    ?>
    <div class="wrap">
        <h1>FOCOS Advertising Administration</h1>
        <form method="post" action="options.php">
            <?php settings_fields('focostv_advertising_options'); ?>
            <?php do_settings_sections('focostv_advertising_options'); ?>
            <table class="form-table">
                <?php
                $image_sections = array(
                    'mobile_page' => 'Ad Mobile Page (320x320)',
                    'mobile_posts' => 'Ad Mobile Posts (320x320)',
                    'desktop_page' => 'Ad Desktop Page (300x600)',
                    'desktop_posts' => 'Ad Desktop Posts (800x250)'
                );
                foreach ($image_sections as $key => $label) :
                ?>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html($label); ?></th>
                    <td>
                        <div class="image-preview-wrapper">
                            <img id="preview_<?php echo esc_attr($key); ?>" src="<?php echo isset($images[$key]) ? esc_url(wp_get_attachment_image_url($images[$key], 'medium')) : ''; ?>" style="max-width:300px;<?php echo !isset($images[$key]) ? 'display:none;' : ''; ?>">
                        </div>
                        <input type="hidden" name="focostv_advertising_images[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>" value="<?php echo isset($images[$key]) ? esc_attr($images[$key]) : ''; ?>">
                        <button type="button" class="button" id="upload_<?php echo esc_attr($key); ?>_button">Seleccionar imagen</button>
                        <button type="button" class="button" id="remove_<?php echo esc_attr($key); ?>_button" style="<?php echo !isset($images[$key]) ? 'display:none;' : ''; ?>">Eliminar imagen</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        <?php foreach ($image_sections as $key => $label) : ?>
        $('#upload_<?php echo $key; ?>_button').click(function(e) {
            e.preventDefault();
            var image_frame;
            if (image_frame) {
                image_frame.open();
            } else {
                image_frame = wp.media({
                    title: 'Seleccionar imagen para <?php echo $label; ?>',
                    multiple: false
                });

                image_frame.on('select', function() {
                    var attachment = image_frame.state().get('selection').first().toJSON();
                    var image_url = attachment.sizes && attachment.sizes.medium 
                        ? attachment.sizes.medium.url 
                        : attachment.url;
                    $('#<?php echo $key; ?>').val(attachment.id);
                    $('#preview_<?php echo $key; ?>').attr('src', image_url).show();
                    $('#remove_<?php echo $key; ?>_button').show();
                });

                image_frame.open();
            }
        });

        $('#remove_<?php echo $key; ?>_button').click(function(e) {
            e.preventDefault();
            $('#<?php echo $key; ?>').val('');
            $('#preview_<?php echo $key; ?>').attr('src', '').hide();
            $(this).hide();
        });
        <?php endforeach; ?>
    });
    </script>
    <?php
}

// Shortcode para mostrar las imágenes
function focostv_advertising_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'type' => 'all', // Puede ser 'all', 'mobile', 'desktop', 'page', o 'posts'
        ),
        $atts,
        'focostv_advertising'
    );

    $images = get_option('focostv_advertising_images', array());
    if (empty($images)) {
        return '';
    }

    $output = '<div class="focostv-advertising-images">';

    foreach ($images as $key => $image_id) {
        if (
            $atts['type'] == 'all' ||
            ($atts['type'] == 'mobile' && strpos($key, 'mobile') !== false) ||
            ($atts['type'] == 'desktop' && strpos($key, 'desktop') !== false) ||
            ($atts['type'] == 'page' && strpos($key, 'page') !== false) ||
            ($atts['type'] == 'posts' && strpos($key, 'posts') !== false)
        ) {

            $image_url = wp_get_attachment_image_url($image_id, 'full');
            if ($image_url) {
                $output .= '<div class="focostv-ad-' . esc_attr($key) . '">';
                $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($key) . ' advertisement">';
                $output .= '</div>';
            }
        }
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('focostv_advertising', 'focostv_advertising_shortcode');