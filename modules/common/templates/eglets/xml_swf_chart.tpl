{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
	codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" 
	WIDTH="500" 
	HEIGHT="200" 
	id="charts" 
	ALIGN="">
<PARAM NAME=movie VALUE="{$smarty.const.XML_SWF_CHARTS_ROOT}charts.swf?library_path={$smarty.const.XML_SWF_CHARTS_ROOT}charts_library&xml_source={$source}">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=bgcolor VALUE=#ffffff>
<PARAM NAME=wmode VALUE=transparent>

<EMBED src="{$smarty.const.XML_SWF_CHARTS_ROOT}charts.swf?library_path={$smarty.const.XML_SWF_CHARTS_ROOT}charts_library&xml_source={$source}"
       quality=high 
       bgcolor=#ffffff  
       WIDTH="500" 
       HEIGHT="200" 
       NAME="charts" 
       ALIGN="" 
       swLiveConnect="true" 
       wmode=transparent
       TYPE="application/x-shockwave-flash" 
       PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
</EMBED>
</OBJECT>