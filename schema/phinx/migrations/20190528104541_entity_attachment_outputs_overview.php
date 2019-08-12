<?php


use UzerpPhinx\UzerpMigration;

class EntityAttachmentOutputsOverview extends UzerpMigration
{

    private $view_name = 'entity_attachment_outputs_overview';

    public function up()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW entity_attachment_outputs_overview AS 
select file.id, file.name, file.type, eao.tag, eao.print_order, ea.data_model, ea.entity_id, eao.entity_attachment_id
from entity_attachment_outputs eao
join entity_attachments ea on ea.id = eao.entity_attachment_id
join file on file.id = ea.file_id
VIEW;
    $this->query($view);
    $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    }

    public function down()
    {
        $this->query("DROP VIEW {$this->view_name}");
    }
}
