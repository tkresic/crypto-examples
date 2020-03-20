<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CryptoController extends Controller {

    const SYMMETRIC_ENCRYPTION = [
        'method' => 'AES-256-CBC',
        'length' => 32
    ];

    public function encryptSymmetric(Request $request)
    {
        // Check if string entered
        if (!strlen($request->text)) {
            $request->session()->flash('message', 'Please enter some text.');
            return redirect('/');
        }

        // The message
        $data = $request->text;

        // Generate secret key
        $secret_key = Str::random(32);

        // Save secret key in the file
        file_put_contents('secret_key.txt', $secret_key, LOCK_EX);

        // Generate an Initialization Vector (IV)
        $length = openssl_cipher_iv_length(self::SYMMETRIC_ENCRYPTION['method']);
        $iv = openssl_random_pseudo_bytes($length);

        // Encrypt data
        try {
            $encrypted = openssl_encrypt($data, self::SYMMETRIC_ENCRYPTION['method'], $secret_key, OPENSSL_RAW_DATA, $iv);
        } catch (\Exception $e) {
            return $e;
        }

        // Append the IV to the encrypted cipher text
        $cipher_text = base64_encode($encrypted) . '|' . base64_encode($iv);

        // Save cipher text in the file
        file_put_contents('cipher_text.txt', $cipher_text, LOCK_EX);

        // Flash to session
        $request->session()->flash('message', "Message encrypted<br>$cipher_text");

        return redirect('/');
    }

    public function decryptSymmetric(Request $request)
    {
        // Check if file exists
        if (!file_exists('cipher_text.txt') || !file_exists('secret_key.txt')) {
            $request->session()->flash('message', 'Required files don\'t exist.');
            return redirect('/');
        }

        // Get cipher text
        $cipher_text = file_get_contents('cipher_text.txt');

        // Extract the IV and the cipher text from the encoded string
        list($data, $iv) = explode('|', $cipher_text);
        $iv = base64_decode($iv);

        // Get secret key
        $secret_key = file_get_contents('secret_key.txt');

        try {
            $decrypted = openssl_decrypt($data, self::SYMMETRIC_ENCRYPTION['method'], $secret_key, 0, $iv);
        } catch (\Exception $e) {
            return $e;
        }

        // Flash to session
        $request->session()->flash('message', "Message decrypted<br>$decrypted");

        return redirect('/');
    }

    public function authenticate(Request $request)
    {
        // Check if file exists
        if (!file_exists('cipher_text.txt')) {
            $request->session()->flash('message', 'Required files don\'t exist.');
            return redirect('/');
        }

        // Generate authentication key
        $authentication_key = openssl_random_pseudo_bytes(self::SYMMETRIC_ENCRYPTION['length'], $strong);

        file_put_contents('authentication_key.txt', base64_encode($authentication_key), LOCK_EX);

        // Get encrypted message
        $encrypted = base64_decode(file_get_contents('cipher_text.txt'));

        // Authentication
        $authentication = hash_hmac('sha256', $encrypted, $authentication_key, true);

        // Concatenate authentication with encrypted message
        $authentication_encrypted = base64_encode($authentication . $encrypted);

        // Save to file
        file_put_contents('authentication_encrypted.txt', $authentication_encrypted, LOCK_EX);

        // Flash to session
        $request->session()->flash('message', "Message hashed<br>$authentication_encrypted");

        return redirect('/');
    }

    public function verifyAuthentication(Request $request)
    {
        // Check if file exists
        if (!file_exists('authentication_encrypted.txt') || !file_exists('authentication_key.txt')) {
            $request->session()->flash('message', 'Required files don\'t exist.');
            return redirect('/');
        }

        // Get encrypted message
        $authentication_encrypted = base64_decode(file_get_contents('authentication_encrypted.txt'));

        // Get authentication key
        $authentication_key = base64_decode(file_get_contents('authentication_key.txt'));

        // Get authentication (first 32 characters)
        $authentication = substr($authentication_encrypted, 0, self::SYMMETRIC_ENCRYPTION['length']);

        // Get encrypted message (the rest of the string)
        $encrypted = substr($authentication_encrypted, self::SYMMETRIC_ENCRYPTION['length']);

        // Authenticate again
        $actual_authentication = hash_hmac('sha256', $request->modified ? $encrypted . ' appended content.' : $encrypted, $authentication_key, true);

        // Flash to session
        $request->session()->flash('message', hash_equals($authentication, $actual_authentication) ?
            'Verification successful.' :
            'Verification failed.'
        );

        return redirect('/');
    }

    public function encryptAsymmetric(Request $request)
    {
        // Check if string entered
        if (!strlen($request->text)) {
            $request->session()->flash('message', 'Please enter some text.');
            return redirect('/');
        }

        $data = $request->text;

        // Create the private and public key
        $res = openssl_pkey_new([
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096, // 512 bytes
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);

        // Extract the private key from $res to $private_key
        openssl_pkey_export($res, $private_key);

        // Extract the public key from $res to $public_key
        $public_key = openssl_pkey_get_details($res)['key'];

        // Encrypt the data to $encrypted using the public key
        openssl_public_encrypt($data, $encrypted, $public_key);

        // Make content viewable
        $encrypted_message = base64_encode($encrypted);

        // Save encrypted message, public and the private key
        file_put_contents('encrypted_message.txt', $encrypted_message, LOCK_EX);
        file_put_contents('public_key.txt', $public_key, LOCK_EX);
        file_put_contents('private_key.txt', $private_key, LOCK_EX);

        // Flash to session
        $request->session()->flash('message', "Message encrypted<br>$encrypted_message");

        return redirect('/');
    }

    public function decryptAsymmetric(Request $request)
    {
        // Check if file exists
        if (!file_exists('private_key.txt') || !file_exists('encrypted_message.txt')) {
            $request->session()->flash('message', 'Required files don\'t exist.');
            return redirect('/');
        }

        // Get private key
        $private_key = file_get_contents('private_key.txt');

        // Get encrypted message
        $encrypted = file_get_contents('encrypted_message.txt');

        // Decrypt the data using the private key and store the results in $decrypted
        openssl_private_decrypt(base64_decode($encrypted), $decrypted, $private_key);

        // Save decrypted message
        file_put_contents('decrypted_message.txt', $decrypted, LOCK_EX);

        // Flash to session
        $request->session()->flash('message', "Message decrypted<br>$decrypted");

        return redirect('/');
    }

    public function sign(Request $request)
    {
        // Check if file exists
        if (!file_exists('encrypted_message.txt') || !file_exists('private_key.txt')) {
            $request->session()->flash('message', 'Required files don\'t exist.');
            return redirect('/');
        }

        // Get encrypted message
        $data = file_get_contents('encrypted_message.txt');

        // Get private key
        $private_key = file_get_contents('private_key.txt');

        // Create signature
        openssl_sign($data, $raw_signature, $private_key, OPENSSL_ALGO_SHA512);

        // Make content viewable
        $signature = base64_encode($raw_signature);

        // Save the signature
        file_put_contents('signature.txt', $signature, LOCK_EX);

        // Flash to session
        $request->session()->flash('message', "Encrypted data signed with signature: <br>$signature");

        return redirect('/');
    }

    public function verifySignature(Request $request)
    {
        // Check if file exists
        if (!file_exists('encrypted_message.txt') || !file_exists('signature.txt') || !file_exists('public_key.txt')) {
            $request->session()->flash('message', 'Required files don\'t exist.');
            return redirect('/');
        }

        // Get data
        $data = file_get_contents('encrypted_message.txt');

        // Get signature
        $signature = file_get_contents('signature.txt');

        // Get public key
        $public_key = file_get_contents('public_key.txt');

        // Verify signature
        $result = openssl_verify($request->modified ? $data . ' appended content.' : $data, base64_decode($signature), $public_key, OPENSSL_ALGO_SHA512);

        // Flash to session
        $request->session()->flash('message', self::getSignatureResult($result));

        return redirect('/');
    }


    public static function getSignatureResult($result)
    {
        if (!$result) return 'Verification of the digital signature failed.';
        else if ($result == -1) return 'An error occurred while verifying the digital signature.';
        else return 'Verification successful.';
    }
}
