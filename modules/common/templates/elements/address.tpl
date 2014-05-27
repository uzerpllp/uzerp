{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{with model=$address}
	{input type='hidden' attribute='id' value=$address->id}
	{input type='text' attribute='street1' }
	{input type='text' attribute='street2' }
	{input type='text' attribute='street3' }
	{input type='text' attribute='town' }
	{input type='text' attribute='county' }
	{input type='text' attribute='postcode' }
	{select attribute='countrycode' }
{/with}