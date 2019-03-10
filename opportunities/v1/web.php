<?php

function get_opportunities_list(WP_REST_Request $request)
{

    $arr = [];
    $query = new WP_Query([
        "post_type" => "opportunity",
        "posts_per_page" => -1,
        "post_status" => "publish",
        "paged" => $page,
        "meta_key" => "deadline",
        "orderby" => "meta_value",
        "order" => "DESC",
    ]);

    $output = build_opportunities_query($query);

    wp_reset_query();

    return $output;
}

function get_opportunities_item_by_slug(WP_REST_Request $request)
{
    $arr = [];
    $slug = $request->get_param('slug');

    $query = new WP_Query([
        'post_type' => 'opportunity',
        'name' => $slug,
        "meta_key" => "deadline",
        "orderby" => "meta_value",
        "order" => "DESC",
    ]);

    $output = build_opportunities_query($query);

    wp_reset_query();

    return $output;
}

// function get_total_opportunities_items()
// {
//     $query = new WP_Query([
//         "post_type" => "opportunity",
//         "posts_per_page" => -1,
//         "post_status" => "publish",
//         "meta_key" => "deadline",
//         "orderby" => "meta_value",
//         "order" => "DESC",
//     ]);

//     if ($query->have_posts()) {
//         return $query->found_posts;
//     }

//     wp_reset_query();
// }

// function get_all_news_items()
// {
//     $arr = [];
//     $query = new WP_Query([
//         "post_type" => "news_item",
//         "posts_per_page" => -1,
//         "post_status" => "publish",
//         "meta_key" => "publication_date",
//         "orderby" => "meta_value",
//         "order" => "ASC",
//     ]);

//     if ($query->have_posts()) {
//         foreach ($query->posts as $p) {
//             // echo "<pre>" . print_r($p, true) . "</div>";
//             $id = $p->ID;
//             $title = $p->post_title;
//             $slug = $p->post_name;

//             $arr[] = [
//                 'id' => $id,
//                 'title' => $title,
//                 'slug' => $slug,
//             ];
//         }
//     }

//     wp_reset_query();

//     return $arr;
// }

// function search_news_items(WP_REST_Request $request)
// {
//     $arr = [];
//     $keyword = urldecode($request->get_param('keyword'));

//     $query = new WP_Query([
//         'post_type' => 'news_item',
//         's' => $keyword,
//         "post_status" => "publish",
//         "posts_per_page" => -1,
//         "meta_key" => "publication_date",
//         "orderby" => "meta_value",
//         "order" => "DESC",
//     ]);

//     $output = build_news_query($query);

//     wp_reset_query();

//     return $output;
// }

function search_opportunities_by_tag(WP_REST_Request $request)
{
    $tag = $request->get_param('tag');

    $query = new WP_Query([
        'post_type' => 'opportunity',
        "post_status" => "publish",
        "posts_per_page" => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'opportunity_tag',
                'field' => 'slug',
                'terms' => $tag,
            ),
        ),
        "meta_key" => "deadline",
        "orderby" => "meta_value",
        "order" => "ASC",
    ]);

    $output = build_opportunities_query($query);

    wp_reset_query();

    return $output;
}

function search_opportunities_by_category(WP_REST_Request $request)
{
    $category = $request->get_param('category');
    $newsId = $request->get_param('newsId');

    $query = new WP_Query([
        'post_type' => 'news_item',
        "post_status" => "publish",
        "posts_per_page" => 3,
        'tax_query' => array(
            array(
                'taxonomy' => 'news_category',
                'field' => 'slug',
                'terms' => $category,
            ),
        ),
        'post__not_in' => [$newsId],
        "meta_key" => "publication_date",
        "orderby" => "meta_value",
        "order" => "ASC",
    ]);

    $output = build_news_query($query);

    wp_reset_query();

    return $output;
}

add_action('rest_api_init', function () {
    register_rest_route('web/v1', '/opportunities', [
        'methods' => 'GET',
        'callback' => 'get_opportunities_list',
        'args' => [
            'page' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('web/v1', '/opportunities-item/(?P<slug>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'get_opportunities_item_by_slug',
        'args' => [
            'slug' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param);
                },
            ],
        ],
    ]);

    // register_rest_route('web/v1', '/news-items-total', [
    //     'methods' => 'GET',
    //     'callback' => 'get_total_news_items',
    // ]);

    // register_rest_route('web/v1', '/news-all', [
    //     'methods' => 'GET',
    //     'callback' => 'get_all_news_items',
    // ]);

    // register_rest_route('web/v1', '/search-news/(?P<keyword>([a-zA-Z0-9-]|%20)+)', [
    //     'methods' => 'GET',
    //     'callback' => 'search_news_items',
    // ]);

    register_rest_route('web/v1', '/search-opportunities-by-tag/(?P<tag>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_opportunities_by_tag',
    ]);

    register_rest_route('web/v1', '/search-opportunities-by-category/(?P<category>[a-zA-Z0-9-]+)/(?P<opportunityId>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_opportunities_by_category',
    ]);
});
