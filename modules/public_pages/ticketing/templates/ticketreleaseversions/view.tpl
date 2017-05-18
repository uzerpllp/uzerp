{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$TicketReleaseVersion}
			<dl class="float-left">
				{assign var='title' value=$model->getIdentifierValue()}
				{view_section heading="$title"}
					{assign var=fields value=$model->getDisplayFieldNames()}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{view_data attribute=$fieldname label=$tag}
					{/foreach}
					{view_data attribute='no_of_tickets' value=$model->ticket_count()}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}