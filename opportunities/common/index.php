<?php
function build_opportunities_query($query)
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
            $deadline = get_post_meta($id, 'deadline', 1);
            $tags = get_the_terms($id, 'opportunity_tag');
            $categories = get_the_terms($id, 'opportunity_category');

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

            $arr[] = [
                'id' => $id,
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'deadline' => $deadline,
                'tags' => $tag_arr,
                'category' => $cat_arr,
            ];
        }
    }

    return $arr;
}
