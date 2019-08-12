<?php


use UzerpPhinx\UzerpMigration;

class AddEntityAttachmentOutputsModuleComponents extends UzerpMigration
{
    protected $module_components = [
        [
            'module' => 'common',
            'name' => 'entityattachmentoutput',
            'type' => 'M',
            'location' => 'modules/common/models/EntityAttachmentOutput.php'
        ],
        [
            'module' => 'common',
            'name' => 'entityattachmentoutputcollection',
            'type' => 'M',
            'location' => 'modules/common/models/EntityAttachmentOutputCollection.php'
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
