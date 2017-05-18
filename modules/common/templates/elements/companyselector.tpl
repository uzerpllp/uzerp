{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<form action="#" name="cs_form" method="get" id="company_selector">
	<select id="company" name="companyselector" >
		{foreach item=company key=id from=$companyselector}
		<option value="{$id}"{if $id==$smarty.const.EGS_COMPANY_ID} selected="true"{/if}>{$company}</option>
		{/foreach}
	</select>
</form>