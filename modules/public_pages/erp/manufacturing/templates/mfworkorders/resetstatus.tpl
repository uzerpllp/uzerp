{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
	<p style='font-size:14px; color: red; text-align: center;'>Warning! If you reset the status of this works order you must retrieve any distributed paperwork</p><br />
	<p>&nbsp;</p><p>&nbsp;</p>
	<p style='font-size:14px; font-weight:bold; text-align: center;'>
		{link_to module=$module controller=$controller action=$action id=$id type='reset' value='Reset Works Order'}
		&nbsp;&nbsp;-&nbsp;&nbsp;
		{link_to module=$module controller=$controller action=$action id=$id type='cancel' value='Cancel'}
	</p>
{/content_wrapper}