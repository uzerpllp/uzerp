<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class RecentlyViewedPeopleEGlet extends SimpleListEGlet
{
    #[\Override]
    public function populate()
    {
        $pl = new PreferencePageList('recently_viewed_people' . EGS_COMPANY_ID);
        $this->contents = $pl->getPages()->toArray();
    }
}
