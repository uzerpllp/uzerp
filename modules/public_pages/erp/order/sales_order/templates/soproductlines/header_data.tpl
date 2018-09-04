{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.1 $ *}
{with model=$SOProductlineHeader legend="SOProduct Details"}
	{view_data attribute='stitem' label='Stock Item'}
	{input type='hidden'  attribute='stitem_id' }
	{view_data attribute='uom_name'}
	{view_data attribute='description'}
	{view_data attribute='ean' label="EAN"}
	{view_data attribute='product_group'}
	{input type='hidden'  attribute='prod_group_id' }
	{view_data attribute='tax_rate'}
	{view_data attribute='gl_account'}
	{view_data attribute='gl_centre'}
	{input type='hidden'  attribute='glcentre_id' }
	{view_data attribute='start_date'}
	{view_data attribute='end_date'}
{/with}