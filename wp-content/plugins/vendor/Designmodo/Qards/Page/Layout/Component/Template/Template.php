<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\Page\Layout\Component\Template;

use Designmodo\Qards\Utility\Db;
/**
 * Template implements the HTML\CSS etc resources providing for components of layout.
 */
class Template
{

    const RESOURCE_TPL = 'tpl';

    const RESOURCE_CSS = 'css';

    const RESOURCE_JSON = 'json';

    const RESOURCE_JS = 'js';

    /**
     * Template ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Constructor
     *
     * @param string $id
     * @return void
     */
    public function __construct($id)
    {
        if (!empty($id)) {
            $this->id = $id;
        } else {
            throw new \Exception('Could not create Template object with empty ID.', 348734);
        }
    }

    /**
     * Get path of resources
     *
     * @param string $resourceType
     * @return string
     */
    public function getResourcePath($resourceType)
    {
        if (! preg_match(DM_TEMPLATE_ID_REGEX, $this->getId())) {
            throw new \Exception('Invalid format of the template ID "' . $this->getId() . '".', 23874);
        }
        $segments = explode(DM_TEMPLATE_DELIMETER, $this->getId());

        switch ($resourceType) {
            case self::RESOURCE_TPL:
                $filePath = 'ui-kit-' . $segments[0] . DM_DS . 'components' . DM_DS . $segments[1] . DM_DS . $segments[1] . DM_TPL_EXT;
                break;
            case self::RESOURCE_CSS:
                $fname = $segments[0] . '.css';
//                 $matches = null;
//                 if (preg_match('/^([a-z]+)(\d+)$/i', $segments[1], $matches)) {
//                     $fname = $matches[1] . '-' . $matches[2] . '-style.css';
//                 }
                $filePath = 'ui-kit-' . $segments[0] . DM_DS . 'css' . DM_DS . $fname;
                break;
            case self::RESOURCE_JSON:
                $filePath = 'ui-kit-' . $segments[0] . DM_DS . 'components' . DM_DS . $segments[1] . DM_DS . $segments[1] . '.json';
                break;
            case self::RESOURCE_JS:
                $filePath = 'ui-kit-' . $segments[0] . DM_DS . 'js' . DM_DS . $segments[0] . '.js';
                break;
        }
        return $filePath;
    }

    /**
     * Get default data model
     *
     * @return mixed
     */
    public function getDefaultModel()
    {
        if (file_exists($jsonFile = DM_TPL_PATH . DM_DS . $this->getResourcePath(self::RESOURCE_JSON))) {
            $data = file_get_contents($jsonFile);
        } else {
            $data = Db::getColumn(
                'SELECT `data` FROM `' . Db::getPluginTableName(Db::TABLE_RESOURCE) . '` WHERE template_id = %s AND type = %s LIMIT 1',
                array(
                    $this->getId(),
                    self::RESOURCE_JSON
                )
            );
        }
        return json_decode($data, true);
    }

    /**
     * Is template custom
     *
     * @return mixed
     */
    public function isCustom()
    {
        $segments = explode(DM_TEMPLATE_DELIMETER, $this->getId());

        return (count($segments) < 2 ? false : $segments[0] == 'custom');
    }

    /**
     * Get template ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Template to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }
}