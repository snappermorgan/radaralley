<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\Utility;

use Designmodo\Qards\Page\Layout\Component\Component;
use Designmodo\Qards\Page\Layout\Layout;
use Designmodo\Qards\Http\Http;
use Designmodo\Qards\CodeTidy\CodeTidy;
use Designmodo\Qards\Page\Layout\Component\Template\Template;
/**
 * Api implements API handler.
 */
class Api
{

    /**
     * API handler
     *
     * @param bool $quietMode TRUE - return result; FALSE - print result and exit.
     * @return array
     */
    static public function handler($quietMode = false)
    {
        try {
            // Get method
            $method = $_REQUEST['method'];
            // Check permission
            $publicMethods = array('layout.css.get', 'component.css.get', 'settings.globalcss.get', 'font.get');
            if (!in_array($method, $publicMethods) && !current_user_can('edit_theme_options')) {
                throw new \Exception('You must be authorized and have permission "edit_theme_options" to call "' . $method . '"', 768423);
            }
            // Get params
            if (! empty($_REQUEST['params'])) {
                if (! is_array($_REQUEST['params'])) {
                    $params = json_decode(stripslashes($_REQUEST['params']), true);
                } else {
                    $params = $_REQUEST['params'];
                }
            } elseif ($input = file_get_contents('php://input')) {
                $payload = json_decode($input, true);
                $params = $payload['params'];
            }
            $params = (!empty($params) && is_array($params)) ? $params : array();
            if (!empty($params)) {
                $stripslashesDeepFunction = function($value) use ( &$stripslashesDeepFunction ) {
                    $value = is_array($value) ? array_map($stripslashesDeepFunction, $value) : stripslashes($value);
                    return $value;
                };
                $params = $stripslashesDeepFunction($params);
            }

            // Call action
            $actionName = self::underscoredToLowerCamelcase(str_replace('.', '_', $method)) . 'Action';
            try {
                $reflector = new \ReflectionMethod(__CLASS__, $actionName);
            } catch (\Exception $e) {
                throw new \Exception('API method "' . $method . '" does not exist.', 645912);
            }

            $args = array();
            foreach ($reflector->getParameters() as $param) {
                $name = $param->getName();
                $isArgumentGiven = array_key_exists(self::camelcasedToUnderscored($name), $params);
                if (! $isArgumentGiven && ! $param->isDefaultValueAvailable()) {
                    throw new \Exception('Parameter "' . self::camelcasedToUnderscored($name) . '" is mandatory for API method "' . $method . '", but was not provided.', 371248);
                }
                $args[$param->getPosition()] = $isArgumentGiven ? $params[self::camelcasedToUnderscored($name)] : $param->getDefaultValue();
            }
            $result = array(
                'result' => call_user_func_array(array(__CLASS__, $actionName), $args)
            );
        } catch (\Exception $e) {
            $result = array(
                'error' => array(
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                )
            );
        }

        if ($quietMode) {
            return $result;
        } else {
            echo json_encode($result);
            exit();
        }
    }

    /**
     * Convert underscored string to lower-camelcase format
     *
     * @param string $underscored
     * @return string
     */
    static protected function underscoredToLowerCamelcase($underscored)
    {
        return preg_replace('/_(.?)/e', "strtoupper('$1')", $underscored);
    }

    /**
     * Convert camelcase string to underscored format
     *
     * @param string $camelcased
     * @return string
     */
    static protected function camelcasedToUnderscored($camelcased)
    {
        return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $camelcased));
    }

    /**
     * Reset component model to default
     * component.model.reset
     *
     * @param int $componentId
     * @return bool
     */
    static protected function componentModelResetAction($componentId)
    {
        $component = new Component($componentId);
        Timber::clearCache();
        return (bool)$component->restore();
    }

    /**
     * Get component thumb
     * component.thumb.get
     *
     * @param array $componentIds
     * @return array
     */
    static protected function componentThumbGetAction($componentIds)
    {
        $thumbs = array();
        foreach ($componentIds as $componentId) {
            $component = new Component($componentId);
            $thumbs[$componentId] = $component->getThumb();
        }
        return $thumbs;
    }

    /**
     * Get layout CSS
     * layout.css.get
     *
     * @param int $layoutId
     * @return void
     */
    static protected function layoutCssGetAction($layoutId)
    {
        $layout = new Layout($layoutId);
        header('Content-Type:' . Http::CONTENT_TYPE_CSS);
        echo $layout->render(Http::CONTENT_TYPE_CSS);
        exit;
    }

    /**
     * Get component CSS
     * component.css.get
     *
     * @param array $componentIds
     * @param string $format
     * @return string
     */
    static protected function componentCssGetAction($componentIds, $format = 'css')
    {
        $result = '';
        $layout = new Layout(null);
        if (!is_array($componentIds) && !$componentIds = json_decode($componentIds, true)) {
            $result = '/* Request error */';
        } else {
            $layout->setComponents($componentIds);
            $result = $layout->render(Http::CONTENT_TYPE_CSS);
        }
        if (strtolower($format) == 'json') {
            return $result;
        } else {
            header('Content-Type:' . Http::CONTENT_TYPE_CSS);
            echo $result;
            exit;
        }
    }

    /**
     * Set custom component CSS
     * component.customcss.set
     *
     * @param int $componentId
     * @param string $customCss
     * @return bool|int
     */
    static protected function componentCustomcssSetAction($componentId, $customCss)
    {
        require_once DM_BASE_PATH . '/vendor/Oyejorge/Less/Less.php';
        $parser = new \Less_Parser();
        $parser->parse(
            '#test' .
            ' { ' .
            $customCss .
            ' }'
        );
        $component = new Component($componentId);
        $component->setCustomCss($customCss);
        return $component->save();
    }

    /**
     * Get custom component CSS
     * component.customcss.get
     *
     * @param int $componentId
     * @return string
     */
    static protected function componentCustomcssGetAction($componentId)
    {
        $component = new Component($componentId);
        return $component->getCustomCss();
    }

    /**
     * Save component data model
     * component.model.save
     *
     * @param array $models
     * @return boolean
     */
    static protected function componentModelSaveAction($models)
    {
        $result = true;
        foreach ($models as $componentId => $model) {
            $component = new Component($componentId);
            $component->setModel($model);
            $result = (bool)$component->save() && $result;
        }
        Timber::clearCache();
        return $result;
    }

    /**
     * Get component model
     * component.model.get
     *
     * @param int $componentId
     * @return array
     */
    static protected function componentModelGetAction($componentId)
    {
        $component = new Component($componentId);
        return $component->getModel();
    }

    /**
     * Create new component
     * component.create
     *
     * @param string $templateId
     * @param array $model
     * @param array $componentIds CSS of that components will be added
     * @return array
     */
    static protected function componentCreateAction($templateId, $model = array(), $componentIds = array())
    {
        $component = new Component();
        $component->setTemplate(new Template($templateId));
        if (! empty($model)) {
            $component->setModel($model);
        }
        $componentId = $component->save();
        if ($componentId) {
            $tpl = self::componentTemplateGetAction($componentId);
            $model = self::componentModelGetAction($componentId);
            $componentIds[] = $componentId;
            $layoutCss = self::componentCssGetAction($componentIds, 'json');
            return array(
                'component_id' => $componentId,
//                 'css' => $tpl['css'],
                'layout_css' => $layoutCss,
                'custom_css' => $tpl['custom_css'],
                'html' => $tpl['html'],
                'template_id' => $tpl['id'],
                'js' => $tpl['js'],
                'model' => $model,
                'is_custom' => $tpl['is_custom']
            );
        } else {
            throw new \Exception('Component was not created.', 324973);
        }
    }

    /**
     * Get list of components
     * components.get
     *
     * @param string $templateId Filter by template
     * @return array
     */
    static protected function componentsGetAction($templateId = null)
    {
        $params = array();
        if ($templateId) {
            $filter = ' && `template_id` = %s ';
            $params[] = $templateId;
        }
        $components = Db::getAll(
            'SELECT * FROM `' . Db::getPluginTableName(Db::TABLE_COMPONENT) . '`
            WHERE is_system = 0 && is_hidden = 0 ' . $filter . '
            ORDER BY `id` DESC',
            $params
        );
        return $components;
    }

    /**
     * Save layout
     * layout.save
     *
     * @param int $layoutId
     * @param array $components
     * @param bool $isPreview
     * @return int Layout ID
     */
    static protected function layoutSaveAction($layoutId, $components, $isPreview = false)
    {
        $componentIds = array();
        foreach ($components as $component) {
            $componentId = ! empty($component['id']) ? $component['id'] : null;
            if ($componentId) {
                $thumb = ! empty($component['thumb']) ? $component['thumb'] : null;
                if ($thumb) {
                    $component = new Component($componentId);
                    $component->setThumb($thumb);
                    $component->save();
                }
                $componentIds[] = $componentId;
            }
        }
        $layout = new Layout($layoutId);
        $layout->setComponents($componentIds);
        $layoutId = $layout->save($isPreview);

        // Clear old unrelated components
        Db::query(
            'DELETE FROM `' . Db::getPluginTableName(Db::TABLE_COMPONENT) . '`
            WHERE id NOT IN (
                SELECT component_id FROM `' . Db::getPluginTableName(Db::TABLE_LAYOUT_COMPONENT) . '`
            ) AND is_system = 0 AND created_ts < ' . (string)(time() - DM_UNRELATED_COMPONENT_MAX_AGE));
        Timber::clearCache();
        if ($layoutId) {
            return $layoutId;
        } else {
            throw new \Exception('Layout was not saved.', 764373);
        }
    }

    /**
     * Get list of layout components
     * layout.components.get
     *
     * @param unknown $layoutId
     * @return multitype:NULL
     */
    static protected function layoutComponentsGetAction($layoutId)
    {
        $layout = new Layout($layoutId);
        $result = array();
        foreach ($layout->getComponents() as $component) {
            $result[] = $component->toArray();
        }
        return $result;
    }

    /**
     * Get template of the component
     * component.template.get
     *
     * @param int $componentId
     * @param bool $pureHtml
     * @return array
     */
    static protected function componentTemplateGetAction($componentId, $pureHtml = false)
    {
        $result = array();
        if ($pureHtml) {
            Context::getInstance()->set('edit_mode', false);
        } else {
            Context::getInstance()->set('edit_mode', true);
        }
        $component = new Component($componentId);
        $layout = new Layout(null);
        $layout->setComponents(array($component->getId()));
        $jsPath = DM_TPL_PATH . DM_DS . $component->getTemplate()->getResourcePath(Template::RESOURCE_JS);
        $result = array(
            'id' => $component->getTemplate()->getId(),
            'html' => $layout->render(Http::CONTENT_TYPE_HTML, false),
            'css' => $layout->render(Http::CONTENT_TYPE_CSS, false),
            'custom_css' => $component->getCustomCss(),
            'js' =>  file_exists($jsPath) ? file_get_contents($jsPath) : '',
            'is_custom' => $component->getTemplate()->isCustom()
        );
        if ($pureHtml) {
            $codeTydyObj = new CodeTidy();
            $result['html'] = $codeTydyObj->parseHtml($result['html']);
            $result['custom_css'] = $codeTydyObj->parseCss($result['custom_css']);
        }
        return $result;
    }

    /**
     * Get permalink
     * permalink.get
     *
     * @param int $postId
     * @param array $query
     * @return string
     */
    static protected function permalinkGetAction($postId, $query = array())
    {
        return add_query_arg($query, get_permalink($postId));
    }

    /**
     * Get HTML for preview
     * layout.preview.get
     *
     * @param array $components array(array('component_id' => '1', 'template_id' => 'header.header1', 'model' => array()), array('template_id' => 'header.header2'))
     * @return string
     */
    static protected function layoutPreviewGetAction($components)
    {
        Timber::clearCache();
        $componentIds = array();
        $components = is_array($components) ? $components : array();
        foreach ($components as $componentData) {
            $component = new Component();
            $templateId = empty($componentData['template_id']) ? null : $componentData['template_id'];
            $component->setTemplate(new Template($templateId));
            if (isset($componentData['model']) && is_array($componentData['model'])) {
                $component->setModel($componentData['model']);
            }
            if (isset($componentData['component_id'])) {
                $origComponent = new Component($componentData['component_id']);
                $component->setCustomCss($origComponent->getCustomCss());
            }
            if ($component->save()) {
                $componentIds[] = $component->getId();
            } else {
                throw new \Exception('Can not create component by template "' . $componentData['template_id'] . '".', 823764);
            }
        }
        $layout = new Layout();
        $layout->setComponents($componentIds);
//         $layout->save(true);
        return array(
            'html' => $layout->render(Http::CONTENT_TYPE_HTML, false),
            'css' => $layout->render(Http::CONTENT_TYPE_CSS, false)
        );
    }

    /**
     * Set post title
     * post.title.set
     *
     * @param int $postId
     * @param string $title
     * @return bool
     */
    static protected function postTitleSetAction($postId, $title)
    {
        Timber::clearCache();
        return (bool)Db::update(
            Db::getWpDb()->posts,
            array('post_title' => $title),
            array('ID' => $postId)
        );
    }

    /**
     * Get global CSS
     * settings.globalcss.get
     *
     * @return void
     */
    static protected function settingsGlobalcssGetAction()
    {
        header('Content-Type:' . Http::CONTENT_TYPE_CSS);
        $dmOptions = get_option('qards_settings');
        echo $dmOptions['DM_GLOBAL_CSS'];
        exit;
    }

    /**
     * Add\Update template
     * template.save
     *
     * @param string $templateId
     * @param string $html
     * @param string $css
     * @return bool
     */
    static protected function templateSaveAction($templateId, $html, $css)
    {
        $template = new Template($templateId);
        if (!$template->isCustom()) {
            throw new \Exception('Template must be "custom".', 324256);
        }
//         $customDir = dirname(dirname(dirname(DM_TPL_BUILDIN_PATH . DM_DS . $template->getResourcePath(Template::RESOURCE_TPL))));
//         if (! is_writable($customDir)) {
//             throw new \Exception('Resource directory is not writable. Custom blocks will not work till you make the templates directory writable or just assign the 777 permissions to ' . $customDir . '.', 324322);
//         }
        $types = array(
            array(
                'type' => Template::RESOURCE_TPL,
                'content' => $html
            ),
            array(
                'type' => Template::RESOURCE_JSON,
                'content' => '{}'
            ),
            array(
                'type' => Template::RESOURCE_CSS,
                'content' => $css
            )
        );
        foreach ($types as $type) {
            $existId = Db::getColumn(
                'SELECT `id` FROM `' . Db::getPluginTableName(Db::TABLE_RESOURCE) . '` WHERE template_id = %s AND type = %s LIMIT 1',
                array(
                    $templateId,
                    $type['type']
                )
            );
            if ($existId) {
                Db::update(
                    Db::getPluginTableName(Db::TABLE_RESOURCE), array(
                        'template_id' => $templateId,
                        'type' => $type['type'],
                        'data' => $type['content'],
                        'is_custom' => 1
                    ),
                    array('id' => $existId)
                );
            } else {
                Db::insert(
                    Db::getPluginTableName(Db::TABLE_RESOURCE), array(
                        'template_id' => $templateId,
                        'type' => $type['type'],
                        'data' => $type['content'],
                        'is_custom' => 1
                    )
                );
            }
        }
        return true;
    }

    /**
     * Get Fonts list
     * font.get
     *
     * @return string
     */
    static protected function fontGetAction()
    {
        $file = @file_get_contents(DM_TPL_JS_CONFIG_PATH.DM_DS.'fonts.json');
        if (!$file) {
            throw new \Exception('No proper json file in' . DM_TPL_JS_CONFIG_PATH.DM_DS.'fonts.json', 723747);
        }
        header('Content-Type:' . Http::CONTENT_TYPE_JSON);
        // FIXME change to normal json response
        echo $file;
        exit;
    }

    /**
     * Remove attached image
     * attachement.delete
     *
     * @param string $attachementUrl
     * @return bool
     */
    static protected function attachementDeleteAction($attachementUrl)
    {
        // Normalize URL
        $address = explode('/', $attachementUrl);
        $keys = array_keys($address, '..');
        foreach($keys AS $keypos => $key) {
            array_splice($address, $key - ($keypos * 2 + 1), 2);
        }
        $address = implode('/', $address);
        $attachementUrl = str_replace('./', '', $address);

        // Get attach id by url
        $image = Db::getRow('SELECT * FROM `' . Db::getWpDb()->posts  . '` WHERE post_type="attachment" AND guid = %s', array($attachementUrl));
        if (empty($image['ID'])) {
            throw new \Exception('This attachment can not be deleted.', 247614);
        }
        $attachmentid = $image['ID'];

        // Delete by WP
        if (wp_delete_attachment( $attachmentid, true) === false) {
            throw new \Exception('Can\'t delete record from filesystem, please, check file permissions of your upload folder: '.wp_upload_dir() . '.', 234824);
        }
        return true;
    }
}