<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/17
 * Time: 9:20
 */

namespace app\index\service;

use think\Env;

class Encrypt
{
    private static $publicKey = null;

    private static $privateKey = null;

    private static $_instance = null;

    const MAX_ENCRYPT_LENGTH = 117;
    const MAX_DECRYPT_LENGTH = 128;

    public function __construct(string $publicKey = '', string $privateKey = '')
    {
        // 初始化 Public Key
        // 如果用户没有传入publicKey,默认使用 ./rsa_keys/rsa_public_key.pem
        self::$publicKey = !empty($publicKey) ? $publicKey : file_get_contents(
            Env::get('ENCRYPT_PUBLIC_KEY_PATH') ?? __DIR__ . '/rsa_keys/rsa_public_key.pem'
        );

        // 初始化 Private Key。
        // 如果用户没有传入privateKey,默认使用 ./rsa_keys/rsa_private_key.pem
        self::$privateKey = !empty($privateKey) ? $privateKey : @file_get_contents(
            Env::get('ENCRYPT_PRIVATE_KEY_PATH') ?? __DIR__ . '/rsa_keys/rsa_private_key.pem'
        );
    }

    public static function instance(string $publicKey = '', string $privateKey = '')
    {
        !self::$_instance && self::$_instance = new static($publicKey, $privateKey);

        return self::$_instance;
    }

    /**
     * 设置RAS Public Key
     *
     * @param $publicKey
     * @return $this
     */
    public function setPublicKey($publicKey)
    {
        self::$publicKey = $publicKey;

        return $this;
    }

    /**
     * 设置RAS Private Key
     *
     * @param $privateKey
     * @return $this
     */
    public function setPrivateKey($privateKey)
    {
        self::$privateKey = $privateKey;

        return $this;
    }

    /**
     * 获取当前使用的公钥
     *
     * @return bool|null|string
     */
    public function getPublicKey()
    {
        return self::$publicKey;
    }

    /**
     * 获取当前使用的私钥
     *
     * @return bool|null|string
     */
    public function getPrivateKey()
    {
        return self::$privateKey;
    }

    /**
     * 使用私钥对数据进行加密
     *
     * @param string $data
     * @param bool $base64
     * @return string
     */
    public function encode(string $data, $base64 = false)
    {
        $encrypted = '';
        while ($data) {
            $input = substr($data, 0, self::MAX_ENCRYPT_LENGTH);
            $data = substr($data, self::MAX_ENCRYPT_LENGTH);
            openssl_private_encrypt($input, $output, self::$privateKey);

            $encrypted .= $output;
        }

        return $base64 ? base64_encode($encrypted) : $encrypted;
    }

    /**
     * 使用公钥对数据进行解密
     *
     * @param string $data
     * @param bool $base64
     * @return string
     */
    public function decode(string $data, $base64 = false)
    {
        $decrypted = '';
        while ($data) {
            $input = substr($data, 0, self::MAX_DECRYPT_LENGTH);
            $data = substr($data, self::MAX_DECRYPT_LENGTH);
            openssl_public_decrypt($input, $output, self::$publicKey);
            $decrypted .= $output;
        }

        return $base64 ? base64_encode($decrypted) : $decrypted;
    }

}