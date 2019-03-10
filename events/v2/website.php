<?php

function get_events_list_v2(WP_REST_Request $request)
{

    $page = $request->get_param('page');
    if (empty($page)) {
        $page = 1;
    }

    $arr = [];
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => 9,
        "post_status" => "publish",
        "paged" => $page,
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("c"),
                "compare" => ">=",
                "type" => "DATETIME",
            ],
        ],

    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function get_full_events_list_v2(WP_REST_Request $request)
{
    $arr = [];
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => -1,
        "post_status" => "publish",
        "meta_key" => "start_datetime",
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("c"),
                "compare" => ">=",
                "type" => "DATETIME",
            ],
        ],
    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function get_event_by_slug_v2(WP_REST_Request $request)
{
    $arr = [];
    $slug = $request->get_param('slug');

    $query = new WP_Query([
        'post_type' => 'event_listing',
        'name' => $slug,
        "orderby" => "meta_value",
        "order" => "ASC",
    ]);

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function get_total_events_v2()
{
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

    if ($query->have_posts()) {
        return $query->found_posts;
    }

    wp_reset_query();
}

function get_all_events_v2()
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

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            // echo "<pre>" . print_r($p, true) . "</div>";
            $id = $p->ID;
            $title = $p->post_title;
            $slug = $p->post_name;

            $arr[] = [
                'id' => $id,
                'title' => $title,
                'slug' => $slug,
            ];
        }
    }

    wp_reset_query();

    return $arr;
}

function search_events_v2(WP_REST_Request $request)
{
    $arr = [];
    $keyword = urldecode($request->get_param('keyword'));

    $query = new WP_Query([
        'post_type' => 'event_listing',
        's' => $keyword,
        "post_status" => "publish",
        "posts_per_page" => -1,
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "start_datetime",
                "value" => date("Y-m-d G:i:s"),
                "compare" => ">=",
                "type" => "DATETIME",
            ],
        ],
    ]);

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function search_events_by_tag_v2(WP_REST_Request $request)
{
    $tag = $request->get_param('tag');

    $query = new WP_Query([
        'post_type' => 'event_listing',
        "post_status" => "publish",
        "posts_per_page" => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'event_tag',
                'field' => 'slug',
                'terms' => $tag,
            ),
        ),
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

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function search_events_by_category_v2(WP_REST_Request $request)
{
    $category = $request->get_param('category');
    $eventId = $request->get_param('eventId');

    $query = new WP_Query([
        'post_type' => 'event_listing',
        "post_status" => "publish",
        "posts_per_page" => 3,
        'tax_query' => array(
            array(
                'taxonomy' => 'event_category',
                'field' => 'slug',
                'terms' => $category,
            ),
        ),
        'post__not_in' => [$eventId],
        "orderby" => "meta_value",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "e_date",
                "value" => date("Y-m-d"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
    ]);

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function search_events_by_region_v2(WP_REST_Request $request)
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
        'tax_query' => [
            [
                'taxonomy' => 'event_region',
                'field' => 'slug',
                'terms' => $region,
            ],
        ],
    ]);

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function search_events_by_date_v2(WP_REST_Request $request)
{
    date_default_timezone_set('Canada/Newfoundland');
    $dateDesc = $request->get_param('date');

    switch ($dateDesc) {
        case "today":
            $meta_query = [
                [
                    'key' => 'start_datetime',
                    'value' => [date('Y-m-d')],
                    'compare' => 'IN',
                    'type' => 'DATE',
                ],
            ];
            break;

        case "this-week":
            $start = date('Y-m-d', strtotime('monday this week'));
            $end = date('Y-m-d', strtotime('sunday this week'));
            $meta_query = [
                [
                    'key' => 'start_datetime',
                    'value' => [$start, $end],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ],
            ];
            break;

        case "this-month":
            $start = date('Y-m-d', strtotime('first day of this month'));
            $end = date('Y-m-d', strtotime('last day of this month'));
            $meta_query = [
                [
                    'key' => 'start_datetime',
                    'value' => [$start, $end],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ],
            ];
            break;

        default:
            $meta_query = [];
            break;
    }

    array_push($meta_query, [
        "key" => "start_datetime",
        "value" => date("Y-m-d G:i:s"),
        "compare" => ">=",
        "type" => "DATETIME",
    ]);

    $query = new WP_Query([
        'post_type' => 'event_listing',
        "post_status" => "publish",
        "posts_per_page" => -1,
        'meta_key' => 'start_datetime',
        "meta_query" => $meta_query,
        "orderby" => "meta_value",
        "order" => "ASC",
    ]);

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

add_action('rest_api_init', function () {
    register_rest_route('web/v2', '/events/(?P<page>\d+)', [
        'methods' => 'GET',
        'callback' => 'get_events_list_v2',
        'args' => [
            'page' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('web/v2', '/full-events-list', [
        'methods' => 'GET',
        'callback' => 'get_full_events_list_v2',
    ]);

    register_rest_route('web/v2', '/full-events-list', [
        'methods' => 'GET',
        'callback' => 'get_full_events_list_v2',
    ]);

    register_rest_route('web/v2', '/event/(?P<slug>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'get_event_by_slug_v2',
        'args' => [
            'slug' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param);
                },
            ],
        ],
    ]);

    register_rest_route('web/v2', '/search-events/(?P<keyword>([a-zA-Z0-9-]|%20)+)', [
        'methods' => 'GET',
        'callback' => 'search_events_v2',
    ]);

    register_rest_route('web/v2', '/search-events-by-tag/(?P<tag>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_tag_v2',
    ]);

    register_rest_route('web/v2', '/search-events-by-category/(?P<category>[a-zA-Z0-9-]+)/(?P<eventId>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_category_v2',
    ]);

    register_rest_route('web/v2', '/search-events-by-region/(?P<region>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_region_v2',
    ]);

    register_rest_route('web/v2', '/search-events-by-date/(?P<date>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_date_v2',
    ]);

    register_rest_route('web/v2', '/events-total', [
        'methods' => 'GET',
        'callback' => 'get_total_events_v2',
    ]);

    register_rest_route('web/v2', '/events-all', [
        'methods' => 'GET',
        'callback' => 'get_all_events_v2',
    ]);
});
