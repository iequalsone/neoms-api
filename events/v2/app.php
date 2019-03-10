<?php

function app_get_events_list_v2(WP_REST_Request $request)
{

    $page = $request->get_param('page');
    $region = $request->get_param('region');

    if (empty($page)) {
        $page = 1;
    }

    $arr = [];
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => 20,
        "post_status" => "publish",
        "paged" => $page,
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("c"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = app_build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function app_get_event_by_slug_v2(WP_REST_Request $request)
{
    $arr = [];
    $slug = $request->get_param('slug');

    $query = new WP_Query([
        'post_type' => 'event_listing',
        'name' => $slug,
        "orderby" => "meta_value",
        "order" => "ASC",
    ]);

    $output = app_build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function app_get_events_list_all_v2(WP_REST_Request $request)
{

    $arr = [];
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => -1,
        "post_status" => "publish",
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("c"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = app_build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function app_get_events_by_region_v2(WP_REST_Request $request)
{
    $region = $request->get_param('region');
    $page = $request->get_param('page');

    if (empty($page)) {
        $page = 1;
    }

    $arr = [];
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => 20,
        "post_status" => "publish",
        "paged" => $page,
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("c"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
        'tax_query' => [
            [
                'taxonomy' => 'event_region',
                'field' => 'slug',
                'terms' => $region,
            ],
        ],
    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = app_build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function app_get_events_by_region_all_v2(WP_REST_Request $request)
{
    $region = $request->get_param('region');

    $arr = [];
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => -1,
        "post_status" => "publish",
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("c"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
        'tax_query' => [
            [
                'taxonomy' => 'event_region',
                'field' => 'slug',
                'terms' => $region,
            ],
        ],
    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = app_build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function app_get_total_events_by_region_v2(WP_REST_Request $request)
{

    $region = $request->get_param('region');
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => -1,
        "post_status" => "publish",
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("c"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
        "tax_query" => [
            [
                "taxonomy" => "event_region",
                "field" => "slug",
                "terms" => $region,
            ],
        ],
    ]);

    if ($query->have_posts()) {
        return $query->found_posts;
    }

    wp_reset_query();
}

add_action('rest_api_init', function () {
    register_rest_route('app/v2', '/events/(?P<page>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_get_events_list_v2',
        'args' => [
            'page' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v2', '/event/(?P<slug>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'app_get_event_by_slug_v2',
        'args' => [
            'slug' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v2', '/events-all', [
        'methods' => 'GET',
        'callback' => 'app_get_events_list_all_v2',
    ]);

    register_rest_route('app/v2', '/events-by-region/(?P<region>[a-zA-Z0-9-]+)/(?P<page>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_get_events_by_region_v2',
        'args' => [
            'page' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v2', '/events-by-region-all/(?P<region>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'app_get_events_by_region_all_v2',
    ]);

    register_rest_route('app/v2', '/events-total-by-region/(?P<region>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'app_get_total_events_by_region_v2',
    ]);
});
