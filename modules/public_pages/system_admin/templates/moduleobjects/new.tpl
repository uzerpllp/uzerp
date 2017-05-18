{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
{content_wrapper}
	{form controller="moduleobjects" action="save"}
		{with model=$models.ModuleObject legend="Module Object Details"}
			{input type='hidden' attribute='id' }
			{input type='text' attribute='name' class="compulsory" }
			{input type='text'  attribute='location' class="path_name" }
			{input type='text'  attribute='help_link' class="website" }
			{textarea attribute='description' }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl' action="cancel"}
	<script type="text/javascript">
		if($('#ModuleObject_location').val()=='') {
			$('#ModuleObject_name').trigger("change");
		}
	</script>
{/content_wrapper}