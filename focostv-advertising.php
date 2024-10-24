<?php
/*
Plugin Name: Focos TV Advertising Administration
Description: Administra y carga los artes para visualizarlos en el sitio web, configurable desde el panel de administración.
Version: 1.0
Author: Manuel Espinoza
*/

function focostv_advertising_add_admin_menu()
{
    add_menu_page(
        'FOCOS Advertising Administration', // Titulo de la pagina
        'FOCOS Advertising Admin', // Titulo del menu
        'manage_options', // Capacidad
        'focostv-advertising-admin', // Slug del menu
        'focostv_advertising_admin_page', // Funcion para mostrar el contenido
        'dashicons-schedule'
    );
}
add_action('admin_menu', 'focostv_advertising_add_admin_menu');

function focostv_advertising_admin_page()
{
    ?>
    <div class="wrap">
        <h1 class="focostv-ad-title">Administraci&oacute;n de publicidad en el sitio</h1>
        <form method="post" action="options.php" enctype="multipart/form-data">
            <?php
            settings_fields('focostv_advertising_options_group');
            do_settings_sections('focostv-advertising-admin');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function focostv_advertising_settings_init()
{
    // mobile
    register_setting('focostv_advertising_options_group', 'focostv_mobile_pages_ad_image');
    register_setting('focostv_advertising_options_group', 'focostv_mobile_pages_footer_ad_image');
    register_setting('focostv_advertising_options_group', 'focostv_mobile_posts_ad_image');
    // desktop
    register_setting('focostv_advertising_options_group', 'focostv_desktop_pages_ad_image');
    register_setting('focostv_advertising_options_group', 'focostv_desktop_posts_ad_image');


    add_settings_section(
        'focostv_advertising_section',
        'Subir Publicidad',
        null,
        'focostv-advertising-admin'
    );


    add_settings_field(
        'focostv_mobile_pages_ad_image_field',
        'Publicidad para Mobile - Páginas (320x320)',
        'focostv_mobile_pages_ad_image_callback',
        'focostv-advertising-admin',
        'focostv_advertising_section'
    );

    add_settings_field(
        'focostv_mobile_pages_footer_ad_image_field',
        'Publicidad para Mobile - Footer (300x100)',
        'focostv_mobile_pages_footer_ad_image_callback',
        'focostv-advertising-admin',
        'focostv_advertising_section'
    );

    add_settings_field(
        'focostv_mobile_posts_ad_image_field',
        'Publicidad para Mobile - Posts (320x320)',
        'focostv_mobile_posts_ad_image_callback',
        'focostv-advertising-admin',
        'focostv_advertising_section'
    );

    // DESKTOP
    add_settings_field(
        'focostv_desktop_pages_ad_image_field',
        'Publicidad para pages Desktop (300x600)',
        'focostv_desktop_pages_ad_image_callback',
        'focostv-advertising-admin',
        'focostv_advertising_section'
    );

    add_settings_field(
        'focostv_desktop_posts_ad_image_field',
        'Publicidad para posts Desktop (800x250)',
        'focostv_desktop_posts_ad_image_callback',
        'focostv-advertising-admin',
        'focostv_advertising_section'
    );
}
add_action('admin_init', 'focostv_advertising_settings_init');

function focostv_mobile_pages_ad_image_callback()
{
    $mobile_pages_ad = get_option('focostv_mobile_pages_ad_image');
    echo '<input type="file" name="focostv_mobile_pages_ad_image" accept=".webm, .png, .jpg, .jpeg, .gif" />';
    if ($mobile_pages_ad) {
        echo '<p><img src="' . esc_url($mobile_pages_ad) . '" style="max-width: 320px;"></p>';
    }
}

// Callback para subir la imagen de publicidad mobile - Posts
function focostv_mobile_posts_ad_image_callback()
{
    $mobile_posts_ad = get_option('focostv_mobile_posts_ad_image');
    echo '<input type="file" name="focostv_mobile_posts_ad_image" accept=".webm, .png, .jpg, .jpeg, .gif" />';
    if ($mobile_posts_ad) {
        echo '<p><img src="' . esc_url($mobile_posts_ad) . '" style="max-width: 320px;"></p>';
    }
}

function focostv_mobile_pages_footer_ad_image_callback()
{
    $mobile_pages_footer_ad = get_option('focostv_mobile_pages_footer_ad_image');
    echo '<input type="file" name="focostv_mobile_pages_footer_ad_image" accept=".webm, .png, .jpg, .jpeg, .gif" />';
    if ($mobile_pages_footer_ad) {
        echo '<p><img src="' . esc_url($mobile_pages_footer_ad) . '" style="max-width: 300px;"></p>';
    }
}

function focostv_desktop_pages_ad_image_callback()
{
    $desktop_ad = get_option('focostv_desktop_pages_ad_image');
    echo '<input type="file" name="focostv_desktop_pages_ad_image" accept=".webm, .png, .jpg, .jpeg, .gif" />';
    if ($desktop_ad) {
        echo '<p><img src="' . esc_url($desktop_ad) . '" style="max-width: 300px;"></p>';
    }
}

function focostv_desktop_posts_ad_image_callback()
{
    $desktop_ad = get_option('focostv_desktop_posts_ad_image');
    echo '<input type="file" name="focostv_desktop_posts_ad_image" accept=".webm, .png, .jpg, .jpeg, .gif" />';
    if ($desktop_ad) {
        echo '<p><img src="' . esc_url($desktop_ad) . '" style="max-width: 300px;"></p>';
    }
}

function focostv_save_ad_images()
{
    if (isset($_FILES['focostv_mobile_pages_ad_image']) && !empty($_FILES['focostv_mobile_pages_ad_image']['tmp_name'])) {
        $uploaded_mobile_pages = media_handle_upload('focostv_mobile_pages_ad_image', 0);

        if (!is_wp_error($uploaded_mobile_pages)) {
            update_option('focostv_mobile_pages_ad_image', wp_get_attachment_url($uploaded_mobile_pages));
        }
    }

    if (isset($_FILES['focostv_mobile_posts_ad_image']) && !empty($_FILES['focostv_mobile_posts_ad_image']['tmp_name'])) {
        $uploaded_mobile_posts = media_handle_upload('focostv_mobile_posts_ad_image', 0);

        if (!is_wp_error($uploaded_mobile_posts)) {
            update_option('focostv_mobile_posts_ad_image', wp_get_attachment_url($uploaded_mobile_posts));
        }
    }
    if (isset($_FILES['focostv_mobile_pages_footer_ad_image']) && !empty($_FILES['focostv_mobile_pages_footer_ad_image']['tmp_name'])) {
        $uploaded_mobile_pages_footer = media_handle_upload('focostv_mobile_pages_footer_ad_image', 0);

        if (!is_wp_error($uploaded_mobile_posts)) {
            update_option('focostv_mobile_pages_footer_ad_image', wp_get_attachment_url($uploaded_mobile_pages_footer));
        }
    }
    if (isset($_FILES['focostv_desktop_pages_ad_image']) && !empty($_FILES['focostv_desktop_pages_ad_image']['tmp_name'])) {
        $uploaded_desktop_pages = media_handle_upload('focostv_desktop_pages_ad_image', 0);

        if (!is_wp_error($uploaded_desktop_pages)) {
            update_option('focostv_desktop_pages_ad_image', wp_get_attachment_url($uploaded_desktop_pages));
        }
    }

    if (isset($_FILES['focostv_desktop_posts_ad_image']) && !empty($_FILES['focostv_desktop_posts_ad_image']['tmp_name'])) {
        $uploaded_desktop_posts = media_handle_upload('focostv_desktop_posts_ad_image', 0);

        if (!is_wp_error($uploaded_desktop_posts)) {
            update_option('focostv_desktop_posts_ad_image', wp_get_attachment_url($uploaded_desktop_posts));
        }
    }
}
add_action('admin_post_save_ad_images', 'focostv_save_ad_images');

function focostv_advertising_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'type' => 'mobile',  // mobile o desktop
            'location' => 'posts',   // posts, pages, footer
        ),
        $atts,
        'focostv_ad' // Nombre del shortcode
    );

    $option_name = 'focostv_' . $atts['type'] . '_' . $atts['location'] . '_ad_image';
    $ad_image = get_option($option_name);


    if ($ad_image) {
        return '<div class="focostv-ad focostv-' . esc_attr($atts['type']) . '-' . esc_attr($atts['location']) . '">
                    <img src="' . esc_url($ad_image) . '" alt="Publicidad para FOCOSTV" />
                </div>';
    }

    return '<div class="focostv-no-ad focostv-' . esc_attr($atts['type']) . '-' . esc_attr($atts['location']) . '">
                <div class="focostv-no-ad-logo-container">
                    <img class="focostv-no-ad-logo" src="' . plugins_url("focostv-logo-white.svg", __FILE__) . '" alt="FOCOSTV AD LOGO" />
                </div>
                <div class="focostv-no-ad-content">
                    <h6 class="focostv-no-ad-title">¡ANUNCIATE CON NOSOTROS!</h6>
                </div>
                <div class="focostv-no-ad-contact">
                    <a href="#" class="focostv-no-ad-button">Contactar</a>
                </div>
            </div>';
}
add_shortcode('focostv_ad', 'focostv_advertising_shortcode');

function focostv_advertising_enqueue_styles()
{
    wp_enqueue_style('focostv-advertising-style', plugins_url('style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'focostv_advertising_enqueue_styles');
