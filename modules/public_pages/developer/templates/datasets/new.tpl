{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="datasets" action="save"}
		{with model=$models.Dataset legend="Dataset Details"}
			<dl id="float-left">
				{input type='hidden'  attribute='id'}
				{input type='hidden'  attribute='owner'}
				{if is_null($model->id)}
					{input type='text' attribute='name'}
				{else}
					{input type='hidden' attribute='name'}
					{view_data attribute='name'}
				{/if}
				{input type='text'  attribute='title'}
				{textarea  attribute='description'}
			</dl>
			{if $model->isLoaded()}
				{submit value='update'}
			{else}
				{submit value='create'}
			{/if}
		{/with}
	{/form}
{/content_wrapper}