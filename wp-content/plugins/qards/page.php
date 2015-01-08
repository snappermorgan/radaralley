<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Designmodo\Qards\Utility\Context;
use Designmodo\Qards\Page\Layout\Layout;
use Designmodo\Qards\Http\Http;
$post = new TimberPost();

Context::getInstance()->set('post', $post);

$pageLayout = get_post_meta($post->ID, '_qards_page_layout', true);
Context::getInstance()->set('current_layout_id', $pageLayout);
Context::getInstance()->set('current_post_id', $post->ID);

$layout = new Layout(Context::getInstance()->get('current_layout_id'));
Context::getInstance()->set('current_component_ids', $layout->getComponents());
if (Context::getInstance()->get('edit_mode')) {
    Context::getInstance()->set('wp_title', $post->title());
}
Context::getInstance()->set('wp_blog_name', get_bloginfo('name'));
echo $layout->render(Http::CONTENT_TYPE_HTML);
