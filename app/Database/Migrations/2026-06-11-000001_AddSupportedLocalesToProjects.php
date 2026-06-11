<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSupportedLocalesToProjects extends Migration
{
    public function up(): void
    {
        $fields = [
            'supported_locales' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'locale_default',
            ],
        ];

        $this->forge->addColumn('projects', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('projects', ['supported_locales']);
    }
}
