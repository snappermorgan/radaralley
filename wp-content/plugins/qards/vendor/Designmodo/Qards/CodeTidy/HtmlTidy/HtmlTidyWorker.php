<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\CodeTidy\HtmlTidy;

/**
 * Require Lib with tiding and make tiding with predefined config
 *
 */
class HtmlTidyWorker {
    protected static $_instance;

    protected $dryHtml;
    protected $configArray;

    private function __construct(){
        // Connect file with functions
        require_once(__DIR__ . '/lib/htmLawed.php');
        $this->configArray = array('comment'=>3,
                                    'tidy'=>'2s0n',
                                    'safe'=>0,
                                    'abs_url'=>0,
                                    'balance'=>0,
                                    'css_expression'=>1,
                                    'deny_attribute'=>0,
                                    'direct_nest_list'=>1,
                                    'keep_bad'=>1,
                                    // Add new or custom element in list below
                                    'elements'=>'a, abbr, acronym, address, applet, area, article, aside, audio, b, bdi, bdo, big, blockquote, br, button, canvas, caption, center, cite, code, col, colgroup, command, data, datalist, dd, del, details, dfn, dir, div, dl, dt, em, embed, fieldset, figcaption, figure, font, footer, form, h1, h2, h3, h4, h5, h6, header, hgroup, hr, i, iframe, img, input, ins, isindex, kbd, keygen, label, legend, link, main, map, mark, menu, meta, meter, nav, noscript, object, ol, optgroup, option, output, p, param, pre, progress, q, rb, rbc, rp, rt, rtc, ruby, s, samp, script, section, select, small, source, span, strike, strong, style, sub, summary, sup, table, tbody, td, textarea, tfoot, th, thead, time, tr, track, tt, u, ul, li, var, video, wbr, svg, circle , g, path, clippath, clipPath',
                                    'make_tag_strict'=>0,
                                    'no_deprecated_attr'=>0,
            );
    }
    private function __clone(){

    }
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function prepareHtml($dryHtml) {
        // SVG support workaround http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm#s2.8
        $matches = array();
        preg_match('/(\<svg.*\>.*\<\/svg\>)/ism', $dryHtml, $matches);

        if (is_array($matches)) {
            array_shift($matches);
        } else {
            $matches = array();
        }

        foreach ($matches as $index => $svg) {
            $dryHtml = str_replace($svg, 'swgworkaround_' . $index . '_swgworkaround', $dryHtml);
        }
        $dryHtml = htmLawed($dryHtml, $this->configArray);
        foreach ($matches as $index => $svg) {
            $dryHtml = str_replace('swgworkaround_' . $index . '_swgworkaround', $svg, $dryHtml);
        }
        return $dryHtml;
    }
}
