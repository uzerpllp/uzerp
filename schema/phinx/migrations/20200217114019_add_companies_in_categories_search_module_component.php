<?php


use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add new Companies in Categories search module componement
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddCompaniesInCategoriesSearchModuleComponent extends UzerpMigration
{
    protected $module_components = [
        [
            'module' => 'contacts',
            'name' => 'companycategorysearch',
            'type' => 'M',
            'location' => 'modules/public_pages/contacts/models/CompanyCategorySearch.php'
        ]
    ];

    public function up()
    {
        $this->addModuleComponents();
    }

    public function down()
    {
        $this->removeModuleComponents();
    }
}