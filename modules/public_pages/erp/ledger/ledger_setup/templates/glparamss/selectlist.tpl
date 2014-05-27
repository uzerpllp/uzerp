{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<div id="GLParams_ajaxvalue">
	{with model=$glparams}
		{if count($selectlist)==0}
			{input type='text' attribute="paramvalue" label='Value' class="compulsory"}
		{else}
			{select attribute="paramvalue_id" label='Value' nonone=true options=$selectlist selected=$selected}
		{/if}
	{/with}
</div>