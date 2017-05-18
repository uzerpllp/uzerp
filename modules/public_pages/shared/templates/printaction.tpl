{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	<div id="print_action" class="clearfix">
		{form controller=$controller action=$redirect}
			{foreach from=$query_data key=key item=value}
				{if is_array($value)}
					{assign var=pos value=$smarty.template|strrpos:'/'}
					{assign var=template_dir value=$smarty.template|substr:0:$pos}
					{include file="$template_dir/printaction_data.tpl" template_dir=$template_dir value=$value parent=$key}
				{else}
					<input type="hidden" name="{$key}" value="{$value}" />
				{/if}
			{/foreach}
			{input type='hidden'  attribute='usercompanyid' }
			<div id=print>
				<dt><label for="printtype">Print Type</label>:</dt>
				<dd>
					<select id="printtype" name="printtype" >
						{html_options options=$printtype selected=$defaultprinttype}
					</select>
				</dd>
				<dt><label for="printaction">Print Action</label>:</dt>
				<dd>
					<select id="printaction" name="printaction" >
						{html_options options=$printaction selected=$defaultprintaction}
					</select>
				</dd>
			</div>
			<div id='csv' visible=false>
				<dt><label for="textdelimiter">Include Field Names</label>:</dt>
				<dd>
					<input type='checkbox' name="fieldnames" class='checkbox'>
				</dd>
				<dt><label for="fieldseparater">CSV Field Separater</label>:</dt>
				<dd>
					<select name="fieldseparater">
						{html_options options=$fieldseparater}
					</select>
				</dd>
				<dt><label for="textdelimiter">CSV Text Delimiter</label>:</dt>
				<dd>
					<select name="textdelimiter">
						{html_options options=$textdelimiter}
					</select>
				</dd>
			</div>
			<div id='Print' visible=false>
				<dt><label for="printer">Printer</label>:</dt>
				<dd>
					<select name="printer">
						{html_options options=$printers selected=$default_printer}
					</select>
				</dd>
			</div>
			<div id='Save' visible=false>
				<dt><label for="filename">File Name</label>:</dt>
				<dd>
					<input type='text' name='filename' value={$filename}>
				</dd>
			</div>
			<div id='Email' visible=false  class=view_data_left>
				<dt><label for="email">Email Address</label>:</dt>
				<dd>
					<input type='text' name='email' value={$email}>
				</dd>
				<dt><label for="emailtext">Email Message</label>:</dt>
				<dd>
					<textarea cols=50 rows=5 name='emailtext'>{$emailtext}</textarea>
				</dd>
			</div>
			<div id=view_data_bottom>
				{if $testprint}
					{submit id='test_print' value='Test Print' another='false'}
				{/if}
				{submit value='OK' another='false'}
			</div>
			{/form}
		<div id=view_data_bottom>
			{include file='elements/cancelForm.tpl'}
		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
			legacyForceChange('#printtype');
			legacyForceChange('#printaction');
		});
	</script>
{/content_wrapper}