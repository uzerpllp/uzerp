{content_wrapper}
<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$model attribute="type_code"}
            {view_data model=$model attribute="description"}
            {view_data model=$model attribute="comp_class"}
            {view_data model=$model attribute="backflush_action"}
            {view_data model=$model attribute="complete_action"}
            {view_data model=$model attribute="issue_action"}
            {view_data model=$model attribute="return_action"}
            {view_data model=$model attribute="active"}
		</dl>
	</div>
{/content_wrapper}