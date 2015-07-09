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
                'align' => 'center',
                'position' => 'position',
                'width' => 'auto'
            ),
            'title' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
            'id_cms_link' => array(
                'title' => $this->l('CMS page'),
                'width' => 'auto'
            )
        );

        parent::__construct();
    }

    protected function renderCmsPageSelect($pages) {
        $html = '';
        foreach ($pages as $page) {
            $html .= '<option value="'.$page['id_cms'].'"'.(($this->object->id_cms_link == $page['id_cms']) ? ' selected="selected"' : '').'>'
                        .$page['meta_title'].'</option>';
        }
        return $html;
    }

    // This method generates the Add/Edit form
    public function renderForm() {
        $pages = CMS::getCmsPages($this->context->language->id);
        $pages_html = $this->renderCmsPageSelect($pages);
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
                    'type' => 'select_category',
                    'label' => $this->l('CMS Page:'),
                    'name' => 'id_cms_link',
                    'options' => array(
                        'html' => $pages_html,
                    ),
                )
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
