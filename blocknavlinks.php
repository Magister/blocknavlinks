<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'blocknavlinks/model/NavLinkItem.php');
include_once(_PS_MODULE_DIR_.'blocknavlinks/controllers/admin/AdminBlockNavLinksController.php');

class BlockNavLinks extends Module
{
    public function __construct()
    {
        $this->name = 'blocknavlinks';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->lang = true;

        parent::__construct();

        $this->displayName = $this->l('Nav links block');
        $this->description = $this->l('Adds links to top menu.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install($delete_params = true)
    {
        if ($delete_params) {
            if (!$this->installDb() || !$this->addTab() )
                return false;
        }
        return (parent::install() && $this->registerHook('displayNav') && $this->registerHook('header'));
    }

    protected function installDb() {

        $db = Db::getInstance();
        if (!$db->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'blocknavlinks` (
            `id_blocknavlinks` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_shop` INT UNSIGNED NOT NULL,
            `id_cms_link` INT UNSIGNED NOT NULL,
            `is_cms` TINYINT(1) UNSIGNED ZEROFILL NOT NULL,
            `date_add` DATE,
            `date_upd` DATE,
            `position` TEXT
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
            return false;

        if (!$db->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'blocknavlinks_lang` (
            `id_blocknavlinks` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_lang` INT UNSIGNED NOT NULL,
            `title` VARCHAR(255),
            `url` VARCHAR(255)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
            return false;

        return true;
    }

    public function reset() {
        if (!$this->uninstall(false))
                return false;
        if (!$this->install(false))
                return false;

        return true;
    }

    public function uninstall($delete_params = true) {

        // Uninstall Tabs
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
                foreach ($moduleTabs as $moduleTab) {
                        $moduleTab->delete();
                }
        }

        if ($delete_params && !$this->deleteDb()) {
            return false;
        }
        if (!parent::uninstall())
            return false;

        return true;
    }

    protected function deleteDb() {
        $db = Db::getInstance();
        if (!$db->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'blocknavlinks`'))
            return false;

        if (!$db->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'blocknavlinks_lang`'))
            return false;

        return true;
    }

    /**
    * Returns module content for header
    *
    * @param array $params Parameters
    * @return string Content
    */
    public function hookTop($params) {
        if (!$this->isCached('blocknavlinks-header.tpl', $this->getCacheId())) {
            $id_lang = $this->context->language->id;
            $links = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
               'SELECT
                   n.`id_blocknavlinks` as id,
                   n.`id_cms_link` as id_cms_link,
                   n.`is_cms` as is_cms,
                   nl.`title` as title,
                   nl.`url` as url,
                   cms_lang.`link_rewrite` as link_rewrite
                FROM `'._DB_PREFIX_.'blocknavlinks` as n
                    LEFT JOIN `'._DB_PREFIX_.'blocknavlinks_lang` as nl
                        ON n.`id_blocknavlinks` = nl.`id_blocknavlinks` AND nl.`id_lang` = '.$id_lang.'
                    LEFT JOIN `'._DB_PREFIX_.'cms_lang` as cms_lang
                        ON n.`id_cms_link` = cms_lang.`id_cms` AND cms_lang.`id_lang` = '.$id_lang.'
                ORDER BY n.`position`'
                );
            $this->smarty->assign('links', $links);
        }
        return $this->display(__FILE__, 'blocknavlinks-header.tpl', $this->getCacheId());
    }

    public function hookDisplayNav($params) {
        return $this->hookTop($params);
    }

    public function hookHeader($params) {
        $this->context->controller->addCSS(($this->_path).'blocknavlinks.css', 'all');
    }

    public function addTab() {
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages() as $language)
            $tab->name[$language['id_lang']] = 'AdminBlockNavLinks';
        $tab->class_name = 'AdminBlockNavLinks';
        // if id_parent = -1, tab will not be visible in menu
        $tab->id_parent = -1;
        $tab->module = $this->name;
        if(!$tab->add())
            return false;
        return true;
    }

    public function getContent() {
        return '<a class="btn btn-default" href="' . $this->context->link->getAdminLink('AdminBlockNavLinks') . '">' .
            $this->l('Setup links') . '</a>';
    }

}
