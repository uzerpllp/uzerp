{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<form action="#" name="cs_form" method="get" id="company_selector">
	<p>You have access to more than one company on this system, choose one below:</p>
	<select name="companyselector" id="company" >
		{foreach item=company key=id from=$content}
		<option value="{$id}"{if $id==$smarty.const.EGS_COMPANY_ID} selected="selected"{/if}>{$company}</option>
		{/foreach}
	</select>
</form>