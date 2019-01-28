<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class PublicTeamContactOption extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->getConnection()->unprepared("ALTER TABLE AngelTypes ADD COLUMN public_contact TINYINT NOT NULL DEFAULT 1;");
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->getConnection()->unprepared("ALTER TABLE AngelTypes DROP COLUMN public_contact;");
    }
}