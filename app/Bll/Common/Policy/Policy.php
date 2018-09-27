<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/26
 * Time: 18:34
 */

namespace App\Bll\Common\Policy;

use App\Model\Policy as MdlPolicy;

class Policy{
    protected $objPolicy;
    
    public function __construct()
    {
        $this->objPolicy = new MdlPolicy();
    }

    public static function isCreditNo($idCard){
        //身份证位数及格式验证，18位或15位数字
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $idCard))
            return false;

        $city = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );

        //身份证前两位地区码验证
        if (!in_array(substr($idCard, 0, 2), $city))
            return false;

        $idCard = strtoupper($idCard);//针对18位身份证最后一位带特殊字符的身份证处理，将字母转为大写
        $length = strlen($idCard);

        //给15位身份证补全18位
        if ($length != 18) {
            //给15位身份证补全4位格式的出生年份 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($idCard, 12, 3), array('996', '997', '998', '999')) !== false) {
                $idCard = substr($idCard, 0, 6) . '18' . substr($idCard, 6, 9);
            } else {
                $idCard = substr($idCard, 0, 6) . '19' . substr($idCard, 6, 9);
            }

            $code = (new self())->calcIDCardCode($idCard);//获取身份证最后一位验证码

            if(!$code){
                return false;
            }

            $idCard .= $code;//给15位身份证补上最后一位验证码
        }

        //验证最后一位校验码是否正确
        $idCardBody = substr($idCard, 0, 17);//身份证号码17位主体
        $idCardCode = substr($idCard, 17, 1);//身份证号码最后一位验证码

        if ((new self())->calcIDCardCode($idCardBody) != $idCardCode) {
            return false;
        } else {
            return true;
        }
    }

    //计算身份证的最后一位验证码,根据国家标准GB 11643-1999
    private function calcIDCardCode($IDCardBody) {
        if (strlen($IDCardBody) != 17) {
            return false;
        }

        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        //校验码对应值
        $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $checksum = 0;
        for ($i = 0; $i < 17; $i++) {
            $checksum += substr($IDCardBody, $i, 1) * $factor[$i];
        }

        return $code[$checksum % 11];
    }

    /**
     * 验证身份证号有效性，并根据身份证号获取出生日期和性别
     *
     * @param $idCard
     * @return array
     */
    public static function getIDCardInfo($idCard){
        if(self::isCreditNo($idCard)){
            $idLength = strlen($idCard);

            //处理15位身份证号，为15位身份证号补全年份
            if ($idLength != 18) {
                // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
                if (array_search(substr($idCard, 12, 3), array('996', '997', '998', '999')) !== false) {
                    $idCard = substr($idCard, 0, 6) . '18' . substr($idCard, 6, 9);
                } else {
                    $idCard = substr($idCard, 0, 6) . '19' . substr($idCard, 6, 9);
                }
            }
            
            $vBirthday = substr($idCard, 6, 4) . '-' . substr($idCard, 10, 2) . '-' . substr($idCard, 12, 2);
            $vSex = substr($idCard, 16, 1) % 2;
            
            return ['status' => 1, 'birthday' => $vBirthday, 'sex' => $vSex];
        }else{
            return ['status' => 0, 'birthday' => '', 'sex' => ''];
        }
    }

}