{*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}
{block name="input_row"}
	{if isset($input.id)}
	<div id="{$input.id}">
	{/if}
		{$smarty.block.parent}
	{if isset($input.id)}
	</div>
	{/if}
{/block}
{block name="script"}
	function switch_link_type() {
		var link_type = $('input[name=link_type]').filter(':checked').val();
		if (link_type == "0") {
			// CMS
			$('#cms_link').show();
			$('#url').hide();
			$('#module_name').hide();
			$('#module_controller').hide();
		} else if (link_type == "1") {
			// Module
			$('#cms_link').hide();
			$('#url').hide();
			$('#module_name').show();
			$('#module_controller').show();
		} else {
			// Custom
			$('#cms_link').hide();
			$('#url').show();
			$('#module_name').hide();
			$('#module_controller').hide();
		}
	};
	$(document).ready(function() {
		switch_link_type();
	});
	$('input[name=link_type]').change(switch_link_type);
{/block}
