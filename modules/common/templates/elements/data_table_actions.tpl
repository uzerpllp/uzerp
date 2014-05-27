{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}

{if $data_table_actions}
	<fieldset id="mass_action">
		<label for="data_table_action">All Selected:</label>
		<select name="data_table_action">
			{html_options options=$data_table_actions}
		</select>
		<input type="submit" value="Go" />
	</fieldset>
{/if}