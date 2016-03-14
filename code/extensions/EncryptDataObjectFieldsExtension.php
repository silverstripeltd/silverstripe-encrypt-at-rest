<?php
/**
 * Copyright (c) 2016. SilverStripe Limited - www.silverstripe.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Class EncryptDataObjectFieldsExtension
 * @package EncryptAtRest\Extensions
 */
class EncryptDataObjectFieldsExtension extends DataExtension
{

    protected static $dbMapping = array();

    public function __call($name, $arguments)
    {
        return $this->getDecryptedProperty(self::$dbMapping[$name]);
    }


    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $dbFields = Config::inst()->get($this->owner->ClassName, 'db', Config::UNINHERITED);
        $changedFields = array();
        if (is_array($this->owner->getChangedFields())) {
            $changedFields = $this->owner->getChangedFields();
        }
        if(is_array($dbFields)) {
            foreach ($dbFields as $dbFieldName => $dbFieldType) {
                $field = $this->owner->dbObject($dbFieldName);
                if ($this->shouldEncrypt($changedFields, $dbFieldName, $field)) {
                    $field->prepValueForDB($this->owner->$dbFieldName);
                }
            }
        }
    }

    private function shouldEncrypt($changedFields, $dbFieldName, $field)
    {
        $return = false;
        if (($this->owner->ID === 0 || in_array($dbFieldName, $changedFields)) && $field->isEncrypted) {
            $return = true;
        }
        return $return;
    }

    public function getDecryptedProperty($property)
    {
        $field = $property[1]::create($property[0]);
        $record = $this->owner->getField($property[0]);
        $field->setValue($record, array());
        return $field->getValue();
    }

    public function allMethodNames($bool)
    {
        $methods = array();
        $classes = Config::inst()->get('EncryptDataObjectFieldsExtension', 'EncryptedDataObjects');
        if (in_array($this->owner->ClassName, $classes)) {
            $fields = Config::inst()->get($this->owner->ClassName, 'db', Config::UNINHERITED);
            foreach ($fields as $fieldName => $fieldType) {
                $reflector = Object::create_from_string($fieldType, '')->isEncrypted;
                if ($reflector === true) {
                    $callFunction = strtolower('get' . $fieldName);
                    self::$dbMapping[$callFunction] = array($fieldName, $fieldType);
                    $methods[] = strtolower('get' . $fieldName);
                }
            }
        }
        return $methods;
    }
}