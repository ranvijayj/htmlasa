<?php

class Encryptor
{
    static $key = "\1\0\1\0\0\1\0\1\0\1\0\0\0\0\0\1\1\0\0\0\0\1\0\0\1\0\1\0\0\0\1\0\0\0\0\0\0\0\0\0";
    static $anubis = null;

    /**
     * Encrypt string
     * @param $str
     * @return string
     */
    public static function encrypt($str) {
        $cypher = self::getAnubis();
        $encrypted = $cypher->encrypt($str);
        return bin2hex($encrypted);
    }

    /**
     * Decrypt string
     * @param $str
     * @return string
     */
    public static function decrypt($str) {
        $cypher = self::getAnubis();
        $decrypted = $cypher->decrypt(self::hex2bin($str));
        return $decrypted;
    }

    /**
     * Get Anubis object
     * @return Anubis
     */
    public static function getAnubis()
    {
        if (!self::$anubis) {
            $cypher = new Anubis();
            $cypher->setKey(self::$key, true);
            self::$anubis = $cypher;
        }

        return self::$anubis;
    }

    public static function hex2bin($hexstr)
    {
        $n = strlen($hexstr);
        $sbin="";
        $i=0;
        while($i<$n) {
            $a =substr($hexstr,$i,2);
            $c = pack("H*",$a);
            if ($i==0){$sbin=$c;}
            else {$sbin.=$c;}
            $i+=2;
        }
        return $sbin;
    }
}