<?php


use Phinx\Migration\AbstractMigration;

class MfoperationColumnChanges extends AbstractMigration
{
    /*
     * Make volume_taget not null (required)
     */
    public function up()
    {
        $mfops_require_volume_target = $this->execute("ALTER TABLE mf_operations ALTER COLUMN volume_target SET NOT NULL;");
        $mfops = $this->table('mf_operations')
        ->addColumn('batch_op', 'boolean', ['default' => false])
        ->save();
    }

    /*
     * Remove requirement for volume_target to have a value
     */
    public function down() {
        $mfops_do_not_require_volume_target = $this->execute("ALTER TABLE mf_operations ALTER COLUMN volume_target DROP NOT NULL;");
        $mfops = $this->table('mf_operations')
        ->removeColumn('batch_op')
        ->save();
    }
}
