<?php

namespace App\Utility;

use EasySwoole\Utility\Hash;
use EasySwoole\VerifyCode\Conf;
use EasySwoole\VerifyCode\VerifyCode;

class Captcha{

    protected $options;

    public function __construct(){
        $options = config('CAPTCHA');
        $this->options = $options;
    }

    /**
     * 创建验证码
     * @return array
     */
    public function create(){
        $config = new Conf();
        $config->setCharset((string)$this->options['charset']);
        $config->setLength($this->options['length']);
        $config->setUseCurve($this->options['curve']);
        $config->setUseNoise($this->options['notice']);
        $config->setFontColor($this->options['font_color']);
        $config->setFontSize($this->options['font_size']);
        $config->setBackColor($this->options['back_color']);
        $config->setImageWidth($this->options['image_weight']);
        $config->setImageHeight($this->options['image_height']);
        $config->setTemp($this->options['temp']);
        $verify = new VerifyCode($config);
        $obj = $verify->DrawCode();
        $code = $obj->getImageCode();
        $captchaKey = Hash::makePasswordHash($code);
        $captcha  = $obj->getImageBase64();
//        $file  = $obj->getImageFile();
//        //删除缓存图片信息
//        unlink($file);
        return ['captcha'=>$captcha,'captchaKey'=>$captchaKey];
    }

    /**
     * 验证验证码
     * @param $code
     * @param $captchaKey
     * @return bool
     */
    public function check($code,$captchaKey){
        if (strlen($captchaKey) === 0) {
            return false;
        }
        return Hash::validatePasswordHash($code,$captchaKey);
    }


}
