{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{form controller=$self.controller action=$action notags=true}
<fieldset class="quicksearch">
	<legend title="Quick Search">Quick Search</legend>
	<input type="text" name="quicksearch" />
	<select name="quicksearchfield">
		{foreach  key=key item=item from=$collection->getDisplayFieldNames()}
			<option value="{$key}">{$item}</option>
		{/foreach}
	</select>
	<input type="submit" name="submit" value="Go" title="Start new search"/><input type="submit" name="submit" value="Refine" title="Add constraint to search"/>
</fieldset>
{/form}

