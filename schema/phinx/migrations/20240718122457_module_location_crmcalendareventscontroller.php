<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;

final class ModuleLocationCrmcalendareventscontroller extends UzerpMigration
{
    public function up(): void
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('module_components')
            ->set('location', 'modules/public_pages/crm/controllers/CrmcalendarEventsController.php')
            ->where(['name' => 'crmcalendareventscontroller'])
            ->execute();
            }
}
