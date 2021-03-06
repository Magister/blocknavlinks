{*
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
*}

<!-- Block nav links module HEADER -->
<ul id="header_nav_links">
	{foreach from=$links key='index' item='link_item' name='links'}
	<li>
            {if $link_item['link_type'] == 0}
            <a href="{$link->getCMSLink($link_item['id_cms_link'], $link_item['link_rewrite'])|escape:'html'}" title="{$link_item['title']}">{$link_item['title']}</a>
            {/if}
            {if $link_item['link_type'] == 1}
            <a href="{$link->getModuleLink($link_item['module_name'], $link_item['module_controller'])|escape:'html'}" title="{$link_item['title']}">{$link_item['title']}</a>
            {/if}
            {if $link_item['link_type'] == 2}
            <a href="{$link_item['url']|escape:'html'}" title="{$link_item['title']}">{$link_item['title']}</a>
            {/if}
        </li>
	{/foreach}
</ul>
<!-- /Block nav links module HEADER -->
