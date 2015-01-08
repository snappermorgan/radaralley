<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\CodeTidy\CssTidy;

/**
 * Require Lib with tiding and make tiding with predefined config
 *
 */
class CssTidyWorker {
    protected static $_instance;

    protected $dryCss;
    protected $configArray = array();
    protected $cssWorkerObj;

    private function __construct(){
        // Connect file with functions
        require_once(__DIR__ . '/lib/class.csstidy.php');
        $this->configArray = array('preserve_css'=>TRUE,
                                   'remove_bslash'=>TRUE,
                                   'compress_colors'=>FALSE,
//                                    'lowercase_s'=>TRUE,
                                    'timestamp'=>FALSE,
                                    'optimise_shorthands' => '0', //[1|2|0]
                                    'remove_last_;'=>TRUE,
                                    'sort_properties'=>FALSE,
                                    'sort_selectors'=>FALSE,
                                    'merge_selectors'=> '0', //[2|1|0]
                                    'compress_font-weight'=>FALSE,
                                    'allow_html_in_templates'=>FALSE,
                                    'silent'=>TRUE,
                                    'case_properties'=>0, //[0|1|2]
//                                    'template'=>'low', //[default | filename | low | high | highest]
//                                    'output_filename'=>FALSE,
                                    );
        $this->cssWorkerObj = new \csstidy();
        $this->setWorkerConfig();
        $this->cssWorkerObj->load_template(__DIR__ . '/lib/template1_1.tpl', true);
    }
    private function setWorkerConfig() {
        foreach($this->configArray as $key => $val){
            $this->cssWorkerObj->set_cfg($key,$val);
        }
    }
    private function __clone(){

    }
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function prepareCss($dryCss) {
//        return $dryCss;
        $this->cssWorkerObj->parse($dryCss);
        return $this->cssWorkerObj->print->plain();
    }
}
