<?php

/**
 * Test encryption on dataobjects.
 * Class EncryptedDataObjectTest
 */
class EncryptedDataObjectTest extends SapphireTest
{

    protected $extraDataObjects = array(
        'EncryptDataObject'
    );

    public function testVarcharNotEncryptedBeforeWrite()
    {
        $text = 'This is a random tekst';
        /** @var EncryptDataObject $object */
        $object = EncryptDataObject::create();
        $object->UnencryptedText = $text;
        $this->assertEquals($text, $object->getField('UnencryptedText'));
    }


    public function testVarcharEncryptedInFieldAfterWrite()
    {
        $text = 'This is a random tekst';
        /** @var EncryptDataObject $object */
        $object = EncryptDataObject::create();
        $object->EncryptedText = $text;
        $object->UnencryptedText = $text . " unencrypted";
        $ID = $object->write();
        $object->flushCache();

        /** @var EncryptDataObject $retrieved */
        $retrieved = EncryptDataObject::get()->filter(array('ID' => $ID))->first();

        $this->assertEquals('EncryptDataObject', $retrieved->ClassName);
        $this->assertNotEquals($text, $retrieved->getField('EncryptedText'));
        $this->assertEquals($text . " unencrypted", $retrieved->getField('UnencryptedText'));
        $this->assertNotEquals($retrieved->EncryptedText, $retrieved->getField('EncryptedText'));
    }


    public function testVarcharDecryptedOnGet()
    {
        $text = 'This is a random tekst';
        /** @var EncryptDataObject $object */
        $object = EncryptDataObject::create();
        $object->EncryptedText = $text;
        $object->UnencryptedText = $text . " unencrypted";

        $ID = $object->write();
        $object->flushCache();

        /** @var EncryptDataObject $retrieved */
        $retrieved = EncryptDataObject::get()->filter(array('ID' => $ID))->first();

        $this->assertEquals($text, $retrieved->EncryptedText);
        $this->assertEquals($text . " unencrypted", $retrieved->UnencryptedText);
    }
}

/**
 * Class EncryptDataObject
 * @property string EncryptedText
 * @property string UnencryptedText
 */
class EncryptDataObject extends DataObject implements TestOnly
{

    private static $db = array(
        'EncryptedText'   => 'EncryptedVarchar',
        'UnencryptedText' => 'Varchar(255)'
    );
}