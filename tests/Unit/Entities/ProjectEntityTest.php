<?php

declare(strict_types=1);

namespace Tests\Unit\Entities;

use App\Entities\ProjectEntity;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ProjectEntityTest extends CIUnitTestCase
{
    public function testEncryptionAndDecryption(): void
    {
        $entity = new ProjectEntity();
        $plainPassword = 'super-secret-password-123';

        $entity->smtp_pass_encrypted = $plainPassword;

        $this->assertNotEmpty($entity->smtp_pass_encrypted);
        $this->assertNotEquals($plainPassword, $entity->smtp_pass_encrypted);
        $this->assertEquals($plainPassword, $entity->getSmtpPassDecrypted());
    }

    public function testFallbackWhenNotEncrypted(): void
    {
        $entity = new ProjectEntity();

        $ref = new \ReflectionClass($entity);
        $prop = $ref->getProperty('attributes');
        $prop->setAccessible(true);
        $attrs = $prop->getValue($entity);
        $attrs['smtp_pass_encrypted'] = 'plain-old-password';
        $prop->setValue($entity, $attrs);

        // Since it's not a valid encrypted string, it should fall back to plain
        $this->assertEquals('plain-old-password', $entity->getSmtpPassDecrypted());
    }
}
