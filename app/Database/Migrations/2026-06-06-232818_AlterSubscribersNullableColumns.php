<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterSubscribersNullableColumns extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('subscribers', [
            'confirm_token' => [
                'name'       => 'confirm_token',
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
            ],
            'unsubscribe_token' => [
                'name'       => 'unsubscribe_token',
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
            ],
            'invitation_code' => [
                'name'       => 'invitation_code',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'confirmed_at' => [
                'name'    => 'confirmed_at',
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'unsubscribed_at' => [
                'name'    => 'unsubscribed_at',
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->getPlatform() === 'SQLite3') {
            return;
        }

        $this->forge->modifyColumn('subscribers', [
            'confirm_token' => [
                'name'       => 'confirm_token',
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
                'default'    => '',
            ],
            'unsubscribe_token' => [
                'name'       => 'unsubscribe_token',
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
                'default'    => '',
            ],
            'invitation_code' => [
                'name'       => 'invitation_code',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => '',
            ],
            'confirmed_at' => [
                'name'    => 'confirmed_at',
                'type'    => 'DATETIME',
                'null'    => false,
            ],
            'unsubscribed_at' => [
                'name'    => 'unsubscribed_at',
                'type'    => 'DATETIME',
                'null'    => false,
            ],
        ]);
    }
}
