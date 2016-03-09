<?php

namespace \EncryptAtRest\FieldTypes;

use \DB,
    \Varchar;

/**
 * Class EncryptedVarchar
 * @package EncryptAtRest\Fieldtypes
 *
 * This class wraps around a Varchar, storing the value in the database as an encrypted string in a larger varchar
 * field, and returning the decrypted value.
 */
class EncryptedVarchar extends Varchar
{

    protected $service;

    public function __construct($name = null) {
        $this->name = $name;
        $this->service = Injector::inst()->get('EncryptAtRest\AtRestCryptoService');
        parent::__construct($name);
    }

    public function getValue()
    {
        return $this->service->decrypt($this->value);
    }

    public function requireField()
    {
        $values = array(
            'type'  => 'varchar',
            'parts' => array(
                'datatype'      => 'varchar',
                'precision'     => $this->service->calculateRequiredFieldSize($this->size),
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
