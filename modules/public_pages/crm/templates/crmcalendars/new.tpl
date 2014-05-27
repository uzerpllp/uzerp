{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	{form controller="crmcalendars" action="save"}
		{with model=$model}
			{view_section heading='calendar_details'}
				{input type='hidden' attribute='id'}
				{input type='text' attribute='title'}
				<dt>Calendar Colour:</dt>
				<dd>
					<ul class="calendar_colours">
						{foreach from=$colours key=key item=value}
						
							{if $model->colour == '' || $model->colour != $value}
								<li style="background-color: {$value};"><input name="CRMCalendar[colour]" value="{$value}" type="radio" class="radio"></li>
							{elseif $model->colour == $value}
								<li style="background-color: {$value};"><input name="CRMCalendar[colour]" value="{$value}" type="radio" checked class="radio"></li>
							{/if}
						
						{/foreach}
					</ul>
				</dd>
			{/view_section}
		{/with}
		{submit}
	{/form}
{/content_wrapper}