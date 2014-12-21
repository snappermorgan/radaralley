<?php

if (!defined('ABSPATH')) exit;

class gdbbMod_Quote {
    private $header = false;

    private $location = 'header';
    private $method = 'quote';

    function __construct($location, $method) {
        $this->location = $location;
        $this->method = $method;

        $this->add_content_filters();
    }

    private function _quote() {
        $id = bbp_get_reply_id();

        $is_reply = true;
        if ($id == 0) {
            $is_reply = false;
            $id = bbp_get_topic_id();
        }

        if (d4p_bbt_o('quote_method', 'tools') == 'html') {
            $url = ''; $ath = '';

            if ($is_reply) {
                $url = bbp_get_reply_url($id);
                $ath = bbp_get_reply_author_display_name($id);
            } else {
                $url = get_permalink($id);
                $ath = bbp_get_topic_author_display_name($id);
            }

            return '<a href="#'.$id.'" bbp-url="'.$url.'" bbp-author="'.$ath.'" class="d4p-bbt-quote-link">'.__("Quote", "gd-bbpress-tools").'</a>';
        } else {
            return '<a href="#'.$id.'" class="d4p-bbt-quote-link">'.__("Quote", "gd-bbpress-tools").'</a>';
        }
    }

    public function add_content_filters() {
        add_filter('bbp_get_reply_content', array(&$this, 'quote_reply_content'), 9);
        add_filter('bbp_get_topic_content', array(&$this, 'quote_topic_content'), 9);

        if ($this->location == 'content' || $this->location == 'both') {
            add_filter('bbp_get_reply_content', array(&$this, 'reply_content'));
            add_filter('bbp_get_topic_content', array(&$this, 'reply_content'));
        }

        if ($this->location == 'header' || $this->location == 'both') {
            add_filter('bbp_get_topic_admin_links', array(&$this, 'reply_links'), 10, 2);
            add_filter('bbp_get_reply_admin_links', array(&$this, 'reply_links'), 10, 2);
            add_action('bbp_theme_after_topic_admin_links', array(&$this, 'after_reply_links'));
            add_action('bbp_theme_after_reply_admin_links', array(&$this, 'after_reply_links'));
        }
    }

    public function remove_content_filters() {
        remove_filter('bbp_get_reply_content', array(&$this, 'quote_reply_content'), 9);
        remove_filter('bbp_get_topic_content', array(&$this, 'quote_topic_content'), 9);

        remove_filter('bbp_get_reply_content', array(&$this, 'reply_content'));
        remove_filter('bbp_get_topic_content', array(&$this, 'reply_content'));

        remove_filter('bbp_get_topic_admin_links', array(&$this, 'reply_links'), 10, 2);
        remove_filter('bbp_get_reply_admin_links', array(&$this, 'reply_links'), 10, 2);
        remove_action('bbp_theme_after_topic_admin_links', array(&$this, 'after_reply_links'));
        remove_action('bbp_theme_after_reply_admin_links', array(&$this, 'after_reply_links'));
    }

    public function quote_reply_content($content) {
        return '<div id="d4p-bbp-quote-'.bbp_get_reply_id().'">'.$content.'</div>';
    }

    public function quote_topic_content($content) {
        return '<div id="d4p-bbp-quote-'.bbp_get_topic_id().'">'.$content.'</div>';
    }

    public function reply_links($content, $args) {
        $this->header = true;

        $before = isset($args['before']) ? $args['before'] : '<span class="bbp-admin-links">';
        $after = isset($args['after']) ? $args['after'] : '</span>';
        $sep = isset($args['sep']) ? $args['sep'] : ' | ';

        $old_links = trim(substr($content, strlen($before), strlen($content) - strlen($before) - strlen($after)));

        return $before.$old_links.($old_links == '' ? '' : $sep).$this->_quote().$after;
    }

    public function after_reply_links() {
        if (!$this->header) {
            echo '<span class="bbp-admin-links">'.$this->_quote().'</span>';
        }

        $this->header = false;
    }

    public function reply_content($content) {
        return $content.'<div class="d4p-bbt-quote-block">'.$this->_quote().'</div>';
    }
}

?>