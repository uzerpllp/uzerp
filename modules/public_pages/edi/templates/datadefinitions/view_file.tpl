{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.3 $ *}
	<h2>
		File : {$file}
		&nbsp;&nbsp;&nbsp;
		{link_to module=$module controller=$controller action='process_file' id=$datadefinition->id data=$data filename=$file value=$type implementation_class=$datadefinition->implementation_class}
		{if $validate!=''}
			&nbsp;&nbsp;&nbsp;
			{link_to module=$module controller=$controller action='view_file' id=$datadefinition->id data=$data validate=true filename=$file value=$validate implementation_class=$datadefinition->implementation_class}
		{/if}
		{if $missing_data}
			&nbsp;&nbsp;&nbsp;
			{link_to module=$module controller=$controller action='load_missing_data' id=$datadefinition->id data=$data filename=$file value="Load Missing Data" implementation_class=$datadefinition->implementation_class}
		{/if}
	</h2>
	<br>
	{include file=$data_tree collection=$doc->childNodes class='id="collapsible_tree" class="collapsible_tree float-left"'}
{/content_wrapper}