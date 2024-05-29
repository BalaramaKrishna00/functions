<?php 

function JobWaveTech_register_styles(){

    wp_enqueue_style('JobWaveTech_stylesheet', get_template_directory_uri() . "/style.css", array(), '1.0', 'all');


}


add_action( 'wp_enqueue_scripts', 'JobWaveTech_register_styles');

function add_custom_query_vars($vars) {
    $vars[] = 'non_it_paged';
    $vars[] = 'wfh_paged';
    $vars[] = 'exp_paged';
    $vars[] = 'walkin_paged';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');
add_theme_support('post-thumbnails');
function custom_excerpt_more($more) {
    global $post;
    return '... <a class="read-more" href="' . get_permalink($post->ID) . '">Read More</a>';
}
add_filter('excerpt_more', 'custom_excerpt_more');
function custom_excerpt_length($length) {
    return 100; // Set the excerpt length to 30 words (adjust as needed)
}
add_filter('excerpt_length', 'custom_excerpt_length', 999);
// Enable support for comments
function mytheme_comment_support() {
    add_theme_support('comments');
}
add_action('after_setup_theme', 'mytheme_comment_support');

// Include the custom post types file
require get_template_directory() . '/custom-post-types.php';



function display_latest_posts_marquee() {
    // Arguments for the query
    $args = array(
        'category_name'  => 'latest', // The slug of your category
        'posts_per_page' => 5,        // Number of posts to display
    );

    // Query the posts
    $latest_posts = new WP_Query($args);

    // Check if there are posts
    if ($latest_posts->have_posts()) {
        echo '<div class="marquee-container"><div class="marquee">';
        while ($latest_posts->have_posts()) {
            $latest_posts->the_post();
            $post_url = get_permalink();
            $post_title = get_the_title();
            echo '<span class="marquee-item"><img src="http://localhost/project/wordpress/wp-content/uploads/2024/05/output-onlinegiftools-6-1.gif" height="12" width="60" alt="Icon"> <a href="' . $post_url . '">' . $post_title . '</a></span>';
        }
        echo '</div></div>';

        // Reset post data
        wp_reset_postdata();
    } else {
        echo '<div class="marquee-container"><div class="marquee"><span>No posts found</span></div></div>';
    }
}










// Add the custom author meta box
function add_custom_author_meta_box() {
    add_meta_box(
        'custom_author_meta_box', // Meta box ID
        'Select Custom Author', // Meta box title
        'display_custom_author_meta_box', // Callback function
        'post', // Post type where this box should appear
        'side', // Context (normal, side, advanced)
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'add_custom_author_meta_box');

// Display the custom author meta box
function display_custom_author_meta_box($post) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field('custom_author_meta_box_nonce_action', 'custom_author_meta_box_nonce');

    // Retrieve existing value from the database.
    $selected_author_id = get_post_meta($post->ID, '_custom_author_id', true);

    // Fetch the custom authors from the 'authors' post type
    $authors = get_posts(array(
        'post_type' => 'authors',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    echo '<select name="custom_author_id">';
    echo '<option value="">Select Author</option>';
    foreach ($authors as $author) {
        echo '<option value="' . esc_attr($author->ID) . '"' . selected($selected_author_id, $author->ID, false) . '>' . esc_html($author->post_title) . '</option>';
    }
    echo '</select>';
}

// Save the custom author meta box data
function save_custom_author_meta_box_data($post_id) {
    // Check if our nonce is set.
    if (!isset($_POST['custom_author_meta_box_nonce'])) {
        return $post_id;
    }
    $nonce = $_POST['custom_author_meta_box_nonce'];

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($nonce, 'custom_author_meta_box_nonce_action')) {
        return $post_id;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'post' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }

    // Sanitize user input.
    $new_author_id = (isset($_POST['custom_author_id']) && !empty($_POST['custom_author_id'])) ? sanitize_text_field($_POST['custom_author_id']) : '';

    // Update the meta field in the database.
    update_post_meta($post_id, '_custom_author_id', $new_author_id);
}
add_action('save_post', 'save_custom_author_meta_box_data');
function remove_default_author_meta_box() {
    remove_meta_box('authordiv', 'post', 'normal');
}

function latest_posts_table_shortcode() {
    // Start the table
    $output = '<table class="latest-posts-table">';
    
    // Table heading
    $output .= '<tr><th colspan="5">Latest Posts</th></tr>';

    // Get the 5 most recent posts from the "IT-JOBS" category
    $args = array(
        'posts_per_page' => 5,
        'category_name' => 'it-jobs',
    );
    $query = new WP_Query($args);

    // Loop through the posts
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Get post data
            $post_id = get_the_ID();
            $post_title = get_the_title();
            $post_date = get_the_date();
            $post_excerpt = get_the_excerpt();
            $post_thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail');
            $apply_link = get_permalink();

            // Add table row for each post
            $output .= '<tr>';
            $output .= '<td><img src="' . $post_thumbnail . '" alt="' . $post_title . '"></td>';
            $output .= '<td>' . $post_title . '</td>';
            $output .= '<td>' . wp_trim_words($post_excerpt, 20) . '</td>';
            $output .= '<td>' . $post_date . '</td>';
            $output .= '<td><a href="' . $apply_link . '">Apply</a></td>';
            $output .= '</tr>';
        }
    } else {
        // If no posts found
        $output .= '<tr><td colspan="5">No posts found</td></tr>';
    }

    // End the table
    $output .= '</table>';

    // Reset Post Data
    wp_reset_postdata();

    return $output;
}
add_shortcode('latest_IT_posts_table', 'latest_posts_table_shortcode');


?>