<?php

namespace EncryptAtRest\FieldTypes;


use \DB,
    \Int,
    EncryptAtRest\AtRestCryptoService;

/**
 * Class EncryptedDatetime
 * @package EncryptAtRest\Fieldtypes
 *
 * This class wraps around a SS_Datetime, storing the value in the database as an encrypted string in a varchar field, but
 * returning it to SilverStripe as a decrypted SS_Datetime object.
 */
class EncryptedDatetime extends SS_Datetime
{

    protected $service;

    public function __construct($name = null) {
        $this->name = $name;
        $this->service = Injector::inst()->get('EncryptAtRest\AtRestCryptoService');
        parent::__construct();
    }

    public function getValue()
    {
        $value = $this->value;
        return $this->service->decrypt($value);
    }

    public function requireField()
    {
        $values = array(
            'type'  => 'varchar',
            'parts' => array(
                'datatype'   => 'SS_Datetime',
                'precision'  => 255,
                'null'       => 'not null',
                'default'    => $this->defaultVal,
                'arrayValue' => $this->arrayValue
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