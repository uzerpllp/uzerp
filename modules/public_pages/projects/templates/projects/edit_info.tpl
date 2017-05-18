{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="projects" action="save"}
		{with model=$models.Project legend="Project Details"}
			{input type='hidden'  attribute='id' }
			<dl id="view_data_fullwidth">
			{textarea attribute='objectives' }
			{textarea attribute='requirements' }
			{textarea attribute='exclusions' }
			{textarea attribute='constraints' }
			{textarea attribute='key_assumptions' }
			{textarea attribute='slippage' }
			</dl>
		{/with}
		{submit another=false}
	{/form}
{/content_wrapper}