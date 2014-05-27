{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.14 $ *}
<div class="print_dialog">
	<div class="print_dialog_inner">
		{form controller=$controller action=$redirect submit_token=FALSE }
			{foreach from=$query_data key=key item=value}
				{if $key!='submit_token'}
					<input type="hidden" name="{$key}" value="{$value}" />
				{/if}
			{/foreach}
			<input type="hidden" name="encoded_query_data" value="{$encoded_query_data}" />
			{input type='hidden' attribute='usercompanyid' }
			<div id="print">
				<ul>
					<li>
						<label for="printtype">File Type:</label>
						<select id="printtype" name="print[printtype]" >
							{html_options options=$options.print_type selected=$options.default_print_type}
						</select>
					</li>
					{if !empty($options.report_type)}
						<li class="pdf pdf_options output_options">
							<label for="report_type">Report Type:</label>
							<select id="report_type" name="print[report_type]">
								{html_options options=$options.report_type selected=$options.default_report_type}
							</select>
						</li>
					{/if}
					{if empty($options.report_name) && !empty($options.report)}
						<input type='hidden' name='print['report'] value=$options.report>
					{else}
						<li class="pdf pdf_options output_options">
							<label for="report_name">Report Name:</label>
							<select id="report" name="print[report]" class="nonone">
								{html_options options=$options.report_name selected=$options.report}
							</select>
						</li>
					{/if}
					<li class="csv csv_options output_options">
						<label for="fieldnames">Include Field Names:</label>
						<input id="fieldnames" type='checkbox' name="print[fieldnames]" class='checkbox'>
					</li>
					<li class="csv csv_options output_options">
						<label for="fieldseparater">CSV Field Separater:</label>
						<select id="fieldseparater" name="print[fieldseparater]">
							{html_options options=$options.field_separater}
						</select>
					</li>
					<li class="csv csv_options output_options">
						<label for="textdelimiter">CSV Text Delimiter:</label>
						<select id="textdelimiter" name="print[textdelimiter]">
							{html_options options=$options.text_delimiter}
						</select>
					</li>
				</ul>
				<input type="hidden" id="printaction" name="print[printaction]" value="view" />
				<ul>
					<li>
						<label for="printaction">Action:</label>
						<select id="printaction" name="print[printaction]" >
							{if $options.pdf_browser_printing === TRUE }
								<option value="quick_output">Quick Output</a>
							{/if}
							{html_options options=$options.print_action selected=$options.default_print_action}
						</select>
					</li>
					<li class="print print_options action_options">
						<label for="printer">Printer:</label>
						<select name="print[printer]">
							{html_options options=$options.printers selected=$options.default_printer}
						</select>
					</li>
					<li class="print print_options action_options">
						<label for="print_copies">No. of Copies:</label>
						<input type='text' name='print[print_copies]' value=1>
					</li>
					<li class="email email_options action_options">
						<label for="email">Email Address:</label>
						<input type='text' name='print[email]' value={$options.email}>
					</li>
					<li class="email email_options action_options">
						<label for="email_subject">Email Subject:</label>
						<input type='text' name='print[email_subject]' value={$options.email_subject}>
					</li>
					<li class="email email_options action_options">
						<label for="emailtext">Email Message:</label>
						<textarea cols=50 rows=5 name='print[emailtext]'>{$options.email_text}</textarea>
					</li>
					<li class="save save_options action_options">
						<label for="filename">File Name:</label>
						<input type='text' name='print[filename]' value={$options.filename} title='The file name should NOT include the extension'>
					</li>
				</ul>
			</div>
			<div id="print_footer">
				<button class="output">Output Document</button>
			</div>
		{/form}
	</div>
</div>