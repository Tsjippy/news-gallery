<?php

namespace TSJIPPY\NEWSGALLERY;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', __NAMESPACE__ . '\initBlocks');
function initBlocks()
{
    register_block_type(
        __DIR__ . '/news-gallery/build',
        array(
            'render_callback' => __NAMESPACE__.'\showNewsGallery',
        )
    );
}

/**
 * Shows the news Gallery
 * 
 * @param   array   $attributes Block Attributes
 * 
 * @return  string              Html
 */
function showNewsGallery($attributes)
{
    $postTypes              = $attributes['postTypes'];
    $maxNewsAge             = $attributes['age'];
    $args                   = [
        'ignore_sticky_posts' => true,
        'post_type'           => $postTypes,
        'post_status'         => 'publish',
        'date_query'          => [
            [
                'after' => [
                    'year'  => gmdate('Y', strtotime("-$maxNewsAge days")),
                    'month' => gmdate('m', strtotime("-$maxNewsAge days")),
                    'day'   => gmdate('d', strtotime("-$maxNewsAge days")),
                ]
            ]
        ],
        //exclude private events and user pages
        'meta_query'         => [
            'relation' => 'AND',
            array(
                'key'     => 'tsjippy_only_for',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'        => 'user_id',
                'compare'    => 'NOT EXISTS'
            ),
            array(
                'key'        => 'tsjippy_skipgallery',
                'compare'    => 'NOT EXISTS'
            )
        ]
    ];

    //Only show public content if not logged in..
    if (!is_user_logged_in()) {
        //Only get news wih the public category
        $blogCategories = [get_cat_ID('Public')];

        $args['tax_query'] = array(
            array(
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $blogCategories,
            ),
        );

        //Do not show password protected news
        $args['has_password'] = false;
    }

    /**
     * Filters the query args
     * 
     * @param   array   $args 
     */
    $args = apply_filters('tsjippy-news-gallery-query', $args);

    if ($attributes['gradient']) {
        $style    = "background: linear-gradient(-90deg, transparent 0 0.1%, {$attributes['color']}, transparent 99.9% 100%);";
    } elseif(!empty($attributes['color'])) {
        $style    = "background-color: {$attributes['color']};";
    }else{
        $style    = '';
    }

    //Get all the posts using the previously defined arguments
    $loop = new \WP_Query($args);

    ob_start();

    if (! $loop->have_posts()) {
        //Show message if there is no news
        ?>
        <article id="news" style='<?php echo esc_attr($style); ?>'>
            <div id="rowwrap">
                <h2 id="news-title">
                    <?php echo wp_kses_post($attributes['title']);?>
                </h2>
                <div class="row">
                    <article class="news-article">
                        <div class="card card-plain card-blog">
                            <div class="content">
                                <h4 class="card-title entry-title">
                                    <p>There is currently no news</p>
                                </h4>
                            </div>
                        </div>
                    </article>
                </div>
                <div id="newslinkdiv"></div>
            </div>
        </article>
        <?php

        return ob_get_clean();
    }

    $allowedHtml = array(
        'br'     => array(),
        'em'     => array(),
        'strong' => array(),
        'i'      => array(),
        'class'  => array(),
        'span'   => array(),
    );

    $i = 1;
    ?>
    <article id="news" style='<?php echo esc_attr($style); ?>'>
        <div id="rowwrap">
            <h2 id="news-title">
                <?php echo wp_kses_post($attributes['title']);?>
            </h2>
            <div class="row">
                <?php
                while ($loop->have_posts()) {
                    wp_link_pages();
                    $loop->the_post();

                    ?>
                    <article class="news-article">
                        <div class="card card-plain card-blog">
                            <div class="card-image">
                                <?php
                                if (has_post_thumbnail()) {
                                    ?>
                                    <a href="<?php echo esc_url(get_permalink());?>" style="background-image: url( <?php echo esc_url( get_the_post_thumbnail_url() ); ?> ');"></a>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="content">
                                <h4 class="card-title entry-title">
                                    <a class="blog-item-title-link" href="<?php echo esc_url(get_permalink()); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
                                        <?php echo wp_kses(force_balance_tags(get_the_title()), $allowedHtml); ?>
                                    </a>
                                </h4>
                                <div class="card-description">
                                    <?php echo force_balance_tags(wp_kses_post(get_the_excerpt())); ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php
                    $i++;
                }
                ?>
            </div>
            <div id="newslinkdiv">
                <p>
                    <a name="newslink" id="newslink" href="'.SITEURL.'/news/">
                        Read all news items →
                    </a>
                </p>
            </div>
        </div>
    </article>
    <?php

    wp_reset_postdata();

    return ob_get_clean();
}
