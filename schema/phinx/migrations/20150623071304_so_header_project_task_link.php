<?php
use Phinx\Migration\AbstractMigration;

/**
 * Phinx Migration
 *
 * Update Sales Invoice Header table
 *
 * Add project_id, task_id columns and relationships to
 * enable a link between a sales invoice and a project/task
 *
 * @author uzERP LLP
 * @license GPLv3 or later
 * @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class SoHeaderProjectTaskLink extends AbstractMigration
{

    public function change()
    {
        // save the dependencies for so_header
        $this->query("select deps_save_and_drop_dependencies('public', 'so_header')");

        // update so_header to accept the projects and tasks
        $siheader = $this->table('so_header');
        $siheader->addColumn('project_id', 'biginteger', array(
            'null' => true
        ))
            ->addForeignKey('project_id', 'projects', 'id')
            ->addColumn('task_id', 'biginteger', array(
            'null' => true
        ))
            ->addForeignKey('task_id', 'tasks', 'id')
            ->save();

        // restore any dependencies
        $this->query("select deps_restore_dependencies('public', 'so_header')");
    }
}
