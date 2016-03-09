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
    public function getValue()
    {
        return Injector::inst()->get('EncryptAtRest\AtRestCryptoService')->decrypt($this->value);
    }

    public function requireField()
    {
        $service = Injector::inst()->get('EncryptAtRest\AtRestCryptoService');
        $values = array(
            'type'  => 'varchar',
            'parts' => array(
                'datatype'      => 'varchar',
                'precision'     => $service->calculateRequiredFieldSize($this->size),
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
        $encryptor = new AtRestCryptoService();
        $ciphertext = $encryptor->encrypt($value);

        return $ciphertext;
    }
}
