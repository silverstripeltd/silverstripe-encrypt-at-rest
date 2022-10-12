<?php

namespace Madmatt\EncryptAtRest\FieldType;

use Exception;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBEnum;
use SilverStripe\ORM\ArrayLib;
use Madmatt\EncryptAtRest\AtRestCryptoService;

/**
 * Class EncryptedEnum
 * @package EncryptAtRest\Fieldtypes
 *
 * This class wraps around a Enum, storing the value in the database as an encrypted string in a varchar field, but
 * returning it to SilverStripe as a decrypted Enum object.
 */
class EncryptedEnum extends DBEnum
{
    /**
     * @var AtRestCryptoService
     */
    protected $service;

    public function __construct($name = null, $enum = null, $default = 0, $options = [])
    {
        parent::__construct($name, $enum, $default, $options);
        $this->service = Injector::inst()->get(AtRestCryptoService::class);
    }

    public function setValue($value, $record = null, $markChanged = true)
    {
        if (is_array($record) && array_key_exists($this->name, $record) && $value === null) {
            $this->value = $record[$this->name];
        } elseif (is_object($record) && property_exists($record, $this->name) && $value === null) {
            $key = $this->name;
            $this->value = $record->$key;
        } else {
            $this->value = $value;
        }
    }

    public function getDecryptedValue($value)
    {
        // Test if we're actually an encrypted value;
        if (is_string($value) && ctype_xdigit($value) && strlen($value) > 130) {
            try {
                return $this->service->decrypt($value);
            } catch (Exception $e) {
                // We were unable to decrypt. Possibly a false positive, but return the unencrypted value
                return $value;
            }
        }
        return $value;
    }

    public function getValue()
    {
        return $this->getDecryptedValue($this->value);
    }

    public function requireField()
    {
        $values = array(
            'type'  => 'text',
            'parts' => array(
                'datatype'   => 'text',
                'null'       => 'not null',
                'arrayValue' => $this->arrayValue
            )
        );

        DB::require_field($this->tableName, $this->name, $values);
    }

    public function prepValueForDB($value)
    {
        $value = parent::prepValueForDB($value);
        $ciphertext = $this->service->encrypt($value);
        $this->value = $ciphertext;
        return $ciphertext;
    }

    /**
     * Returns the values of this enum as an array, suitable for insertion into
     * a {@link DropdownField}
     *
     * @param boolean
     *
     * @return array
     */
    public function enumValues($hasEmpty = true) {
        $this->enum = array();
        return ($hasEmpty)
            ? array_merge(array('' => ''), ArrayLib::valuekey($this->enum))
            : ArrayLib::valuekey($this->enum);
    }
}
