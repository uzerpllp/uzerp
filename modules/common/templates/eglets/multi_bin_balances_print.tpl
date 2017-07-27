{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
<div id="eglet-manufacturing-multi_bin_balances_print">
	<form action="/?module=manufacturing&controller=WHLocations&action=printMultipleBalance" method="POST">
		<input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}" />
		<dl id="view_data_left" style="width: 240px;">
			<p>
				<label for="WHStore_store">Store:</label><br />
				<select name="WHStore_store" id="WHStore_store" style="width: 225px;" >
					{foreach item=name key=id from=$content.whstore}
					<option value="{$id}">{$name}</option>
					{/foreach}
				</select>
			</p>
			<p>
				<label for="WHLocation_location">Location:</label><br />
				<select name="WHLocation_location" id="WHLocation_location" style="width: 225px;" ></select>
			</p>
			<p><br />Select the bins from the right</p>
			<p>
				<br />
				<input id="submit" name="saveform" value="Print" type="submit" />
			</p>
		</dl>
		<dl id="view_data_right" style="width: 240px;">
			<p>
				<label for="WHBin_bins">Bins:</label><br />
				<select name="WHBin_bins[]" id="WHBin_bins" multiple="multiple" style="width: 235px; height: 185px;">
				</select>
			</p>

		</dl>
	</form>
	<script type="text/javascript">
		$(document).ready(function(){
			legacyForceChange('#WHStore_store');
		});
	</script>
</div>