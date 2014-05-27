{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}

{if $options.pdf_browser_printing===TRUE}
	<iframe style="display: none;" src="{$options.location}" id="PDFtoPrint"></iframe>
{/if}

{if $options.pdf_preview===TRUE}
	<ul class="pdf-preview" data-build-preview-link="{$option.pdf_preview_link}" data-pdf-location="{$options.paths.temp_file_path}" >
		<li>Building PDF Preview...</li>
		<li class="spinner"><img src="/themes/{$theme}/graphics/ajax_load.gif" /></li>
	</ul>
{/if}

<p class="wait_title">Output Complete</p>

<p class="wait_spinner"><img class="tick" src="/themes/{$theme}/graphics/large_tick.png" alt="Output complete, click to close" title="Output complete, click to close" /></p>

<p>The document has been successfully generated.</p>
{$options.message}

<div id="print_footer">
	<a class="button close">Close</a>
	{if $options.buttons.print === TRUE}
		<a class="button print">Print</a>
	{/if}
	{if $options.buttons.open === TRUE}
		<a class="button open" href="{$options.location}" target="_blank">Open</a>
	{/if}
</div>