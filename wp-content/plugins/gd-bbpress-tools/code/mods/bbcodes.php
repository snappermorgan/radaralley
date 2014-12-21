<?php

if (!defined('ABSPATH')) exit;

class gdbbMod_Shortcodes {
    private $advanced = array('url', 'google', 'youtube', 'note');
    private $shortcodes = array();

    private $removal = 'info';
    private $notice = true;
    private $restricted = false;
    private $bbpress_only = false;

    private $list_deactivated = array();

    function __construct($bbpress_only = false, $restricted = false, $removal = 'info', $deactivated = array(), $notice = true) {
        $this->bbpress_only = $bbpress_only;
        $this->restricted = $restricted;
        $this->removal = $removal;
        $this->list_deactivated = (array)$deactivated;
        $this->notice = $notice;

        $this->_init();

        $list = array_keys($this->shortcodes);
        foreach ($list as $shortcode) {
            $deactivate = in_array($shortcode, $this->list_deactivated);

            if (!$deactivate) {
                add_shortcode($shortcode, array(&$this, 'shortcode_'.$shortcode));
                add_shortcode(strtoupper($shortcode), array(&$this, 'shortcode_'.$shortcode));
            }
        }

        if ($this->notice) {
            add_action('bbp_theme_before_reply_form_notices', array(&$this, 'show_notice'));
            add_action('bbp_theme_before_topic_form_notices', array(&$this, 'show_notice'));
        }

        if ($this->restricted) {
            add_filter('bbp_new_reply_pre_insert', array(&$this, 'restrict_content'));
            add_filter('bbp_new_topic_pre_insert', array(&$this, 'restrict_content'));
        }

        add_filter('bbp_get_reply_content', 'do_shortcode');
        add_filter('bbp_get_topic_content', 'do_shortcode');
    }

    private function _init() {
        $this->shortcodes = array(
            'b' => array(
                'name' => __("Bold", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'i' => array(
                'name' => __("Italic", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'u' => array(
                'name' => __("Underline", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0),
                'args' => array('style' => 'text-decoration: underline;')
            ),
            's' => array(
                'name' => __("Strikethrough", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'center' => array(
                'name' => __("Align Center", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0),
                'args' => array('style' => 'text-align: center;')
            ),
            'right' => array(
                'name' => __("Align Right", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0),
                'args' => array('style' => 'text-align: right;')
            ),
            'left' => array(
                'name' => __("Align Left", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0),
                'args' => array('style' => 'text-align: left;')
            ),
            'justify' => array(
                'name' => __("Align Justify", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0),
                'args' => array('style' => 'text-align: justify;')
            ),
            'sub' => array(
                'name' => __("Subscript", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'sup' => array(
                'name' => __("Superscript", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'pre' => array(
                'name' => __("Preformatted", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 1)
            ),
            'reverse' => array(
                'name' => __("Reverse", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0),
                'args' => array('dir' => 'rtl')
            ),
            'list' => array(
                'name' => __("List: Ordered", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'ol' => array(
                'name' => __("List: Ordered", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'ul' => array(
                'name' => __("List: Unordered", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'li' => array(
                'name' => __("List: Item", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'blockquote' => array(
                'name' => __("Blockquote", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'area' => array(
                'name' => __("Area", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'border' => array(
                'name' => __("Border", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'div' => array(
                'name' => __("Block", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'hr' => array(
                'name' => __("Horizontal Line", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '')
            ),
            'size' => array(
                'name' => __("Font Size", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'color' => array(
                'name' => __("Font Color", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 0)
            ),
            'quote' => array(
                'name' => __("Quote", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'raw' => 1)
            ),
            'url' => array(
                'name' => __("URL", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'target' => '_blank', 'rel' => '', 'raw' => 0)
            ),
            'google' => array(
                'name' => __("Google Search", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'target' => '_blank', 'rel' => '', 'raw' => 0)
            ),
            'img' => array(
                'name' => __("Image", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'alt' => '', 'title' => '', 'width' => '', 'height' => '')
            ),
            'youtube' => array(
                'name' => __("YouTube Video", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'width' => '', 'height' => '')
            ),
            'vimeo' => array(
                'name' => __("Vimeo Video", "gd-bbpress-tools"),
                'atts' => array('style' => '', 'class' => '', 'width' => '', 'height' => '')
            ),
            'note' => array(
                'name' => __("Note", "gd-bbpress-tools"),
                'atts' => array('raw' => 0)
            )
        );
    }

    private function _scope() {
        global $post;

        if ($this->bbpress_only) {
            return in_array($post->post_type, array('forum', 'topic', 'reply'));
        } else {
            return true;
        }
    }

    private function _atts($code, $atts = array()) {
        if (isset($atts[0])) {
            $atts[$code] = substr($atts[0], 1);
            unset($atts[0]);
        }

        $default = $this->shortcodes[$code]['atts'];
        $default[$code] = '';
        $atts = shortcode_atts($default, $atts);

        return $atts;
    }

    private function _content($content, $raw = false) {
        if ($raw) {
            return $content;
        } else {
            return do_shortcode($content);
        }
    }

    private function _tag($tag, $name, $content = null, $atts = array(), $args = array()) {
        $attributes = array('class' => 'd4pbbc-'.$name);

        foreach ($atts as $key => $value) {
            if (isset($attributes[$key]) && ($key == 'class' || $key == 'style')) {
                $attributes[$key].= ' '.$value;
            } else {
                $attributes[$key] = $value;
            }
        }

        foreach ($args as $key => $value) {
            if (isset($attributes[$key]) && ($key == 'class' || $key == 'style')) {
                $attributes[$key].= ' '.$value;
            } else {
                $attributes[$key] = $value;
            }
        }

        $render = '<'.$tag;

        foreach ($attributes as $key => $value) {
            if (trim($value) != '' && $key != 'raw' && $key != $name) {
                $render.= ' '.$key.'="'.trim($value).'"';
            }
        }

        if (is_null($content)) {
            $render.= ' />';
        } else {
            $render.= '>';
            $render.= $this->_content($content, $atts['raw'] == 1);
            $render.= '</'.$tag.'>';
        }

        return $render;
    }

    private function _simple($code, $tag, $name, $atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts($code, $atts);
        $args = isset($this->shortcodes[$code]['args']) ? $this->shortcodes[$code]['args'] : array();

        return $this->_tag($tag, $name, $content, $atts, $args);
    }

    private function _regex($list) {
	$tagregexp = join('|', $list);

	return    '\\['
		. '(\\[?)'
		. "($tagregexp)"
		. '\\b'
		. '('
		.     '[^\\]\\/]*'
		.     '(?:'
		.         '\\/(?!\\])'
		.         '[^\\]\\/]*'
		.     ')*?'
		. ')'
		. '(?:'
		.     '(\\/)'
		.     '\\]'
		. '|'
		.     '\\]'
		.     '(?:'
		.         '('
		.             '[^\\[]*+'
		.             '(?:'
		.                 '\\[(?!\\/\\2\\])'
		.                 '[^\\[]*+'
		.             ')*+'
		.         ')'
		.         '\\[\\/\\2\\]'
		.     ')?'
		. ')'
		. '(\\]?)';

    }

    private function _strip($m) {
        if ($this->removal == 'info') {
            return '[blockquote]'.__("BBCode you used is not allowed.", "gd-bbpress-tools").'[/blockquote]';
        } else {
            return '';
        }
    }

    public function strip_advanced($content) {
        $pattern = $this->_regex(apply_filters('d4p_bbpresstools_bbcode_advanced_list', $this->advanced));
        return preg_replace_callback("/$pattern/s", array(&$this, '_strip'), $content);
    }

    public function restrict_content($reply_data) {
        $reply_data['post_content'] = $this->strip_advanced($reply_data['post_content']);
        return $reply_data;
    }

    public function show_notice() {
        echo '<div class="bbp-template-notice"><p>';
        echo __("You can use BBCodes to format your content.", "gd-bbpress-tools");
        if ($this->restricted) {
            echo '<br/>'.__("Your account can't use Advanced BBCodes, they will be stripped before saving.", "gd-bbpress-tools");
        }
        do_action('d4p_bbpresstools_bbcode_notice');
        echo '</p></div>';
    }

    public function shortcode_b($atts, $content = null) {
        return $this->_simple('b', 'strong', 'bold', $atts, $content);
    }

    public function shortcode_i($atts, $content = null) {
        return $this->_simple('i', 'em', 'italic', $atts, $content);
    }

    public function shortcode_u($atts, $content = null) {
        return $this->_simple('u', 'span', 'underline', $atts, $content);
    }

    public function shortcode_s($atts, $content = null) {
        return $this->_simple('s', 'del', 'strikethrough', $atts, $content);
    }

    public function shortcode_right($atts, $content = null) {
        return $this->_simple('right', 'div', 'align-right', $atts, $content);
    }

    public function shortcode_center($atts, $content = null) {
        return $this->_simple('center', 'div', 'align-center', $atts, $content);
    }

    public function shortcode_left($atts, $content = null) {
        return $this->_simple('left', 'div', 'align-left', $atts, $content);
    }

    public function shortcode_justify($atts, $content = null) {
        return $this->_simple('justify', 'div', 'align-justify', $atts, $content);
    }

    public function shortcode_sub($atts, $content = null) {
        return $this->_simple('sub', 'sub', 'sub', $atts, $content);
    }

    public function shortcode_sup($atts, $content = null) {
        return $this->_simple('sup', 'sup', 'sup', $atts, $content);
    }

    public function shortcode_pre($atts, $content = null) {
        return $this->_simple('pre', 'pre', 'pre', $atts, $content);
    }

    public function shortcode_border($atts, $content = null) {
        return $this->_simple('border', 'fieldset', 'border', $atts, $content);
    }

    public function shortcode_reverse($atts, $content = null) {
        return $this->_simple('reverse', 'bdo', 'reverse', $atts, $content);
    }

    public function shortcode_blockquote($atts, $content = null) {
        return $this->_simple('blockquote', 'blockquote', 'blockquote', $atts, $content);
    }

    public function shortcode_list($atts, $content = null) {
        return $this->_simple('list', 'ol', 'ol', $atts, $content);
    }

    public function shortcode_ol($atts, $content = null) {
        return $this->_simple('ol', 'ol', 'ol', $atts, $content);
    }

    public function shortcode_ul($atts, $content = null) {
        return $this->_simple('ul', 'ul', 'ul', $atts, $content);
    }

    public function shortcode_li($atts, $content = null) {
        return $this->_simple('li', 'li', 'li', $atts, $content);
    }

    public function shortcode_div($atts, $content = null) {
        return $this->_simple('div', 'div', 'div', $atts, $content);
    }

    public function shortcode_size($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('size', $atts);
        $args = isset($this->shortcodes['size']['args']) ? $this->shortcodes['size']['args'] : array();

        if ($atts['size'] != '') {
            $args['style'] = 'font-size: '.$atts['size'];

            if (is_numeric($atts['size'])) {
                $args['style'].= 'px';
            }

            unset($atts['size']);
        }

        return $this->_tag('span', 'font-size', $content, $atts, $args);
    }

    public function shortcode_color($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('color', $atts);
        $args = isset($this->shortcodes['color']['args']) ? $this->shortcodes['color']['args'] : array();

        if ($atts['color'] != '') {
            $args['style'] = 'color: '.$atts['color'];

            unset($atts['color']);
        }

        return $this->_tag('span', 'font-color', $content, $atts, $args);
    }

    public function shortcode_area($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('area', $atts);
        $args = isset($this->shortcodes['area']['args']) ? $this->shortcodes['area']['args'] : array();

        if ($atts['area'] != '') {
            $content = '<legend>'.$atts['area'].'</legend>'.$content;
        }

        return $this->_tag('fieldset', 'area', $content, $atts, $args);
    }

    public function shortcode_hr($atts) {
        $atts = $this->_atts('hr', $atts);

        return $this->_tag('hr', 'hr', null, $atts);
    }

    public function shortcode_quote($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('quote', $atts);
        $args = isset($this->shortcodes['quote']['args']) ? $this->shortcodes['quote']['args'] : array();

        $title = '';
        if ($atts['quote'] != '') {
            $id = intval($atts['quote']);
            $url = bbp_get_reply_url($id);
            $ath = bbp_get_reply_author_display_name($id);
            $title = '<div class="d4p-bbt-quote-title"><a href="'.$url.'">'.$ath.' '.__("wrote", "gd-bbpress-tools").':</a></div>';
        }

        return $this->_tag('blockquote', 'quote', $title.$content, $atts, $args);
    }

    public function shortcode_url($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('url', $atts);
        $args = isset($this->shortcodes['url']['args']) ? $this->shortcodes['url']['args'] : array();

        if ($atts['url'] != '') {
            $args['href'] = str_replace(array('"', "'"), '', $atts['url']);
        } else {
            $args['href'] = $content;
        }

        return $this->_tag('a', 'url', $content, $atts, $args);
    }

    public function shortcode_img($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('img', $atts);
        $args = isset($this->shortcodes['img']['args']) ? $this->shortcodes['img']['args'] : array();
        $args['src'] = $content;

        if ($atts['img'] != '') {
            $parts = explode("x", $atts['img'], 2);

            if (count($parts) == 2) {
                $args['width'] = intval($parts[0]);
                $args['height'] = intval($parts[1]);
            }
        }

        return $this->_tag('img', 'image', null, $atts, $args);
    }

    public function shortcode_google($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('google', $atts);
        $args = isset($this->shortcodes['google']['args']) ? $this->shortcodes['google']['args'] : array();

        $protocol = is_ssl() ? 'https' : 'http';
        $link = $protocol.'://www.google.';

        if ($atts['google'] != '') {
            $link.= $atts['google'];
        } else {
            $link.= 'com';
        }

        $link.= '/search?q='.urlencode($content);

        $args['href'] = $link;

        return $this->_tag('a', 'google', $content, $atts, $args);
    }

    public function shortcode_youtube($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('youtube', $atts);

        if (strpos($content, 'youtube.com') === false && strpos($content, 'youtu.be') === false) {
            $protocol = is_ssl() ? 'https' : 'http';
            $url = $protocol.'://www.youtube.com/watch?v='.$content;
        } else {
            $url = $content;

            if (is_ssl() && substr($url, 0, 5) != 'https') {
                $url = 'https'.substr($url, 4);
            }
        }

        if ($atts['youtube'] != '') {
            $parts = explode('x', $atts['youtube'], 2);

            if (count($parts) == 2) {
                $args['width'] = intval($parts[0]);
                $args['height'] = intval($parts[1]);
            }
        }

        $data = array();
        if ($atts['width'] > 0) $data['width'] = $atts['width'];
        if ($atts['height'] > 0) $data['height'] = $atts['height'];

        global $wp_embed;
        return $wp_embed->shortcode($data, $url);
    }

    public function shortcode_vimeo($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        $atts = $this->_atts('vimeo', $atts);

        if (strpos($content, 'vimeo.com') === false) {
            $protocol = is_ssl() ? 'https' : 'http';
            $url = $protocol.'://www.vimeo.com/'.$content;
        } else {
            $url = $content;

            if (is_ssl() && substr($url, 0, 5) != 'https') {
                $url = 'https'.substr($url, 4);
            }
        }

        if ($atts['vimeo'] != '') {
            $parts = explode('x', $atts['vimeo'], 2);

            if (count($parts) == 2) {
                $args['width'] = intval($parts[0]);
                $args['height'] = intval($parts[1]);
            }
        }

        $data = array();
        if ($atts['width'] > 0) $data['width'] = $atts['width'];
        if ($atts['height'] > 0) $data['height'] = $atts['height'];

        global $wp_embed;
        return $wp_embed->shortcode($data, $url);
    }

    public function shortcode_note($atts, $content = null) {
        if (is_null($content)) return '';
        if (!$this->_scope()) return $content;

        return '<!-- '.$this->_content($content).' -->';
    }
}

?>