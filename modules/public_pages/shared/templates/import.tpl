{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<h1>Import {$what|default:$controller}</h1>
<form action="/?module={$module}&amp;controller={$controller}&action=do_import" method="post" enctype="multipart/form-data" id="import_form">
<table id="formtable">
	<tr>
		<td>
			<label for="import_file">File:</label>
		</td>
		<td>
			<input type="file" name="file" id="import_file" />
		</td>
	</tr>
	<tr>
		<td>
			<label for="contains_headings">Headings are first row?</label>
		</td>
		<td>
			<input type="checkbox" class="checkbox" name="contains_headings" id="contains_headings" />
		</td>
	</tr>
	<tr>
		<td>Or: provide definition</td>
		<td><label for="num_cols"># Columns</label>
			<input type="text" class="numeric" id="num_cols" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="submit" value="Import" />
		</td>
	</tr>
</table>
</form>
<ul id="available_fields" style="display:none">
{foreach from=$fields item=field}
<li>{$field}</li>
{/foreach}
</ul>
<script type="text/javascript">
{if $js_extension}
{$js_extension}
{/if}
var iw = new ImportWizard('import_form');
{if $callback}
iw.addCallback('/?javascript&module={$module}&controller={$controller}&action=','{$callback}');
{/if}

</script>
