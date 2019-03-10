<?php

function get_events_list(WP_REST_Request $request)
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
                "key" => "e_date",
                "value" => date("Y-m-d"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],

    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = build_events_query($query);

    wp_reset_query();

    return $output;
}

function get_event_by_slug(WP_REST_Request $request)
{
    $arr = [];
    $slug = $request->get_param('slug');

    $query = new WP_Query([
        'post_type' => 'event_listing',
        'name' => $slug,
        "orderby" => "meta_value",
        "order" => "ASC",
    ]);

    $output = build_events_query($query);

    wp_reset_query();

    return $output;
}

function get_total_events()
{
    $query = new WP_Query([
        "post_type" => "event_listing",
        "posts_per_page" => -1,
        "post_status" => "publish",
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

    if ($query->have_posts()) {
        return $query->found_posts;
    }

    wp_reset_query();
}

function get_all_events()
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
                "key" => "e_date",
                "value" => date("Y-m-d"),
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

function get_all_regions()
{
    $arr = [];
    $terms = get_terms(array(
        'taxonomy' => 'event_region',
        'hide_empty' => true,
    ));

    if (!empty($terms)) {
        foreach ($terms as $t) {
            switch ($t->slug) {
                case "avalon":
                    $lat = 47.348113;
                    $lng = -53.401582;
                    break;

                case "burin":
                    $lat = 47.840799;
                    $lng = -56.172883;
                    break;

                case "central":
                    $lat = 48.971189;
                    $lng = -54.217317;
                    break;

                case "western":
                    $lat = 48.529324;
                    $lng = -58.018586;
                    break;

                case "northern":
                    $lat = 50.560546;
                    $lng = -56.656282;
                    break;

                case "labrador":
                    $lat = 53.326410;
                    $lng = -60.320047;
                    break;

                default:
                    $lat = "";
                    $lng = "";
                    break;
            }

            if (!empty($lat) && !empty($lng)) {
                $arr[] = [
                    'id' => $t->term_id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                    'lat' => $lat,
                    'lng' => $lng,
                ];
            }
        }
    }

    return $arr;
}

function search_events(WP_REST_Request $request)
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

    // $output = build_events_query($query);

    $output = build_events_query_v2($query);

    wp_reset_query();

    return $output;
}

function search_events_by_tag(WP_REST_Request $request)
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
                "key" => "e_date",
                "value" => date("Y-m-d"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
    ]);

    $output = build_events_query($query);

    wp_reset_query();

    return $output;
}

function search_events_by_category(WP_REST_Request $request)
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

    $output = build_events_query($query);

    wp_reset_query();

    return $output;
}

function search_events_by_region(WP_REST_Request $request)
{
    $region = $request->get_param('region');

    $query = new WP_Query([
        'post_type' => 'event_listing',
        "post_status" => "publish",
        "posts_per_page" => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'event_region',
                'field' => 'slug',
                'terms' => $region,
            ),
        ),
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

    $output = build_events_query($query);

    wp_reset_query();

    return $output;
}

function search_events_by_date(WP_REST_Request $request)
{
    date_default_timezone_set('Canada/Newfoundland');
    $dateDesc = $request->get_param('date');

    switch ($dateDesc) {
        case "today":
            $meta_query = [
                [
                    'key' => 'e_date',
                    'value' => [date('Ymd')],
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
                    'key' => 'e_date',
                    'value' => [$start, $end],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ],
            ];
            break;

        case "this-month":
            $start = date('Ymd', strtotime('first day of this month'));
            $end = date('Ymd', strtotime('last day of this month'));
            $meta_query = [
                [
                    'key' => 'e_date',
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
        "key" => "e_date",
        "value" => date("Y-m-d"),
        "compare" => ">=",
        "type" => "DATE",
    ]);

    $query = new WP_Query([
        'post_type' => 'event_listing',
        "post_status" => "publish",
        "posts_per_page" => -1,
        'meta_key' => 'e_date',
        "meta_query" => $meta_query,
        "orderby" => "meta_value",
        "order" => "ASC",
    ]);

    $output = build_events_query($query);

    wp_reset_query();

    return $output;
}

add_action('rest_api_init', function () {
    register_rest_route('web/v1', '/events/(?P<page>\d+)', [
        'methods' => 'GET',
        'callback' => 'get_events_list',
        'args' => [
            'page' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('web/v1', '/event/(?P<slug>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'get_event_by_slug',
        'args' => [
            'slug' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param);
                },
            ],
        ],
    ]);

    register_rest_route('web/v1', '/search-events/(?P<keyword>([a-zA-Z0-9-]|%20)+)', [
        'methods' => 'GET',
        'callback' => 'search_events',
    ]);

    register_rest_route('web/v1', '/search-events-by-tag/(?P<tag>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_tag',
    ]);

    register_rest_route('web/v1', '/search-events-by-category/(?P<category>[a-zA-Z0-9-]+)/(?P<eventId>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_category',
    ]);

    register_rest_route('web/v1', '/search-events-by-region/(?P<region>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_region',
    ]);

    register_rest_route('web/v1', '/search-events-by-date/(?P<date>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'search_events_by_date',
    ]);

    register_rest_route('web/v1', '/events-total', [
        'methods' => 'GET',
        'callback' => 'get_total_events',
    ]);

    register_rest_route('web/v1', '/events-all', [
        'methods' => 'GET',
        'callback' => 'get_all_events',
    ]);

    register_rest_route('web/v1', '/regions-all', [
        'methods' => 'GET',
        'callback' => 'get_all_regions',
    ]);

});
