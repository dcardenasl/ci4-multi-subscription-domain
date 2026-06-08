<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTemplateRelationsToProjects extends Migration
{
    public function up(): void
    {
        $fields = [
            'double_opt_in_template_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'double_opt_in_enabled',
            ],
            'welcome_template_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'double_opt_in_template_id',
            ],
        ];

        $this->forge->addColumn('projects', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('projects', ['double_opt_in_template_id', 'welcome_template_id']);
    }
}
