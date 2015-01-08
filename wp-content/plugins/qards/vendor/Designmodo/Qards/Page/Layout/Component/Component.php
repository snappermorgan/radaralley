<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\Page\Layout\Component;

use Designmodo\Qards\Page\Layout\Component\Template\Template;
use Designmodo\Qards\Utility\Db;

/**
 * Component implements the HTML\CSS part of the page layout.
 */
class Component
{

    /**
     * Component ID
     *
     * @var int
     */
    protected $id;

    /**
     * Component ID.
     *
     * @var Template
     */
    protected $template;

    /**
     * Component model.
     *
     * @var array
     */
    protected $model;

    /**
     * Component custom CSS.
     *
     * @var string
     */
    protected $customCss;

    /**
     * Is system
     *
     * @var bool
     */
    protected $isSystem;

    /**
     * Is hidden
     *
     * @var bool
     */
    protected $isHidden;

    /**
     *
     * @var string
     */
    protected $thumb;


    /**
     * Constructor
     *
     * @param int $id
     * @return void
     */
    public function __construct($id = null)
    {
        $this->id = $id;
        if ($this->getId()) {
            $result = Db::getRow(
                'SELECT * FROM `' . Db::getPluginTableName(Db::TABLE_COMPONENT) . '` WHERE id = %d',
                array($this->getId())
            );
            if (empty($result)) {
                throw new \Exception('Component "' . $this->getId() . '" not found.', 435736);
            }
            $this->setTemplate(new Template($result['template_id']));
            $this->setModel(json_decode($result['model'], true));
            $this->setCustomCss($result['custom_css']);
            $this->setThumb($result['thumb']);
            $this->isSystem = $result['is_system'];
            $this->isHidden = $result['is_hidden'];
        } else {
            $this->setModel(array());
            $this->isSystem = false;
            $this->isHidden = true;
        }
    }

    /**
     * Get component thumb
     *
     * @return string
     */
    public function getThumb()
    {
        return $this->thumb;
    }

    /**
     * Set component thumb
     *
     * @param string $thumb
     * @return void
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;
    }

    /**
     * Get default component data model
     *
     * @return mixed
     */
    public function getDefaultModel()
    {
        return $this->getTemplate()->getDefaultModel();
    }

    /**
     * Set component
     *
     * @param Template $component
     * @return void
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;
    }

    /**
     * Set data model
     *
     * @param array $model
     * @return void
     */
    public function setModel($model)
    {
        $model = is_array($model) ? $model : array();
        $this->model = $model;
    }

    /**
     * Get component data model
     *
     * @return array
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set custom CSS
     *
     * @param string $css
     * @return void
     */
    public function setCustomCss($css)
    {
        $this->customCss = $css;
    }

    /**
     * Get custom CSS
     *
     * @return string
     */
    public function getCustomCss()
    {
        return $this->customCss;
    }

    /**
     * Is system
     *
     * @return bool
     */
    public function isSystem()
    {
        return $this->isSystem;
    }

    /**
     * Is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->isHidden;
    }

    /**
     * Save
     *
     * @return bool|int
     */
    public function save()
    {
        $templateId = $this->getTemplate()->getId();
        if (empty($templateId)) {
            throw new \Exception('Could not save component without template.', 834427);
        }
        if ($this->getId()) {
            $result = Db::update(
                Db::getPluginTableName(Db::TABLE_COMPONENT),
                array(
                    'template_id' => $this->getTemplate()->getId(),
                    'model' => json_encode($this->getModel()),
                    'custom_css' => $this->getCustomCss(),
                    'thumb' => $this->getThumb(),
                    'is_hidden' => $this->isHidden(),
                    'is_system' => $this->isSystem()
                ),
                array(
                    'id' => $this->getId()
                )
            );
            $result = $result !== false ? $this->getId() : false;
        } else {
            if (!$this->getModel()) {
                $this->setModel($this->getDefaultModel());
            }
            $result = Db::insert(
                Db::getPluginTableName(Db::TABLE_COMPONENT),
                array(
                    'template_id' => $this->getTemplate()->getId(),
                    'model' => json_encode($this->getModel()),
                    'custom_css' => $this->getCustomCss(),
                    'thumb' => $this->getThumb(),
                    'is_hidden' => $this->isHidden(),
                    'is_system' => $this->isSystem()
                )
            );
            if ($result !== false) {
                $this->id = $result;
            }
        }
        return $result;
    }

    /**
     * Restore default data model
     *
     * @return bool|int
     */
    public function restore()
    {
        $this->setModel($this->getDefaultModel());
        return $this->save();
    }

    /**
     * Get component ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get template
     *
     * @return Template
     */
    public function getTemplate()
    {
        if (!$this->template) {
            throw new \Exception('Impossible to get template of new component.', 832442);
        }
        return $this->template;
    }

    /**
     * Delete component
     *
     * @return bool
     */
    public function delete()
    {
        $result1 = Db::delete(
            Db::getPluginTableName(Db::TABLE_COMPONENT),
            array(
                'id' => $this->getId()
            )
        );
        $result2 = Db::delete(
            Db::getPluginTableName(Db::TABLE_LAYOUT_COMPONENT),
            array(
                'component_id' => $this->getId()
            )
        );
        return ($result1 !== false && $result2 !== false);
    }

    /**
     * Component to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'template_id' => $this->getTemplate()->getId(),
            'model' => $this->getModel(),
            'custom_css' => $this->getCustomCss(),
            'thumb' => $this->getThumb(),
            'is_hidden' => $this->isHidden(),
            'is_system' => $this->isSystem()
        );
    }
}