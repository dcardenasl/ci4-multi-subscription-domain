<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFirstNameAndLocaleToSubscribers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('subscribers', [
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'email',
            ],
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'after'      => 'first_name',
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->getPlatform() === 'SQLite3') {
            return;
        }

        $this->forge->dropColumn('subscribers', 'first_name');
        $this->forge->dropColumn('subscribers', 'locale');
    }
}
