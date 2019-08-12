<?php


use UzerpPhinx\UzerpMigration;

/**
 * Make the sales history summary uzLET double width
 */
class ExpandInvoiceSummaryUzlet extends UzerpMigration
{
    public function up()
    {
        $uzlets = [
            'SalesHistorySummary' => ['2', 'info'],
        ];

        foreach ($uzlets as $name => $options){
            $this->query("UPDATE uzlets SET size = {$options[0]}, type = '{$options[1]}' WHERE name = '{$name}';");
        }
    }
}
