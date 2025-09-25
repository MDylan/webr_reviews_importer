<?php
/*
Plugin Name: Reviews Importer
Description: Google és Facebook értékelések importálása egyedi post type-ba.
Version: 0.5
Author: Molnár Dávid
*/

if (!defined('ABSPATH')) exit;

add_action(
    'init',
    function () {
        if (function_exists('get_field')) {
            define('WEBR_GOOGLE_PLACE_API_KEY', get_field('google_places_api_key', 'option'));
            define('WEBR_GOOGLE_PLACE_ID', get_field('google_place_id', 'option'));
        }
    });

// Google értékelések importálása
// Google értékelések importálása
function import_google_reviews()
{
    if (empty(WEBR_GOOGLE_PLACE_API_KEY) || empty(WEBR_GOOGLE_PLACE_ID)) {
        error_log("Nincs google place key #1");
        return;
    }

    $languages = [
        'en',
        'hu'
    ];

    foreach($languages as $language) {
        webr_get_google_reviews($language);
    }  
}

function webr_get_google_reviews($language) {


    if (empty(WEBR_GOOGLE_PLACE_API_KEY) || empty(WEBR_GOOGLE_PLACE_ID)) {
        error_log("Nincs google place key #2");
        return;
    }

    //error_log("===== REVIEW ". $language ."=====");

    $headers = [
        'Content-Type' => 'application/json',
        'Referer' => get_site_url()
    ];


    $fields = array('formatted_address', 'icon', 'id', 'name', 'rating', 'reviews', 'url', 'user_ratings_total', 'vicinity');
    // $language = "en";

    $url = 'https://maps.googleapis.com/maps/api/place/details/json'
        . '?placeid=' . rawurlencode(WEBR_GOOGLE_PLACE_ID)
        . '&key=' . rawurlencode(WEBR_GOOGLE_PLACE_API_KEY)
        . '&fields=' . rawurlencode(implode(',', $fields))
        . '&reviews_sort=newest' // Legújabb értékelések elől
        . '&reviews_no_translations=false' //. rawurlencode((!$translate) ? 'true' : 'false')
        . (($language != NULL) ? '&language=' . rawurlencode($language) : '');
    //error_log("URL: " . $url);
    if (version_compare(PHP_VERSION, '8.1') >= 0) {
        $data_string = wp_remote_retrieve_body(@wp_remote_get($url, $headers));
    } else {
        $data_string = wp_remote_retrieve_body(wp_remote_get($url, $headers));
    }

    $data_array = ($data_string != NULL) ? json_decode($data_string, TRUE) : NULL;
   // error_log('Google API válasz: ' . print_r($data_array, true));

    if (isset($data_array['error_message'])) {
        error_log('Google API hiba: ' . $data_array['error_message']);
        return;
    }

    if (isset($data_array['result']['reviews'])) {
        $data = $data_array['result'];
        foreach ($data['reviews'] as $key => $value) {
            $review_id = md5(trim($value['author_url']).trim($value['time']));
            $data['reviews'][$key]['review_id'] = $review_id;
            $data['reviews'][$key]['text'] = trim($value['text']);
            $data['reviews'][$key]['review_time'] = $value['time'];
            $data['reviews'][$key]['review_timestamp'] = date("Y-m-d H:i:s", $value['time']);
            $data['reviews'][$key]['review_timestamp_gmt'] = gmdate("Y-m-d H:i:s", $value['time']);
            
            $data['reviews'][$key]['rating'] = $value['rating'];
        }
    } else {
        error_log('Google API hiba: Nincs értékelés.');
        return;
    }

    //módosítja a két alap mezőt

    if(isset($data_array['result']['rating']) && function_exists('update_field')) {
        $new_rating = floatval($data_array['result']['rating']);

        update_field('google_rating', $new_rating, 'option');
    }

    if (isset($data_array['result']['user_ratings_total']) && function_exists('update_field')) {
        $new_rating = floatval($data_array['result']['user_ratings_total']);

        update_field('user_ratings_total', $new_rating, 'option');
    }

    //return;

    if (!empty($data['reviews'])) {
        foreach ($data['reviews'] as $review) {
            $existing = get_posts([
                'post_type' => 'reviews',
                'meta_query' => [[
                    'key'   => 'review_id',
                    'value' => $review['review_id'],
                ]],
                'fields' => 'ids',
                'numberposts' => 1
            ]);

            if (!$existing) {
                $post_status = ($review['rating'] >= 4) ? 'publish' : 'draft';

                $post_id = wp_insert_post([
                    'post_type'    => 'reviews',
                    'post_title'   => $review['author_name'],
                    'post_status'  => $post_status,
                    'post_date' => $review['review_timestamp'],
                    'post_date_gmt' => $review['review_timestamp_gmt']
                ]);
            } else {
                //meglévő frissítése
                $post_id =  $existing[0];
            }
            if ($post_id) {
                update_field('review_number', $review['rating'], $post_id);
                update_field('review_timestamp', $review['review_timestamp'], $post_id);
                update_field('review_source', 'Google', $post_id);
                update_field('review_id', $review['review_id'], $post_id);
                update_field('review_' . $language, $review['text'], $post_id);
            }
        }
    }
}

// Facebook értékelések importálása
function import_facebook_reviews()
{
    $access_token = get_field('facebook_graph_api_key', 'option');
    $page_id = get_field('facebook_page_id', 'option');

    if (!$access_token || !$page_id) return;

    $url = "https://graph.facebook.com/{$page_id}/ratings?access_token={$access_token}";

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        error_log('Facebook API hiba: ' . $response->get_error_message());
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($data['data'])) {
        foreach ($data['data'] as $review) {
            $review_id = strtotime($review['created_time']);

            $existing = get_posts([
                'post_type' => 'reviews',
                'meta_query' => [[
                    'key'   => 'review_id',
                    'value' => $review_id,
                ]],
            ]);

            if (!$existing) {
                $post_status = ($review['rating'] >= 4) ? 'publish' : 'draft';

                $post_id = wp_insert_post([
                    'post_type'    => 'reviews',
                    'post_title'   => $review['reviewer']['name'],
                    'post_status'  => $post_status,
                    'post_content' => $review['review_text'], // Értékelés tartalma a leírás mezőbe
                ]);

                if ($post_id) {
                    update_field('review_number', $review['rating'], $post_id);
                    update_field('review_timestamp', $review_id, $post_id);
                    update_field('review_source', 'Facebook', $post_id);
                    update_field('review_id', $review_id, $post_id);
                }
            }
        }
    }
}

// Időzített esemény hozzáadása
if (!wp_next_scheduled('import_reviews_event')) {
    wp_schedule_event(time(), 'hourly', 'import_reviews_event');
}

add_action('import_reviews_event', 'import_google_reviews');
add_action('import_reviews_event', 'import_facebook_reviews');
