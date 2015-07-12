<?php

if (!defined('_PS_VERSION_'))
        exit;

class NavLinkItem extends ObjectModel
{
    public $id_blocknavlinks;
    public $id_shop;
    public $position;
    public $link_type;
    public $id_cms_link;
    public $module_name;
    public $module_controller;
    public $title;
    public $url;
    public $date_add;
    public $date_upd;

    const LINK_TYPE_CMS = 0;
    const LINK_TYPE_MODULE = 1;
    const LINK_TYPE_CUSTOM = 2;

    public static $definition = array(
        'table' => 'blocknavlinks',
        'primary' => 'id_blocknavlinks',
        'multilang' => true,
        'fields' => array(
            'id_shop' =>            array('type' => self::TYPE_INT),
            'position' =>           array('type' => self::TYPE_INT),
            'link_type' =>          array('type' => self::TYPE_INT),
            'id_cms_link' =>        array('type' => self::TYPE_INT),
            'module_name' =>        array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' > 128),
            'module_controller' =>  array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' > 128),
            'title' =>              array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
            'url' =>                array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl', 'size' => 255),
            'date_add' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public  function __construct($id_blocknavlinks = null, $id_lang = null, $id_shop = null, Context $context = null) {
        parent::__construct($id_blocknavlinks, $id_lang, $id_shop);
    }

    public function add($autodate = true, $null_values = false) {
        $this->do_preprocess();
        $context = Context::getContext();
        $this->id_shop = $context->shop->id;

        $res = Db::getInstance()->executeS('SELECT MAX(position) as pos FROM '._DB_PREFIX_.'blocknavlinks WHERE id_shop='.$this->id_shop);
        if (isset($res[0]['pos'])) {
            $this->position = $res[0]['pos'] + 1;
        } else {
            $this->position = 0;
        }

        $res = parent::add($autodate, $null_values);
        return $res;
    }

    public function delete() {
        $res = true;

        $res &= $this->reOrderPositions();

        $res &= parent::delete();
        return $res;
    }

    protected function do_preprocess() {
        if ($this->link_type == self::LINK_TYPE_CMS) {
            $this->url = '';
            $this->module_name = '';
            $this->module_controller = '';
        } else if ($this->link_type == self::LINK_TYPE_MODULE) {
            $this->id_cms_link = 0;
            $this->url = '';
        } else if ($this->link_type == self::LINK_TYPE_CUSTOM) {
            $this->id_cms_link = 0;
            $this->module_name = '';
            $this->module_controller = '';
        }
    }

    public function save($null_values = false, $autodate = true) {
        $this->do_preprocess();
        return parent::save($null_values, $autodate);
    }

    public function update($null_values = false) {
        $this->do_preprocess();
        return parent::update($null_values);
    }

    public function reOrderPositions() {
        $id_slide = $this->id;
        $context = Context::getContext();
        $id_shop = $context->shop->id;

        $max = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT MAX(nl.`position`) as position
            FROM `'._DB_PREFIX_.'blocknavlinks` nl
            WHERE nl.`id_shop` = '.(int)$id_shop
        );

        if ((int)$max == (int)$id_slide)
            return true;

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT nl.`position` as position, nl.`id_blocknavlinks` as id_blocknavlinks
            FROM `'._DB_PREFIX_.'blocknavlinks` nl
            WHERE nl.`id_shop` = '.(int)$id_shop.' AND nl.`position` > '.(int)$this->position
        );

        foreach ($rows as $row) {
            $current_link= new NavLinkItem($row['id_blocknavlinks']);
            --$current_link->position;
            $current_link->update();
            unset($current_link);
        }

        return true;
    }

    public static function getAssociatedIdsShop($id_blocknavlinks) {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT nl.`id_shop`
            FROM `'._DB_PREFIX_.'blocknavlinks` nl
            WHERE hs.`id_blocknavlinks` = '.(int)$id_blocknavlinks
        );

        if (!is_array($result))
            return false;

        $return = array();

        foreach ($result as $id_shop)
            $return[] = (int)$id_shop['id_shop'];

        return $return;
    }

}
