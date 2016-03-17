<?php

class EncryptedDataObjectTest extends SapphireTest
{

    protected $extraDataObjects = array(
        'EncryptDataObject'
    );

    public function testVarchar()
    {
        $text = 'This is a random tekst';
        /** @var EncryptDataObject $object */
        $object = EncryptDataObject::create();
        $object->Text = $text;

        $ID = $object->write();
        $object->flushCache();

        /** @var EncryptDataObject $retrieved */
        $retrieved = EncryptDataObject::get()->filter(array('ID' => $ID))->first();

        $this->assertNotEquals($text, $retrieved->getField('text'));
    }

}

class EncryptDataObject extends DataObject implements TestOnly
{

    private static $db = array(
        'Text' => 'EncryptedVarchar'
    );
}