<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class ImportChangesNisacon extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $sql = file_get_contents(__DIR__ . '/../changes_nisacon.sql');
        $this->schema->getConnection()->unprepared($sql);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $sql = file_get_contents(__DIR__ . '/../changes_nisacon_down.sql');
        $this->schema->getConnection()->unprepared($sql);
    }
}
