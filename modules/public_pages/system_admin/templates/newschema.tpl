{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller=$self.controller action="createschema"}
		<dl id="view_data_left">
			{with model=$Schema}
				<dt><label for="centres">Supported Databases</label>:</dt><dd>
					<select name="Schema[database_type]" id="Schema_database">
						{html_options options=$databases}
					</select>
				</dd>
				{input type='text' attribute="database_host" label="database_host" value=$model->defaulthost()}
				{input type='text' attribute="database_admin_username" label="database_admin_username" class='mandatory'}
				{input type='password' attribute="database_admin_password" label="database_admin_password"}
				{input type='text' attribute="database_name" label="database_name" value=$model->defaultdbname()}
				{input type='text' attribute="database_username" label="database_username" value=$model->defaultuser()}
				{input type='password' attribute="database_password" label="database_password"}
				{submit value='Create Database' name='createdb'}
				{submit value='Cancel' name='cancel'}
			{/with}
		</dl>
	{/form}
{/content_wrapper}