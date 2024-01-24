<?php
/*
Plugin Name: Custom Search Plugin
Description: Custom auto-suggestion search by post title with shortcode.
Version: 1.0
Author: Dalveer Nayak Wordpress Developer
*/

// Register shortcode for the search bar
function custom_search_shortcode() {
    ob_start(); ?>

    <div class="custom-search">
        <input type="text" id="custom-search-input" class="form-control" placeholder="Search by title...">
        <ul id="custom-suggestions-list"></ul>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            $('#custom-search-input').on('input', function () {
                var searchTerm = $(this).val();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'custom_search',
                        term: searchTerm,
                    },
                    success: function (response) {
                        var suggestionsList = $('#custom-suggestions-list');
                        suggestionsList.empty();

                        if (response.length > 0) {
                            $.each(response, function (index, item) {
                                suggestionsList.append('<li><a href="' + item.value + '">' + item.label + '</a></li>');
                            });
                            suggestionsList.show();
                        } else {
                            suggestionsList.hide();
                        }
                    },
                });
            });
        });
    </script>

    <?php
    return ob_get_clean();
}

add_shortcode('custom_search', 'custom_search_shortcode');

// AJAX handler for the search suggestion
function custom_search_suggestions() {
    $search_term = sanitize_text_field($_GET['term']);

    $args = array(
        //'post_type'      => 'page',
        'posts_per_page' => 5,
        's'              => $search_term,
    );

    $query = new WP_Query($args);

    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'label' => get_the_title(),
                'value' => get_the_permalink(),
            );
        }
    }

    wp_send_json($results);
    wp_die();
}

add_action('wp_ajax_custom_search', 'custom_search_suggestions');
add_action('wp_ajax_nopriv_custom_search', 'custom_search_suggestions');
