<?php
function build_events_query($query)
{
    $arr = [];

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $tag_arr = [];
            $cat_arr = [];
            $id = $p->ID;
            $author = $p->post_author;
            $slug = $p->post_name;
            $title = $p->post_title;
            $excerpt = $p->post_excerpt;
            $content = apply_filters('the_content', $p->post_content);
            $sub_title = get_post_meta($id, 'sub_title', 1);
            $image = get_post_meta($id, 'e_image');
            $start_date = get_post_meta($id, 'e_date', 1);
            $end_date = get_post_meta($id, 'e_end_date', 1);
            $time = get_post_meta($id, 'time', 1);
            $location = get_post_meta($id, 'location', 1);
            $tickets_cost = get_post_meta($id, 'tickets_cost', 1);
            $website = get_post_meta($id, 'website', 1);
            $tags = get_the_terms($id, 'event_tag');
            $categories = get_the_terms($id, 'event_category');
            $floating_tag = get_post_meta($id, 'floating_tag', 1);

            if (!empty($image[0])) {
                $image = wp_get_attachment_image_src($image[0]->ID, 'event-thumb');
            } else {
                $image = false;
            }

            if (!empty($tags[0])) {
                foreach ($tags as $t) {
                    $tag_arr[] = $t->slug;
                }
            }

            if (!empty($categories[0])) {
                foreach ($categories as $c) {
                    $cat_arr[] = $c->slug;
                }
            }

            $member_profile_image = get_user_profile_image($author);

            $arr[] = [
                'id' => $id,
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'sub_title' => $sub_title,
                'image' => $image,
                'member_profile_image' => $member_profile_image,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'time' => $time,
                'location' => $location,
                'tickets_cost' => $tickets_cost,
                'website' => $website,
                'tags' => $tag_arr,
                'category' => $cat_arr,
                'floating_tag' => $floating_tag,
            ];
        }
    }

    return $arr;
}

function build_events_query_v2($query)
{
    $arr = [];

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $tag_arr = [];
            $cat_arr = [];
            $id = $p->ID;
            $author = $p->post_author;
            $slug = $p->post_name;
            $title = $p->post_title;
            $excerpt = $p->post_excerpt;
            $content = apply_filters('the_content', $p->post_content);
            $sub_title = get_post_meta($id, 'sub_title', 1);
            $image = get_post_meta($id, 'e_image', 1);
            $start_datetime = get_post_meta($id, 'start_datetime', 1);
            $end_datetime = get_post_meta($id, 'end_datetime', 1);
            $location = get_post_meta($id, 'location', 1);
            $tickets_cost = get_post_meta($id, 'tickets_cost', 1);
            $website = get_post_meta($id, 'website', 1);
            $tags = get_the_terms($id, 'event_tag');
            $categories = get_the_terms($id, 'event_category');
            $floating_tag = get_post_meta($id, 'floating_tag', 1);

            if (!empty($image['ID'])) {
                $image = wp_get_attachment_image_src($image['ID'], 'event-thumb');
            } else {
                $image = false;
            }

            if (!empty($tags[0])) {
                foreach ($tags as $t) {
                    $tag_arr[] = $t->slug;
                }
            }

            if (!empty($categories[0])) {
                foreach ($categories as $c) {
                    $cat_arr[] = $c->slug;
                }
            }

            $member_profile_image = get_user_profile_image($author);

            $arr[] = [
                'id' => $id,
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'sub_title' => $sub_title,
                'image' => $image,
                'member_profile_image' => $member_profile_image,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'location' => $location,
                'tickets_cost' => $tickets_cost,
                'website' => $website,
                'tags' => $tag_arr,
                'category' => $cat_arr,
                'floating_tag' => $floating_tag,
            ];
        }
    }

    return $arr;
}

function app_build_events_query($query)
{
    $arr = [];

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $tag_arr = [];
            $cat_arr = [];
            $id = $p->ID;
            $author = $p->post_author;
            $slug = $p->post_name;
            $title = $p->post_title;
            $excerpt = $p->post_excerpt;
            $content = apply_filters('the_content', $p->post_content);
            $sub_title = get_post_meta($id, 'sub_title', 1);
            // $image = get_post_meta($id, 'e_image', 1);
            $start_date = get_post_meta($id, 'e_date', 1);
            $end_date = get_post_meta($id, 'e_end_date', 1);
            $time = get_post_meta($id, 'time', 1);
            $location = get_post_meta($id, 'location', 1);
            $tickets_cost = get_post_meta($id, 'tickets_cost', 1);
            $website = get_post_meta($id, 'website', 1);
            $tags = get_the_terms($id, 'event_tag');
            $categories = get_the_terms($id, 'event_category');
            $floating_tag = get_post_meta($id, 'floating_tag', 1);

            $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'member-thumbnail');

            if (!empty($tags[0])) {
                foreach ($tags as $t) {
                    $tag_arr[] = $t->slug;
                }
            }

            if (!empty($categories[0])) {
                foreach ($categories as $c) {
                    $cat_arr[] = $c->slug;
                }
            }

            $member_profile_image = app_get_user_profile_image($author);

            $arr[] = [
                'id' => $id,
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'sub_title' => $sub_title,
                'image' => $image,
                'member_profile_image' => $member_profile_image,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'time' => $time,
                'location' => $location,
                'tickets_cost' => $tickets_cost,
                'website' => $website,
                'tags' => $tag_arr,
                'category' => $cat_arr,
                'floating_tag' => $floating_tag,
            ];
        }
    }
    return $arr;
}

function app_build_events_query_v2($query)
{
    $arr = [];

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $tag_arr = [];
            $cat_arr = [];
            $id = $p->ID;
            $author = $p->post_author;
            $slug = $p->post_name;
            $title = $p->post_title;
            $excerpt = $p->post_excerpt;
            $content = apply_filters('the_content', $p->post_content);
            $sub_title = get_post_meta($id, 'sub_title', 1);
            // $image = get_post_meta($id, 'e_image', 1);
            $start_datetime = date("c", strtotime(get_post_meta($id, 'start_datetime', 1)));
            $end_datetime = date("c", strtotime(get_post_meta($id, 'end_datetime', 1)));
            $location = get_post_meta($id, 'location', 1);
            $tickets_cost = get_post_meta($id, 'tickets_cost', 1);
            $website = get_post_meta($id, 'website', 1);
            $tags = get_the_terms($id, 'event_tag');
            $categories = get_the_terms($id, 'event_category');
            $floating_tag = get_post_meta($id, 'floating_tag', 1);

            $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'member-thumbnail');

            if (!empty($tags[0])) {
                foreach ($tags as $t) {
                    $tag_arr[] = $t->slug;
                }
            }

            if (!empty($categories[0])) {
                foreach ($categories as $c) {
                    $cat_arr[] = $c->slug;
                }
            }

            $member_profile_image = app_get_user_profile_image($author);

            $arr[] = [
                'id' => $id,
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'sub_title' => $sub_title,
                'image' => $image,
                'member_profile_image' => $member_profile_image,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'time' => $time,
                'location' => $location,
                'tickets_cost' => $tickets_cost,
                'website' => $website,
                'tags' => $tag_arr,
                'category' => $cat_arr,
                'floating_tag' => $floating_tag,
            ];
        }
    }
    return $arr;
}

function get_user_profile_image($user_id)
{
    $query = new WP_Query([
        "post_type" => "user_profile",
        "posts_per_page" => "1",
        'meta_query' => [
            [
                'key' => 'user_id',
                'value' => $user_id,
                'compare' => '=',
            ],
        ],
    ]);

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            // $member_profile_image = get_post_meta($p->ID, 'member-featured-image', 1);
            $member_profile_image = wp_get_attachment_image_src(get_post_thumbnail_id($p->ID), 'event-thumb');
            return $member_profile_image[0];
        }
    }

    wp_reset_query();

    return;
}

function app_get_user_profile_image($user_id)
{
    $query = new WP_Query([
        "post_type" => "user_profile",
        "posts_per_page" => "1",
        'meta_query' => [
            [
                'key' => 'user_id',
                'value' => $user_id,
                'compare' => '=',
            ],
        ],
    ]);

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            // $member_profile_image = get_post_meta($p->ID, 'member-featured-image', 1);
            $member_profile_image = wp_get_attachment_image_src(get_post_thumbnail_id($p->ID), 'member-thumbnail');
            return $member_profile_image[0];
        }
    }

    wp_reset_query();

    return;
}
