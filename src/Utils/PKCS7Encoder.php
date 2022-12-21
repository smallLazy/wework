<?php

/**
 * 提供基于 PKCS7 算法的加解密接口.
 */

namespace Wework\Utils;


class PKCS7Encoder
{
    const BLOCK_SIZE = 32;

    /**
     * 对需要加密的明文进行填充补位
     *
     * @param $params : 需要进行填充补位操作的明文
     * @return string
     */
    public function encode($params): string
    {
        // 计算需要填充的位数
        $amountToPad = self::BLOCK_SIZE - (strlen($params) % self::BLOCK_SIZE);
        if ($amountToPad == 0) {
            $amountToPad = PKCS7Encoder::BLOCK_SIZE;
        }

        // 获得补位所用的字符
        return $params . str_repeat(chr($amountToPad), $amountToPad);
    }

    /**
     * 对解密后的明文进行补位删除
     *
     * @param $params : 解密后的明文
     * @return false|string: 删除填充补位后的明文
     */
    public function decode($params)
    {
        $pad = ord(substr($params, -1));
        if ($pad < 1 || $pad > self::BLOCK_SIZE) {
            $pad = 0;
        }
        return substr($params, 0, (strlen($params) - $pad));
    }
}
