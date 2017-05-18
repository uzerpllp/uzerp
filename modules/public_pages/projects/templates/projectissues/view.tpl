{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		{with model=$ProjectIssueHeader}
			<dt class="heading">Issue Header</dt>
			{view_data attribute="title"}
			{view_data attribute="project"}
			{view_data attribute="status"}
		{/with}
		</dl>
		<div id="view_data_bottom">
			<table id="order_lines" class="datagrid">
				<thead>
					<tr>
						<th>Title</th>
						<th>Location</th>
						<th>Created</th>
						<th>Completed</th>
						<th>Completed By</th>
					</tr>
				</thead>
				{foreach name=lines item=line from=$issue_lines}
					<tr>
						<td>{link_to module="projects" controller="projectissuelines" action="edit" id=$line->id value=$line->title}</td>
						<td>{$line->location}</td>
						<td>{$line->created|un_fix_date}</td>
						<td>{$line->completed|un_fix_date}</td>
						<td>{$line->completed_by}</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
{/content_wrapper}