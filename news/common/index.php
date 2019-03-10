<?php
function build_news_query($query)
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
            $pub_date = get_post_meta($id, 'publication_date', 1);
            $tags = get_the_terms($id, 'news_tag');
            $categories = get_the_terms($id, 'news_category');

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
                'sub_title' => $sub_title,
                'pub_date' => $pub_date,
                'tags' => $tag_arr,
                'category' => $cat_arr,
            ];
        }
    }

    return $arr;
}
