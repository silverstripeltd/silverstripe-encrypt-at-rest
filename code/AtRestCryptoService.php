<?php


use Defuse\Crypto\Crypto;
use Defuse\Crypto\File;
use Defuse\Crypto\Key;

class AtRestCryptoService {
    /**
     * @param $raw
     * @param null $key
     * @return string
     * @throws \Defuse\Crypto\Exception\CannotPerformOperationException
     * @throws \Defuse\Crypto\Exception\CryptoTestFailedException
     */
    public function encrypt($raw, $key = null) {
        $key = $this->getKey($key);
        return Crypto::Encrypt($raw, $key);
    }

    public function decrypt($ciphertext, $key = null) {
        $key = $this->getKey($key);
        return Crypto::Decrypt($ciphertext, $key);
    }

    public function encryptFile($inputFilename, $key = null) {
        $key = $this->getKey($key);
        $encryptedFilename = str_replace('.txt', '.enc', $inputFilename);
        try{
            File::encryptFile($inputFilename, $encryptedFilename, $key);
            return $encryptedFilename;
        } catch (Exception $e) {
            SS_Log::log(sprintf('Encryption exception while parsing "%s": %s', $inputFilename, $e->getMessage()), SS_Log::ERR);
            return false;
        }

    }

    public function decryptFile($inputFilename, $key = null) {
        $key = $this->getKey($key);
        $decryptedFilename = str_replace('.enc', '.txt', $inputFilename);
        try{
            File::decryptFile($inputFilename, $decryptedFilename, $key);
            return $decryptedFilename;
        } catch (Exception $e) {
            SS_Log::log(sprintf('Encryption exception while parsing "%s": %s', $inputFilename, $e->getMessage()), SS_Log::ERR);
            return false;
        }
    }

    public function getKey($rawKey) {
        // If this is already a \Defuse\Crypto\Key object, just return it
        if($rawKey instanceof Key) {
            return $rawKey;
        }

        if($rawKey === null && defined('ENCRYPT_AT_REST_KEY')) {
            // Retrieve key from _ss_env, if set
            $rawKey = ENCRYPT_AT_REST_KEY;
        }

        if($rawKey === null) {
            throw new \InvalidArgumentException('Can\'t encrypt without a key. Define ENCRYPT_AT_REST_KEY, or pass the $key argument.');
        }

        $key = Key::LoadFromAsciiSafeString($rawKey);

        return $key;
    }
}
