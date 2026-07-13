<?php

namespace TSJIPPY\NEWSGALLERY;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('tsjippy-frontend-content-post-after-content', __NAMESPACE__ . '\afterPostContent', 20);
/**
 * Add the comments section to the frontend post content
 * 
 * @param   object    $object    The FrontEndContent instance
 */
function afterPostContent($object)
{
    ?>
    <tbody id="nonews" class="frontend-form expand-wrapper">
        <tr>
            <td>
                <h4>
                    News Gallery
                </h4>
            </td>

            <td>
                <button class="button small expand" type='button'>
                    &#9660;
                </button>
            </td>
        </tr>
        <tr>
            <td class='hidden expandable' collspan=2>
                <input
                    type='checkbox'
                    name='skipgallery'
                    value='skipgallery'
                    <?php if (get_post_meta($object->postId, 'tsjippy_skipgallery', true)) echo 'checked'; ?>>
                Do not add this <?php echo esc_attr($object->post->post_type ?? ''); ?> to the news gallery
            </td>
        </tr>
    </tbody>
    <?php
}