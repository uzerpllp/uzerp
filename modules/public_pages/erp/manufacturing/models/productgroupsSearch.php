<?php

/**
 *	@author uzERP LLP
 *	@license GPLv3 or later
 *	@copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class productgroupsSearch extends BaseSearch {

	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new productgroupsSearch($defaults);
		$search->addSearchField(
			'product_group',
			'product_group',
			'begins',
			'',
			'basic'
        );
        $search->addSearchField(
			'description',
			'description',
			'contains',
			'',
			'basic'
		);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>