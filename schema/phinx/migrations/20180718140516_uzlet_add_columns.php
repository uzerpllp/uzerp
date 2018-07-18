<?php


use Phinx\Migration\AbstractMigration;

/**
 * Add size and type columns to uzLETs
 * 
 * size = grid span of item on dashboard.
 * type = indication of usage, e.g. 'info' for a graph or list,
 *   'action' for an item that performs dome action for the user.
 */
class UzletAddColumns extends AbstractMigration
{
    public function down()
    {
        // Drop and recreate the collection view
        $view = <<<VIEW
CREATE VIEW uzlet_modules_overview AS 
SELECT u.id,
u.name,
u.title,
u.preset,
u.enabled,
u.dashboard,
u.uses,
u.usercompanyid,
u.created,
u.createdby,
u.alteredby,
u.lastupdated,
um.module_id,
m.name AS module
FROM uzlets u
LEFT JOIN uzlet_modules um ON u.id = um.uzlet_id
LEFT JOIN modules m ON m.id = um.module_id;
VIEW;
        
        $this->query('DROP VIEW uzlet_modules_overview');
        $this->query($view);
        $this->query('ALTER TABLE uzlet_modules_overview OWNER TO "www-data";');

        // Drop the table columns
        $table = $this->table('uzlets');
        $table->removeColumn('size');
        $table->removeColumn('type');
    }


    public function up()
    {
        // Add table columns
        $table = $this->table('uzlets');
        $table->addColumn('size', 'integer', array('null' => true,));
        $table->addColumn('type', 'string', array('null' => true,));
        $table->save();

        // Update the collection view
        $view = <<<VIEW
CREATE VIEW uzlet_modules_overview AS 
SELECT u.id,
u.name,
u.title,
u.preset,
u.enabled,
u.size,
u.type,
u.dashboard,
u.uses,
u.usercompanyid,
u.created,
u.createdby,
u.alteredby,
u.lastupdated,
um.module_id,
m.name AS module
FROM uzlets u
LEFT JOIN uzlet_modules um ON u.id = um.uzlet_id
LEFT JOIN modules m ON m.id = um.module_id;
VIEW;

        $this->query('DROP VIEW uzlet_modules_overview');
        $this->query($view);
        $this->query('ALTER TABLE uzlet_modules_overview OWNER TO "www-data";');

        // Load data into the new columns
        //      'uzLETname' => [size(2 for double 'eglet'), type(action|info)]
        $uzlets = [
            'WOrdersBookProductionNewEGlet' => [2, 'action'],
            'MultiBinBalancesPrintEGlet' => [2, 'action'],
            'TopSalesInvoicesEGlet' => [2, 'info'],
            'SalesHistorySummary' => [2, 'info'],
            'PPOverdueEGlet' => [2, 'info'],
            'unpostedJournalsUZlet' => [2, 'info'],
            'templateJournalsUZlet' => [2, 'info'],
            'OverCreditLimitEGlet' => [2, 'info'],
            'POQueryInvoicesEGlet' => [2, 'info'],
            'POrdersNotAcknowledgedEGlet' => [2, 'info'],
            'POrdersAuthRequisitionEGlet' => [2, 'info'],
            'POrdersOverdueEGlet' => [2, 'info'],
            'POrdersDueTodayEGlet' => [2, 'info'],
            'POoverdueInvoicesEGlet' => [2, 'info'],
            'POrdersNoAuthUserEGlet' => [2, 'info'],
            'PriceCheckuzLET' => [2, 'action'],
            'SOrdersItemSummaryEGlet' => [2, 'info'],
            'SOrdersOverdueEGlet' => [2, 'info'],
            'SOrdersNotInvoicedUZlet' => [2, 'info'],
            'TopSalesOrdersEGlet' => [2, 'info'],
            'ClientTicketQuickEntryEGlet' => [2, 'action'],
            'ExpensesWaitingAuthUZlet' => [2, 'info'],
            'HolidaysWaitingAuthUZlet' => [2, 'info'],
            'ExpensesWaitingPaymentUZlet' => [2, 'info'],
            'RecentlyViewedCompaniesEGlet' => ['NULL', 'info'],
            'RecentlyViewedPeopleEGlet' => ['NULL', 'info'],
            'RecentlyViewedLeadsEGlet' => ['NULL', 'info'],
            'RecentlyAddedCompaniesEGlet' => ['NULL', 'info'],
            'RecentlyAddedLeadsEGlet' => ['NULL', 'info'],
            'CompaniesAddedTodayEGlet' => ['NULL', 'info'],
            'LeadsAddedTodayEGlet' => ['NULL', 'info'],
            'OpportunitiesBySourceGrapher' => [2, 'info'],
            'OpportunitiesHistoryGrapher' => [2, 'info'],
            'OpenOpportunitiesEGlet' => ['NULL', 'info'],
            'OpportunitiesWeeklyByStatusGrapher' => [2, 'info'],
            'OpportunitiesMonthlyByStatusGrapher' => [2, 'info'],
            'OpportunitiesQuarterlyByStatusGrapher' => [2, 'info'],
            'OpportunitiesYearlyByStatusGrapher' => [2, 'info'],
            'SalesTeamYearlySummaryEGlet' => ['NULL', 'info'],
            'SalesTeamMonthlySummaryEGlet' => ['NULL', 'info'],
            'SalesTeamWeeklySummaryEGlet' => ['NULL', 'info'],
            'CurrentActivitiesEGlet' => ['NULL', 'info'],
            'CurrentProjectsEGlet' => ['NULL', 'info'],
            'CurrentTasksEGlet' => ['NULL', 'info'],
            'LoggedHoursPerWeekEGlet' => ['NULL', 'info'],
            'NewIssuesEGlet' => ['NULL', 'info'],
            'MyCurrentIssuesEGlet' => ['NULL', 'info'],
            'EquipmentUtilisationEGlet' => ['NULL', 'info'],
            'MyWebsitesEGlet' => ['NULL', 'info'],
            'CompanySelectorEGlet' => ['NULL', 'info'],
            'TicketsWeeklyByStatusGrapher' => [2, 'info'],
            'TicketsWeeklyByPriorityGrapher' => [2, 'info'],
            'TicketsWeeklyBySeverityGrapher' => [2, 'info'],
            'MyTicketsEGlet' => ['NULL', 'info'],
            'UnassignedTicketsEGlet' => ['NULL', 'info'],
            'CustomerServiceGrapher' => [2, 'info'],
            'WOrdersPrintPaperworkNewEGlet' => ['NULL', 'action'],
            'WHActionsEGlet' => ['NULL', 'action'],
            'WOrdersBackflushErrorsEGlet' => ['NULL', 'info'],
            'agedCreditorsSummaryEGlet' => ['NULL', 'info'],
            'POrdersReceivedValueEGlet' => ['NULL', 'info'],
            'SalesHistoryGrapher' => [2, 'info'],
            'AccountsOnStopEGlet' => ['NULL', 'info'],
            'OverDueAccountsEGlet' => ['NULL', 'info'],
            'agedDebtorsSummaryEGlet' => ['NULL', 'info'],
            'sales_orders_summary' => ['NULL', 'info'],
            'sales_quotes_summary_eglet' => ['NULL', 'info'],
            'sales_orders_item_summary' => ['NULL', 'info'],
            'BankAccountsSummary' => ['NULL', 'info'],
            'ModuleDocumentsUZlet' => ['NULL', 'info']
        ];

        foreach ($uzlets as $name => $options){
            $this->query("UPDATE uzlets SET size = {$options[0]}, type = '{$options[1]}' WHERE name = '{$name}';");
        }
    }
}
