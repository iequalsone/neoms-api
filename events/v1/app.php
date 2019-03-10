<?php

function app_get_events_list(WP_REST_Request $request)
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
                "key" => "e_date",
                "value" => date("Y-m-d"),
                "compare" => ">=",
                "type" => "DATE",
            ],
        ],
    ]);

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = app_build_events_query($query);

    wp_reset_query();

    return $output;
}

function app_get_events_list_all(WP_REST_Request $request)
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

    // echo "<pre>" . print_r($query->posts, true) . "</pre>";

    $output = app_build_events_query($query);

    wp_reset_query();

    return $output;
}

function app_get_events_by_region(WP_REST_Request $request)
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
                "key" => "e_date",
                "value" => date("Y-m-d"),
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

    $output = app_build_events_query($query);

    wp_reset_query();

    return $output;
}

function app_get_events_by_region_all(WP_REST_Request $request)
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
                "key" => "e_date",
                "value" => date("Y-m-d"),
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

    $output = app_build_events_query($query);

    wp_reset_query();

    return $output;
}

function app_get_total_events_by_region(WP_REST_Request $request)
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
                "key" => "e_date",
                "value" => date("Y-m-d"),
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

function app_authentication(WP_REST_Request $request)
{
    $response = array(
        'data' => array(),
        'msg' => 'Invalid email or password',
        'status' => false,
    );

    /* Sanitize all received posts */
    foreach ($_POST as $k => $value) {
        $_POST[$k] = sanitize_text_field($value);
    }

    /**
     * Login Method
     *
     */
    if (isset($_POST['type']) && $_POST['type'] == 'login') {

        /* Get user data */
        $user = get_user_by('email', $_POST['email']);

        if ($user) {
            $password_check = wp_check_password($_POST['password'], $user->user_pass, $user->ID);

            if ($password_check) {
                /* Generate a unique auth token */
                $token = wp_generate_password(30, false);

                /* Store / Update auth token in the database */
                if (update_user_meta($user->ID, 'auth_token', $token)) {

                    /* Return generated token and user ID*/
                    $response['status'] = true;
                    $response['data'] = array(
                        'auth_token' => $token,
                        'user_id' => $user->ID,
                        'user_login' => $user->user_login,
                    );
                    $response['msg'] = 'Successfully Authenticated';
                }
            }
        }
    }

    return $response;
}

function app_notifications_init(WP_REST_Request $request)
{
    date_default_timezone_set('Canada/Newfoundland');

    $user_id = $request->get_param('userid');
    $user = get_user_by('id', $user_id);

    update_user_meta($user_id, 'current_login', date("Y-m-d H:i:s"));

    $current_login = strtotime(get_user_meta($user_id, 'current_login', 1));
    $previous_login = strtotime(get_user_meta($user_id, 'previous_login', 1));

    if (!empty($current_login) && !empty($previous_login)) {
        $n_query = new \WP_Query([
            'post_type' => 'notification',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'user_id',
                    'value' => $user_id,
                    'compare' => '=',
                ],
            ],
        ]);

        $e_ids = [];

        if ($n_query->have_posts()) {
            foreach ($n_query->posts as $np) {
                $nid = $np->ID;
                $e_ids[] = get_post_meta($nid, 'event_id', 1);
            }
        }

        wp_reset_query();

        $query = new \WP_Query([
            'post_type' => 'event_listing',
            'posts_per_page' => -1,
            'post__not_in' => $e_ids,
            'meta_key' => 'e_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'e_date',
                    'value' => date("Y-m-d", $previous_login),
                    'type' => 'DATETIME',
                    'compare' => '>',
                ],
                [
                    'key' => 'e_date',
                    'value' => date("Y-m-d", $current_login),
                    'type' => 'DATETIME',
                    'compare' => '<',
                ],
            ],
        ]);

        if ($query->have_posts()) {
            foreach ($query->posts as $p) {
                // echo "<pre>" . print_r($p, true) . "</pre>";
                $eid = $p->ID;
                $title = $p->post_title;
                $excerpt = $p->post_excerpt;
                $slug = $p->post_name;
                $e_date = get_post_meta($p->ID, 'e_date', 1);
                // $image = get_post_meta($p->ID, 'e_image');
                // $author = $p->post_author;
                // $member_profile_image = app_get_user_profile_image($author);

                // if (!empty($image[0])) {
                //   $image = wp_get_attachment_image_src($image[0]->ID, 'event-thumb');
                // } else {
                //    $image = false;
                // }

                $up_post = [
                    'post_type' => 'notification',
                    'post_title' => $title,
                    'post_status' => 'publish',
                    'post_excerpt' => $excerpt,
                ];

                // Insert a new User Created Event
                $post_id = wp_insert_post($up_post);
                add_post_meta($post_id, 'user_id', $user_id);
                add_post_meta($post_id, 'user_name', $user->user_email);
                add_post_meta($post_id, 'event_id', $eid);
                add_post_meta($post_id, 'event_slug', $slug);
            }
        }

        wp_reset_query();
    }

    return;
}

function app_get_notifications(WP_REST_Request $request)
{
    $user_id = $request->get_param('userid');
    $user = get_user_by('id', $user_id);

    $query = new \WP_Query([
        'post_type' => 'notification',
        'posts_per_page' => -1,
        'meta_query' => [
            "relation" => "AND",
            [
                'key' => 'user_id',
                'value' => $user_id,
                'compare' => '=',
            ],
            [
                "relation" => "OR",
                [
                    'key' => 'archive',
                    'value' => "0",
                    'compare' => '=',
                ],
                [
                    'key' => 'archive',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ],
    ]);

    if ($query->have_posts()) {
        $result = [];
        foreach ($query->posts as $p) {
            $id = $p->ID;
            $title = $p->post_title;
            $excerpt = $p->post_excerpt;
            $event_id = get_post_meta($id, 'event_id', 1);
            $event_slug = get_post_meta($id, 'event_slug', 1);
            $permalink = get_the_permalink($event_id);
            $archive = get_post_meta($id, 'archive', 1);

            $result[] = [
                "id" => $id,
                "title" => html_entity_decode($title),
                "slug" => $event_slug,
                "excerpt" => $excerpt,
                "permalink" => $permalink,
                "archive" => $archive,
            ];
        }
    }

    wp_reset_query();

    return $result;
}

function app_archive_notification(WP_REST_Request $request)
{
    $notification_id = $request->get_param('nid');

    // /* Sanitize all received posts */
    // foreach ($_POST as $k => $value) {
    //     $_POST[$k] = sanitize_text_field($value);
    // }

    $result = update_post_meta($notification_id, 'archive', 1);

    return $result;
}

function app_enable_push_notifications(WP_REST_Request $request)
{
    $response = [
        'data' => [],
        'msg' => 'Error enabling push notifications',
        'status' => false,
    ];

    /* Sanitize all received posts */
    // foreach ($_POST as $k => $value) {
    //     $_POST[$k] = sanitize_text_field($value);
    // }

    if (isset($_POST['type']) && $_POST['type'] == 'enablePushNotifications') {
        $user = new WP_Query([
            'post_type' => 'user_profile',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    "key" => "user_id",
                    "value" => $_POST['userID'],
                    "compare" => "=",
                ],
            ],
        ]);

        if ($user->have_posts()) {
            foreach ($user->posts as $p) {
                if (update_post_meta($p->ID, 'enable_push_notifications', $_POST['flag'])) {
                    $response['status'] = true;
                    $response['data'] = array(
                        'flag' => $_POST['flag'],
                        'post_id' => $p->ID,
                    );
                    $response['msg'] = 'Notifications Status updated';
                }
            }
        } else {
            $response['data'] = $user;
        }

        /* Get user data */
        // $user = get_user_by('id', $_POST['userID']);

        // if ($user) {
        //   if(update_user_meta($user->ID, 'enable_push_notifications', $_POST['flag'])){
        //     $response['status'] = true;
        //     $response['data'] = array(
        //         'flag' => $_POST['flag']
        //     );
        //     $response['msg'] = 'Push notifications enabled';
        //   }
        // }
    }

    return $response;
}

function app_get_enable_push_notifications_status(WP_REST_Request $request)
{
    $user_id = $request->get_param('userid');

    $query = new \WP_Query([
        "post_type" => "user_profile",
        "posts_per_page" => -1,
        "meta_query" => [
            [
                "key" => "user_id",
                "value" => $user_id,
                "compare" => "=",
            ],
        ],
    ]);

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $flag = get_post_meta($p->ID, 'enable_push_notifications', 1);
        }
    }

    $response = [
        "flag" => $flag,
    ];

    return $flag;
}

function app_save_expo_push_token(WP_REST_Request $request)
{
    $response = array(
        'data' => [],
        'msg' => 'error'
    );

    $user = get_user_by('email', $request->get_param('userEmail'));
    $token = $request->get_param('token');

    $query = new \WP_Query([
        "post_type" => "user_profile",
        "posts_per_page" => 1,
        "meta_query" => [
            [
                "key" => "user_id",
                "value" => $user->ID,
                "compare" => "=",
            ],
        ],
    ]);

    $pid = -1;

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $pid = $p->ID;
        }
    }

    if (update_post_meta($pid, 'expo_push_token', $token)) {

        /* Return generated token and user ID*/
        $response['status'] = true;
        $response['data'] = array(
            'expo_push_token' => $request->get_param('token'),
            'user_id' => $user->ID,
        );
        $response['msg'] = 'Successful';
    }

    // /**
    //  * Login Method
    //  *
    //  */
    // if (isset($_POST['type']) && $_POST['type'] == 'login') {

    //     /* Get user data */
    //     $user = get_user_by('email', $_POST['email']);

    //     if ($user) {
    //         $password_check = wp_check_password($_POST['password'], $user->user_pass, $user->ID);

    //         if ($password_check) {
    //             /* Generate a unique auth token */
    //             $token = wp_generate_password(30, false);

    //             /* Store / Update auth token in the database */
    //             if (update_user_meta($user->ID, 'auth_token', $token)) {

    //                 /* Return generated token and user ID*/
    //                 $response['status'] = true;
    //                 $response['data'] = array(
    //                     'auth_token' => $token,
    //                     'user_id' => $user->ID,
    //                     'user_login' => $user->user_login,
    //                 );
    //                 $response['msg'] = 'Successfully Authenticated';
    //             }
    //         }
    //     }
    // }

    return $response;
}

function app_get_users_with_push_notifications(WP_REST_Request $request)
{
    $response = [];

    $users = new \WP_Query([
        "post_type" => "user_profile",
        "posts_per_page" => -1,
        "meta_query" => [
            [
                "key" => "enable_push_notifications",
                "value" => 1,
                "compare" => "=",
            ],
        ],
    ]);

    $user_info = [];

    if ($users->have_posts()) {
        foreach ($users->posts as $p) {
            $user_id = get_post_meta($p->ID, "user_id", 1);
            $token = get_post_meta($p->ID, "expo_push_token", 1);
            $user_info[] = [
                "ID" => $user_id,
                "token" => $token,
            ];
        }
    }

    foreach ($user_info as $ui) {
        $q2 = new \WP_Query([
            "post_type" => "notification",
            "posts_per_page" => -1,
            "meta_query" => [
                "relation" => "AND",
                [
                    "key" => "user_id",
                    "value" => $ui['ID'],
                    "compare" => "=",
                ],
                [
                    "relation" => "OR",
                    [
                        'key' => 'archive',
                        'value' => "0",
                        'compare' => '=',
                    ],
                    [
                        'key' => 'archive',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ],
        ]);

        if (($q2->found_posts > 0) && !empty($ui['token'])) {
            // Data in JSON format
            $response[] = [
                'token' => $ui['token'],
                'message' => 'You have ' . $q2->found_posts . ' unread notifications',
            ];
        }
    }

    return $response;
}

function app_save_logout_datetime(WP_REST_Request $request)
{
    date_default_timezone_set('Canada/Newfoundland');
    $response = array(
        'data' => [],
        'msg' => 'error'
    );

    $dt = date("Y-m-d H:i:s");
    $userID = $request->get_param('userID');

    if (update_user_meta($userID, 'previous_login', $dt)) {

        /* Return generated token and user ID*/
        $response['status'] = true;
        $response['data'] = array(
            'logout_datetime' => $dt,
            'user_id' => $userID,
        );
        $response['msg'] = 'Successful';
    }

    return $response;
}

add_action('rest_api_init', function () {
    register_rest_route('app/v1', '/events/(?P<page>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_get_events_list',
        'args' => [
            'page' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v1', '/events-all', [
        'methods' => 'GET',
        'callback' => 'app_get_events_list_all',
    ]);

    register_rest_route('app/v1', '/events-by-region/(?P<region>[a-zA-Z0-9-]+)/(?P<page>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_get_events_by_region',
        'args' => [
            'page' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v1', '/events-by-region-all/(?P<region>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'app_get_events_by_region_all',
    ]);

    register_rest_route('app/v1', '/events-total-by-region/(?P<region>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'app_get_total_events_by_region',
    ]);

    register_rest_route('app/v1', '/authentication', [
        'methods' => 'POST',
        'callback' => 'app_authentication',
    ]);
    register_rest_route('app/v1', '/notifiations-init/(?P<userid>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_notifications_init',
        'args' => [
            'userid' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v1', '/get-notifications/(?P<userid>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_get_notifications',
        'args' => [
            'userid' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v1', '/archive-notification/(?P<nid>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_archive_notification',
        'args' => [
            'nid' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v1', '/get-enable-push-notifications-status/(?P<userid>\d+)', [
        'methods' => 'GET',
        'callback' => 'app_get_enable_push_notifications_status',
        'args' => [
            'userid' => [
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
    ]);

    register_rest_route('app/v1', '/enable-push-notifications', [
        'methods' => 'POST',
        'callback' => 'app_enable_push_notifications',
    ]);

    register_rest_route('app/v1', '/save-expo-push-token', [
        'methods' => 'POST',
        'callback' => 'app_save_expo_push_token',
    ]);

    register_rest_route('app/v1', '/get-users-with-push-notifications', [
        'methods' => 'GET',
        'callback' => 'app_get_users_with_push_notifications',
    ]);

    register_rest_route('app/v1', '/save-logout-datetime', [
        'methods' => 'POST',
        'callback' => 'app_save_logout_datetime',
    ]);

});
