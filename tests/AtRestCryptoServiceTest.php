<?php

namespace Madmatt\EncryptAtRest\Tests;

use Madmatt\EncryptAtRest\AtRestCryptoService;
use SilverStripe\Assets\File;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class AtRestCryptoServiceTest extends SapphireTest
{
    public function testEncrypt()
    {
        /** @var AtRestCryptoService $service */
        $service = Injector::inst()->get(AtRestCryptoService::class);

        $originalText = 'This is a test string';
        $encrypted1 = $service->encrypt($originalText);
        $encrypted2 = $service->encrypt($originalText);

        $this->assertNotEquals($originalText, $encrypted1);
        $this->assertNotEquals($originalText, $encrypted2);
        $this->assertNotEquals($encrypted1, $encrypted2); // This is technically testing a property of the defuse lib
    }

    public function testDecrypt()
    {
        /** @var AtRestCryptoService $service */
        $service = Injector::inst()->get(AtRestCryptoService::class);

        $expectedInt = 200;
        $expectedFloat = 295.285;
        $expectedString = 'This is a test string';

        $encrypted1Int = $service->encrypt($expectedInt);
        $encrypted1Float = $service->encrypt($expectedFloat);
        $encrypted1String = $service->encrypt($expectedString);

        $this->assertEquals($expectedInt, $service->decrypt($encrypted1Int));
        $this->assertEquals($expectedFloat, $service->decrypt($encrypted1Float));
        $this->assertEquals($expectedString, $service->decrypt($encrypted1String));

        // Ensure that when we encrypt the same value twice, we can still decrypt
        $encrypted2Int = $service->encrypt($expectedInt);
        $encrypted2Float = $service->encrypt($expectedFloat);
        $encrypted2String = $service->encrypt($expectedString);

        $this->assertNotEquals($encrypted1Int, $encrypted2Int);
        $this->assertNotEquals($encrypted1Float, $encrypted2Float);
        $this->assertNotEquals($encrypted1String, $encrypted2String);

        $this->assertEquals($expectedInt, $service->decrypt($encrypted2Int));
        $this->assertEquals($expectedFloat, $service->decrypt($encrypted2Float));
        $this->assertEquals($expectedString, $service->decrypt($encrypted2String));
    }

    public function testEncryptFile()
    {
        $originalText = 'This is a test file';
        $originalFilename = 'test-filename.txt';

        $file = File::create();
        $file->setFromString($originalText, $originalFilename);
        $file->write();
        $oldFilename = $file->getFilename();
        $this->assertFileExists($file->File->getSourceURL());

        /** @var AtRestCryptoService $service */
        $service = Injector::inst()->get(AtRestCryptoService::class);
        $encryptedFile = $service->encryptFile($file);

        // Confirm the old file has been deleted
        $this->assertFileNotExists($oldFilename);

        // Confirm the new file exists
        $this->assertFileExists($encryptedFile->getFilename());

        // Confirm the new file is encrypted
        $this->assertStringStartsWith('def', $encryptedFile->File->getString());
        $this->assertNotEquals($originalText, $encryptedFile->File->getString());
    }
}
