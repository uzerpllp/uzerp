{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}

{if !$dialog}

	{if $cancel_action==''}
		{assign var=action value='cancel'}
	{else}
		{assign var=action value=$cancel_action}
	{/if}

	{form controller=$controller action=$action form_id='cancel_form'}
		{submit value="Cancel" name="cancelform" id="cancelform" tags=$tags}
	{/form}

{/if}