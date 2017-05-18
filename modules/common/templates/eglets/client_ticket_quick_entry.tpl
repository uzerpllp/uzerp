{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{* Temporary style, I'll move this or do it properly, or something. *}
<style>
	#clientTicketQuickEntry textarea {
		width: 480px;
		height: 120px;
	}
	
	#clientTicketQuickEntry input#summary {
		width: 480px;
	}
	
	#clientTicketQuickEntry select {
		width: 190px;
	}
	
	#clientTicketQuickEntry div {
		float: left;
		margin-left: 10px;
		margin-top: 7px;
	}
	
	#clientTicketQuickEntry {
	}
</style>
<span id="clientTicketQuickEntry">
<form action="/?module=ticketing&controller=client&action=save" method="POST">
	<div>
		<label for="Ticket[summary]">Summary:</label><br />
		<input id="summary" type="text" name="Ticket[summary]" />
	</div>
	
	<div>
		<label for="TicketResponse[body]">Description:</label><br />
		<textarea name="TicketResponse[body]"></textarea>
	</div>
	
	<div>
		<label for="Ticket[ticket_category_id]">Category:</label><br />
		<select name="Ticket[ticket_category_id]">
			{foreach item=name key=id from=$content.categories}
			<option value="{$id}">{$name}</option>
			{/foreach}
		</select>
	</div>
	
	<div>
		<label for="Ticket[client_ticket_severity_id]">Severity:</label><br />
		<select name="Ticket[client_ticket_severity_id]">
			{foreach item=name key=id from=$content.severities}
			<option value="{$id}">{$name}</option>
			{/foreach}
		</select>
	</div>
	
	<div>
		<br />
		<input id="submit" name="saveform" value="Save" type="submit" />
	</div>
	
	<input type="hidden" name="TicketResponse[type]" value="quick" id="TicketResponse[type]">
</form>
</span>
