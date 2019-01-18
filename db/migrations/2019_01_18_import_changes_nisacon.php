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
        $sql = file_get_contents(__DIR__ . '/../changed_nisacon.sql');
        $this->schema->getConnection()->unprepared($sql);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->getConnection()->unprepared('
            ALTER TABLE `users`            
                DROP COLUMN `street`,
                DROP COLUMN `zip_code`,
                DROP COLUMN `emergency_contact`,
                DROP COLUMN `emergency_contact_phone`,
                DROP COLUMN `allergies`,
                DROP COLUMN `medicines`,
                DROP COLUMN `date_of_birth`;
        ');
    }
}
