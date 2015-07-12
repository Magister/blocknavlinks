<?php

if (defined('__PS_VERSION_'))
    exit('Restricted Access!!!');

class AdminBlockNavLinksController extends ModuleAdminController {

    protected $position_identifier = 'id_blocknavlinks';

    public function __construct() {

        $this->table = 'blocknavlinks';
        $this->className = 'NavLinkItem';
        $this->lang = true;
        $this->bootstrap = true;
        $this->_defaultOrderBy = 'position';

        // add action on list
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // This adds a multiple deletion button
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );

        // create list
        $this->fields_list = array(
            'id_blocknavlinks' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 33
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'align' => 'left',
                'position' => 'position',
                'width' => 33
            ),
            'title' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
        );

        parent::__construct();
    }

    public static function sortModules($a, $b) {
        return $a['name'] > $b['name'];
    }

    // This method generates the Add/Edit form
    public function renderForm() {
        $modules = Module::getModulesInstalled();
        usort($modules, 'AdminBlockNavLinksController::sortModules');
        // Building the Add/Edit form
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('NavLink'),
                'icon' => 'icon-plus-sign-alt'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title:'),
                    'name' => 'title',
                    'size' => 33,
                    'required' => true,
                    'lang' => true,
                    'desc' => $this->l('Link title'),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Link type:'),
                    'name' => 'link_type',
                    'size' => 'auto',
                    'required' => true,
                    'values' => array(
                        array(
                            'value' => 0,
                            'id' => 'link_type_cms',
                            'label' => $this->l('CMS')
                        ),
                        array(
                            'value' => 1,
                            'id' => 'link_type_module',
                            'label' => $this->l('Module')
                        ),
                        array(
                            'value' => 2,
                            'id' => 'link_type_custom',
                            'label' => $this->l('Custom')
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'id' => 'cms_link',
                    'label' => $this->l('CMS Page:'),
                    'required' => true,
                    'name' => 'id_cms_link',
                    'options' => array(
                        'query' => array_merge(
                                array(
                                    array('id_cms' => 0, 'meta_title' => '--')
                                ),
                                CMS::getCmsPages($this->context->language->id)
                            ),
                        'id' => 'id_cms',
                        'name' => 'meta_title',
                    ),
                ),
                array(
                    'type' => 'select',
                    'id' => 'module_name',
                    'label' => $this->l('Module:'),
                    'required' => true,
                    'name' => 'module_name',
                    'options' => array(
                        'query' => $modules,
                        'id' => 'name',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'id' => 'module_controller',
                    'label' => $this->l('Module controller:'),
                    'required' => true,
                    'name' => 'module_controller',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('URL:'),
                    'id' => 'url',
                    'name' => 'url',
                    'size' => 33,
                    'required' => true,
                    'lang' => true,
                    'desc' => $this->l('Link URL (if not CMS)'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );
        return parent::renderForm();
    }

    public function ajaxProcessUpdatePositions() {
        $way = (int)Tools::getValue('way');
        $id_blocknavlinks = (int)Tools::getValue('id');
        $positions = Tools::getValue('blocknavlinks');

        if (is_array($positions))
            foreach ($positions as $position => $value)
            {
                $pos = explode('_', $value);

                if (isset($pos[2]) && (int)$pos[2] === $id_blocknavlinks)
                {
                        if (isset($position) && $this->updatePosition($way, $position, $id_blocknavlinks))
                            echo 'ok position '.(int)$position.' for id '.(int)$pos[1].'\r\n';
                        else
                            echo '{"hasError" : true, "errors" : "Can not update id '.(int)$id_blocknavlinks.' to position '.(int)$position.' "}';

                    break;
                }
            }
        Tools::clearCache(Context::getContext()->smarty, null, 'blocknavlinks');
    }

    public function updatePosition($way, $position, $id) {

        if (!$res = Db::getInstance()->executeS('
            SELECT `id_blocknavlinks`, `position`
            FROM `'._DB_PREFIX_.'blocknavlinks`
            ORDER BY `position` ASC'
        ))
            return false;

        foreach ($res as $blocknavlinks)
            if ((int)$blocknavlinks['id_blocknavlinks'] == (int)$id)
                $moved_blocknavlinks = $blocknavlinks;

        if (!isset($moved_blocknavlinks) || !isset($position))
            return false;

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'blocknavlinks`
            SET `position`= `position` '.($way ? '- 1' : '+ 1').'
            WHERE `position`
            '.($way
                ? '> '.(int)$moved_blocknavlinks['position'].' AND `position` <= '.(int)$position
                : '< '.(int)$moved_blocknavlinks['position'].' AND `position` >= '.(int)$position.'
            '))
        && Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'blocknavlinks`
            SET `position` = '.(int)$position.'
            WHERE `id_blocknavlinks` = '.(int)$moved_blocknavlinks['id_blocknavlinks']));

    }

    public function postProcess() {
        Tools::clearCache(Context::getContext()->smarty, null, 'blocknavlinks');
        parent::postProcess();
    }

}
