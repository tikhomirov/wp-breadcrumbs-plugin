<?php
/**
 * Plugin Name: Breadcrumbs
 * Plugin URL: https://rwsite.ru
 * Description: WordPress breadcrumbs plugin with support schema.org. PHP 8.2 ready. How to use: <code>breadcrumbs();</code> or shortcode: <code>[breadcrumbs]</code>
 * Version: 1.0.1
 * Text Domain: breadcrumbs
 * Domain Path: /languages
 * Author: Aleksey Tikhomirov
 *
 * Requires at least: 4.6
 * Tested up to: 6.8
 * Requires PHP: 8.0+
 *
 */


defined('ABSPATH') or die('Nothing here!');

add_action('init', static function (): void {
    load_plugin_textdomain('breadcrumbs', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

require_once __DIR__ . '/SchemaOrgBreadCrumbs.php';
add_action('wp', [SchemaOrgBreadCrumbs::class, 'instance']);

/**
 * Show breadcrumbs tree
 */
if (!function_exists('breadcrumbs')) :
    add_shortcode('breadcrumbs', 'breadcrumbs');
    function breadcrumbs($args = [])
    {
        // Do not display on the homepage

        // Set default arguments
        $defaults = [
            'separator_icon'      => '&gt;',
            'breadcrumbs_id'      => 'breadcrumbs',
            'breadcrumbs_classes' => 'breadcrumb-trail breadcrumbs',
            'home_title'          => esc_html__('Главная', 'understrap'),
        ];
        // Parse any arguments added
        $args = apply_filters('breadcrumbs_args', wp_parse_args($args, $defaults));
        // Set variable for adding separator markup
        $separator = '<span class="separator"> ' . esc_attr($args['separator_icon']) . ' </span>';
        // Get global post object
        global $post;
        /***** Begin Markup *****/
        // Open the breadcrumbs
        $html = '<div id="' . esc_attr($args['breadcrumbs_id']) . '" class="' . esc_attr($args['breadcrumbs_classes']) . '">';
        // Add Homepage link & separator (always present)
        $html .= '<span class="item-home"><a class="bread-link bread-home" href="' . esc_url(home_url('/')) . '" title="' . esc_attr($args['home_title']) . '">' . esc_attr($args['home_title']) . '</a></span>';

        if (!is_front_page()) {
            $html .= $separator;
        }
        // Post

        if (is_front_page()) {
            return '';
        } elseif (is_singular('post')) {
            $category = get_the_category();
            if (! empty($category) && ! is_wp_error($category)) {
                $category_values = array_values($category);
                $last_category = end($category_values);
                if ($last_category instanceof WP_Term) {
                    $cat_parents = rtrim((string) get_category_parents($last_category->term_id, true, ','), ',');
                    if ($cat_parents !== '') {
                        foreach (explode(',', $cat_parents) as $parent) {
                            $html .= '<span class="item-cat">' . wp_kses($parent, wp_kses_allowed_html('a')) . '</span>';
                            $html .= $separator;
                        }
                    }
                }
            }
            $post_id = $post->ID ?? get_the_ID();
            $html .= '<span class="item-current item-' . esc_attr((string) $post_id) . '"><span class="bread-current bread-' . esc_attr((string) $post_id) . '" title="' . esc_attr(get_the_title()) . '">' . esc_html(get_the_title()) . '</span></span>';
        } // Page
        elseif (is_singular('page')) {
            // if page has a parent page
            if ($post->post_parent) {
                // Get all parents
                $parents = get_post_ancestors($post->ID);
                // Sort parents into the right order
                $parents = array_reverse($parents);
                // Add each parent to markup
                foreach ($parents as $parent) {
                    $html .= '<span class="item-parent item-parent-' . esc_attr($parent) . '"><a class="bread-parent bread-parent-' . esc_attr($parent) . '" href="' . get_permalink($parent) . '" title="' . get_the_title($parent) . '">' . get_the_title($parent) . '</a></span>';
                    $html .= $separator;
                }
            }
            // Current page
            $html .= '<span class="item-current item-' . $post->ID . '"><span title="' . get_the_title() . '"> ' . get_the_title() . '</span></span>';
        } // Attachment
        elseif (is_singular('attachment')) {
            // Get the parent post ID
            $parent_id = $post->post_parent;
            // Get the parent post title
            $parent_title = get_the_title($parent_id);
            // Get the parent post permalink
            $parent_permalink = get_permalink($parent_id);
            // Add markup
            $html .= '<span class="item-parent"><a class="bread-parent" href="' . esc_url($parent_permalink) . '" title="' . esc_attr($parent_title) . '">' . esc_attr($parent_title) . '</a></span>';
            $html .= $separator;
            // Add name of attachment
            $html .= '<span class="item-current item-' . $post->ID . '"><span title="' . get_the_title() . '"> ' . get_the_title() . '</span></span>';
        } // Custom Post Types
        elseif (is_singular()) {
            // Get the post type
            $post_type = get_post_type();
            // Get the post object
            $post_type_object = get_post_type_object($post_type);
            // Get the post type archive
            $post_type_archive = get_post_type_archive_link($post_type);
            // Add taxonomy link and separator
            $html .= '<span class="item-cat item-custom-post-type-' . esc_attr($post_type) . '"><a class="bread-cat bread-custom-post-type-' . esc_attr($post_type) . '" href="' . esc_url($post_type_archive) . '" title="' . esc_attr($post_type_object->labels->name) . '">' . esc_attr($post_type_object->labels->name) . '</a></span>';
            $html .= $separator;
            // Add name of Post
            $html .= '<span class="item-current item-' . $post->ID . '"><span class="bread-current bread-' . $post->ID . '" title="' . $post->post_title . '">' . $post->post_title . '</span></span>';
        } // Category
        elseif (is_category() && get_queried_object() instanceof WP_Term) {
            // Get category object
            $parent = get_queried_object()->parent;
            // If there is a parent category...
            if ($parent !== 0) {
                // Get the parent category object
                $parent_category = get_term($parent, 'category');
                // Get the link to the parent category
                $term_link = get_term_link($parent, 'category');
                // Output the markup for the parent category item
                $html .= '<span class="item-parent item-parent-' . esc_attr($parent_category->slug) . '"><a class="bread-parent bread-parent-' . esc_attr($parent_category->slug) . '" href="' . esc_url($term_link) . '" title="' . esc_attr($parent_category->name) . '">' . esc_attr($parent_category->name) . '</a></span>';
                $html .= $separator;
            }
            // Add category markup
            $html .= '<span class="item-current item-cat"><span class="bread-current bread-cat">' . esc_html(single_cat_title('', false)) . '</span></span>';
        } // Tag
        elseif (is_tag()) {
            // Add tag markup
            $html .= '<span class="item-current item-tag"><span class="bread-current bread-tag">' . single_tag_title('', false) . '</span></span>';
        } // Author
        elseif (is_author() && get_queried_object() instanceof WP_User) {
            // Add author markup
            $html .= '<span class="item-current item-author"><span class="bread-current bread-author">' . (get_queried_object()->display_name ?? '') . '</span></span>';
        } // Day
        elseif (is_day()) {
            // Add day markup
            $html .= '<span class="item-current item-day"><span class="bread-current bread-day">' . get_the_date() . '</span></span>';
        } // Month
        elseif (is_month()) {
            // Add month markup
            $html .= '<span class="item-current item-month"><span class="bread-current bread-month">' . get_the_date('F Y') . '</span></span>';
        } // Year
        elseif (is_year()) {
            // Add year markup
            $html .= '<span class="item-current item-year"><span class="bread-current bread-year">' . get_the_date('Y') . '</span></span>';
        } // Custom Taxonomy
        elseif (is_archive()) {
            // get the name of the taxonomy
            $custom_tax_name = get_queried_object()->name;
            // Add markup for taxonomy
            $html .= '<span class="item-current item-archive"><span class="bread-current bread-archive">' . esc_attr($custom_tax_name) . '</span></span>';
        } // Search
        elseif (is_search()) {
            // Add search markup
            $html .= '<span class="item-current item-search"><span class="bread-current bread-search">' . esc_html__('Search results for', 'theme') . ': ' . get_search_query() . '</span></span>';
        } // 404
        elseif (is_404()) {
            // Add 404 markup
            $html .= '<span>' . esc_html__('Error 404', 'theme') . '</span>';
        } elseif (is_home() && isset($_GET['read-it-later'])) {
            // Add read later markup
            $html .= '<span>' . esc_html__('Read It Later', 'theme') . '</span>';
        } else {
            $html .= '<span class="item-current"><span class="bread-current">' . esc_attr(get_the_title(get_the_ID())) . '</span></span>';
        }

        // Close breadcrumb container
        $html .= '</div>';

        return apply_filters('breadcrumbs_filter', $html);
    }
endif;