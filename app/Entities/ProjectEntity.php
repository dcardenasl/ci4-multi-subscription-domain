<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ProjectEntity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'project_key' => 'string',
        'is_active' => 'bool',
        'smtp_provider' => 'string',
        'smtp_host' => 'string',
        'smtp_port' => 'int',
        'smtp_user' => 'string',
        'smtp_pass_encrypted' => 'string',
        'smtp_crypto' => 'string',
        'smtp_from_name' => 'string',
        'smtp_from_email' => 'string',
        'double_opt_in_enabled' => 'bool',
        'double_opt_in_template_id' => 'int',
        'welcome_template_id' => 'int',
        'locale_default' => 'string',
        'recaptcha_site_key' => 'string',
        'recaptcha_secret_key' => 'string',
        'supported_locales' => 'string',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function setSmtpPassEncrypted(string $pass): self
    {
        if ($pass !== '') {
            $encrypter = \Config\Services::encrypter();
            $this->attributes['smtp_pass_encrypted'] = bin2hex($encrypter->encrypt($pass));
        }
        return $this;
    }

    public function getSmtpPassDecrypted(): string
    {
        $encrypted = $this->attributes['smtp_pass_encrypted'] ?? '';
        if ($encrypted === '') {
            return '';
        }
        try {
            $encrypter = \Config\Services::encrypter();
            return $encrypter->decrypt((string) hex2bin($encrypted));
        } catch (\Throwable $e) {
            // Fallback in case it wasn't hex-encoded/encrypted
            return $encrypted;
        }
    }
}
