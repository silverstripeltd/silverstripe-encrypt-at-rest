# silverstripe-encrypt-at-rest

This module allows data to be encrypted in the database, but be decrypted when extracted from the database, using a
secret key (hopefully) known only by the web server.

*Note:* This does not provide significant protection except in the case of database compromise. It should be used as
part of a layered security strategy. This is because the key is still available on the web server, so if remote code
execution is achieved by an attacker, they will be able to read both the database *and* the encryption key, thereby
decrypting the content.

*Note:* This module is not yet ready for real use, it's currently v0.0.1 material.

## Usage

TODO
