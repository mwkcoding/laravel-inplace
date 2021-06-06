<?php

namespace devsrv\inplace;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use devsrv\inplace\Exceptions\DecryptException as InplaceDecryptException;

class Helper {
    public static function decrypt(string $encrypted) {
        $decrypted = null;

        try {
            $decrypted = Crypt::decryptString($encrypted);
        } catch (DecryptException $e) {
            throw new InplaceDecryptException('Inplace encountered corrupt data');
        }

        return $decrypted;
    }
}