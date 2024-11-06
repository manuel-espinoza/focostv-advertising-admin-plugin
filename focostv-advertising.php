<?php
/*
Plugin Name: Focos TV Advertising Administration
Description: Administra y carga los artes para visualizarlos en el sitio web, configurable desde el panel de administración.
Version: 1.3
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
    $image_sections = array(
        'mobile_page' => array(320, 320),
        'mobile_posts' => array(320, 320),
        'desktop_page' => array(300, 600),
        'desktop_posts' => array(800, 250)
    );

    foreach ($valid_keys as $key) {
        if (isset($input[$key])) {
            $image_id = absint($input[$key]);
            $image_data = wp_get_attachment_image_src($image_id, 'full');
            if ($image_data) {
                $width = $image_data[1];
                $height = $image_data[2];
                if ($width === $image_sections[$key][0] && $height === $image_sections[$key][1]) {
                    $new_input[$key] = $image_id;
                }
            }
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
        <h5>En esta seccion puede subir las imagenes que se mostrar&aacute;n como publicidad dentro del sitio, en las
            secciones espec&iacute;ficas para m&oacute;viles y desktops y segun la resoluci&oacute;n permitida</h5>
        <form method="post" action="options.php">
            <?php settings_fields('focostv_advertising_options'); ?>
            <?php do_settings_sections('focostv_advertising_options'); ?>
            <table class="form-table">
                <?php
                $image_sections = array(
                    'mobile_page' => array('Ad Mobile Page', 320, 320),
                    'mobile_posts' => array('Ad Mobile Posts', 320, 320),
                    'desktop_page' => array('Ad Desktop Page', 300, 600),
                    'desktop_posts' => array('Ad Desktop Posts', 800, 250)
                );
                foreach ($image_sections as $key => $info):
                    $label = $info[0];
                    $width = $info[1];
                    $height = $info[2];
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html($label); ?> (<?php echo $width; ?>x<?php echo $height; ?>)</th>
                        <td>
                            <div class="image-preview-wrapper">
                                <img id="preview_<?php echo esc_attr($key); ?>"
                                    src="<?php echo isset($images[$key]) ? esc_url(wp_get_attachment_image_url($images[$key], 'full')) : ''; ?>"
                                    style="max-width:300px;<?php echo !isset($images[$key]) ? 'display:none;' : ''; ?>">
                            </div>
                            <input type="hidden" name="focostv_advertising_images[<?php echo esc_attr($key); ?>]"
                                id="<?php echo esc_attr($key); ?>"
                                value="<?php echo isset($images[$key]) ? esc_attr($images[$key]) : ''; ?>">
                            <button type="button" class="button" id="upload_<?php echo esc_attr($key); ?>_button">Seleccionar
                                imagen</button>
                            <button type="button" class="button" id="remove_<?php echo esc_attr($key); ?>_button"
                                style="<?php echo !isset($images[$key]) ? 'display:none;' : ''; ?>">Eliminar imagen</button>
                            <p class="description" id="<?php echo esc_attr($key); ?>_dimensions"></p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            <?php foreach ($image_sections as $key => $info):
                $width = $info[1];
                $height = $info[2];
                ?>
                $('#upload_<?php echo $key; ?>_button').click(function (e) {
                    e.preventDefault();
                    var image_frame;
                    if (image_frame) {
                        image_frame.open();
                    } else {
                        image_frame = wp.media({
                            title: 'Seleccionar imagen para <?php echo $info[0]; ?>',
                            multiple: false
                        });

                        image_frame.on('select', function () {
                            var attachment = image_frame.state().get('selection').first().toJSON();
                            var image_url = attachment.url;

                            // Validar dimensiones
                            var img = new Image();
                            img.onload = function () {
                                if (this.width === <?php echo $width; ?> && this.height === <?php echo $height; ?>) {
                                    $('#<?php echo $key; ?>').val(attachment.id);
                                    $('#preview_<?php echo $key; ?>').attr('src', image_url).show();
                                    $('#remove_<?php echo $key; ?>_button').show();
                                    $('#<?php echo $key; ?>_dimensions').text('Dimensiones correctas: ' + this.width + 'x' + this.height).css('color', 'green');
                                } else {
                                    $('#<?php echo $key; ?>').val('');
                                    $('#preview_<?php echo $key; ?>').attr('src', '').hide();
                                    $('#remove_<?php echo $key; ?>_button').hide();
                                    $('#<?php echo $key; ?>_dimensions').text('Error: Las dimensiones de la imagen (' + this.width + 'x' + this.height + ') no coinciden con las requeridas (<?php echo $width; ?>x<?php echo $height; ?>). La imagen no se guardará.').css('color', 'red');
                                }
                            };
                            img.src = image_url;
                        });

                        image_frame.open();
                    }
                });

                $('#remove_<?php echo $key; ?>_button').click(function (e) {
                    e.preventDefault();
                    $('#<?php echo $key; ?>').val('');
                    $('#preview_<?php echo $key; ?>').attr('src', '').hide();
                    $(this).hide();
                    $('#<?php echo $key; ?>_dimensions').text('');
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
            'group' => '', // Puede ser 'mobile_page', 'mobile_posts', 'desktop_page', o 'desktop_posts'
        ),
        $atts,
        'focostv_advertising'
    );

    $images = get_option('focostv_advertising_images', array());

    // Validar si el grupo solicitado existe y tiene una imagen asignada
    if (empty($images) || !isset($images[$atts['group']])) {
        return '';
    }

    // Obtener URL de la imagen del grupo especificado
    $image_url = wp_get_attachment_image_url($images[$atts['group']], 'full');
    if (!$image_url) {
        return '';
    }

    // Generar la salida HTML
    $output = '<div class="focostv-advertising-image focostv-ad-' . esc_attr($atts['group']) . '">';
    $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($atts['group']) . ' advertisement">';
    $output .= '</div>';

    return $output;
}
add_shortcode('focostv_advertising', 'focostv_advertising_shortcode');