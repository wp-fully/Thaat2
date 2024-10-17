// Register Custom Post Type for Directories
function thaat_register_directories_cpt() {

    $labels = array(
        'name'                  => _x( 'Directories', 'Post Type General Name', 'thaat' ),
        'singular_name'         => _x( 'Directory', 'Post Type Singular Name', 'thaat' ),
        'menu_name'             => __( 'Directories', 'thaat' ),
        'all_items'             => __( 'All Directories', 'thaat' ),
        'add_new_item'          => __( 'Add New Directory', 'thaat' ),
        'edit_item'             => __( 'Edit Directory', 'thaat' ),
    );
    $args = array(
        'label'                 => __( 'Directory', 'thaat' ),
        'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields' ),
        'public'                => true,
        'show_ui'               => true,
        'has_archive'           => true,
        'rewrite'               => array( 'slug' => 'directories' ),
        'menu_icon'             => 'dashicons-awards',
    );
    register_post_type( 'directories', $args );

}
add_action( 'init', 'thaat_register_directories_cpt' );

// Register Custom Taxonomies for City and Speciality
function thaat_register_directories_taxonomies() {

    // City Taxonomy
    $city_labels = array(
        'name'              => _x( 'Cities', 'taxonomy general name', 'thaat' ),
        'singular_name'     => _x( 'City', 'taxonomy singular name', 'thaat' ),
    );
    $city_args = array(
        'labels'            => $city_labels,
        'hierarchical'      => true,
        'public'            => true,
        'rewrite'           => array( 'slug' => 'city' ),
    );
    register_taxonomy( 'city', array( 'directories' ), $city_args );

    // Speciality Taxonomy
    $speciality_labels = array(
        'name'              => _x( 'Specialities', 'taxonomy general name', 'thaat' ),
        'singular_name'     => _x( 'Speciality', 'taxonomy singular name', 'thaat' ),
    );
    $speciality_args = array(
        'labels'            => $speciality_labels,
        'hierarchical'      => true,
        'public'            => true,
        'rewrite'           => array( 'slug' => 'speciality' ),
    );
    register_taxonomy( 'speciality', array( 'directories' ), $speciality_args );

}
add_action( 'init', 'thaat_register_directories_taxonomies' );
<!-- #################################################################   -->
<!--#################################################################  -->
<!--#################################################################  -->
// Shortcode to display City and Speciality Filters
function thaat_directory_filters_shortcode() {

    // Get terms for City and Speciality taxonomies
    $cities = get_terms(array(
        'taxonomy' => 'city',
        'hide_empty' => false,
    ));
    $specialities = get_terms(array(
        'taxonomy' => 'speciality',
        'hide_empty' => false,
    ));

    ob_start();
    ?>
    <div style="display:flex; gap:16px;">
        <!-- City Filter Dropdown -->
        <select id="city-filter" style="background:rgba(40, 51, 115, 1); color:#fff;">
            <option value="__all">اسم المدينة</option>
            <?php foreach ($cities as $city) : ?>
                <option value="<?php echo esc_attr($city->slug); ?>"><?php echo esc_html($city->name); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Specialty Filter Dropdown -->
        <select id="specialty-filter" style="background:rgba(40, 51, 115, 1); color:#fff;">
            <option value="__all">اسم التخصص</option>
            <?php foreach ($specialities as $speciality) : ?>
                <option value="<?php echo esc_attr($speciality->slug); ?>"><?php echo esc_html($speciality->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="directory-list"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('directory_filters', 'thaat_directory_filters_shortcode');
// #########################################################################
// #########################################################################
// #########################################################################
// We added in JS file
jQuery(document).ready(function($) {
    $('#city-filter, #specialty-filter').change(function() {
        var city = $('#city-filter').val();
        var specialty = $('#specialty-filter').val();

        $.ajax({
            url: filter_params.ajax_url, // This comes from wp_localize_script
            type: 'POST',
            data: {
                action: 'filter_directories',
                city: city,
                specialty: specialty,
            },
            success: function(response) {
                $('#directory-list').html(response);  // Assuming you have a div with id="directory-list"
            }
        });
    });
});
// ##############################################
// ##############################################
// ##############################################
// Filter directories based on Ajax request
function filter_directories() {
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $specialty = isset($_POST['specialty']) ? sanitize_text_field($_POST['specialty']) : '';

    $args = array(
        'post_type' => 'directories',
        'posts_per_page' => -1, // Show all
        'tax_query' => array(
            'relation' => 'AND',
        ),
    );

    if ($city && $city !== '__all') {
        $args['tax_query'][] = array(
            'taxonomy' => 'city',
            'field'    => 'slug',
            'terms'    => $city,
        );
    }

    if ($specialty && $specialty !== '__all') {
        $args['tax_query'][] = array(
            'taxonomy' => 'speciality',
            'field'    => 'slug',
            'terms'    => $specialty,
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Display your custom post data here (e.g., title, content)
            echo '<div>' . get_the_title() . '</div>';
        }
    } else {
        echo 'No directories found.';
    }

    wp_die();
}
add_action('wp_ajax_filter_directories', 'filter_directories');
add_action('wp_ajax_nopriv_filter_directories', 'filter_directories');
// #########################################################################
// #########################################################################
// #########################################################################
function enqueue_filter_script() {
    wp_enqueue_script('filter_script', get_template_directory_uri() . '/js/filter.js', array('jquery'), null, true);

    wp_localize_script('filter_script', 'filter_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_filter_script');
// #############################################################
// #############################################################
// #############################################################
