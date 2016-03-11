<?php


/**
 * Class EncryptedVarchar
 * @package EncryptAtRest\Fieldtypes
 *
 * This class wraps around a Varchar, storing the value in the database as an encrypted string in a larger varchar
 * field, and returning the decrypted value.
 */
class EncryptedVarchar extends Varchar
{
    public $isEncrypted = true;

    /**
     * @var AtRestCryptoService
     */
    protected $service;

    public function __construct($name = null)
    {
        $this->name = $name;
        $this->service = Injector::inst()->get('AtRestCryptoService');
        parent::__construct($name);
    }

    public function setValue($value, $record = null)
    {
        $value = $this->getDecryptedValue($value);
        return parent::setValue($value, $record);
    }

    public function getDecryptedValue($value)
    {
        // Test if we're actually an encrypted value;
        if (ctype_xdigit($value)) {
            return $this->service->decrypt($value);
        }
        return $value;
    }


    public function requireField()
    {
        $values = array(
            'type'  => 'text',
            'parts' => array(
                'datatype'      => 'text',
//                'precision'     => $this->service->calculateRequiredFieldSize($this->size),
                'character set' => 'utf8',
                'collate'       => 'utf8_general_ci',
                'arrayValue'    => $this->arrayValue
            )
        );

        DB::require_field($this->tableName, $this->name, $values);
    }

    public function prepValueForDB($value)
    {
        $value = parent::prepValueForDB($value);
        $ciphertext = $this->service->encrypt($value);

        return $ciphertext;
    }
}
