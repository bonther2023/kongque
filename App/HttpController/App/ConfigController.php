<?php

namespace App\HttpController\App;

use App\HttpController\BaseController;

class ConfigController extends BaseController
{

    public function vip(){
        try {
            //获取配置
            $settings = settings();
            $payModel = 0;
            $telModel = 0;
            if ($settings['payment_wechat'] || $settings['payment_alipay']) {
                $payModel = 1;
            }
            if ($settings['tel_payment_wechat'] || $settings['tel_payment_alipay']) {
                $telModel = 1;
            }
            $show = 1; //展示渠道（1、普通  2、话费）
            if ($payModel && !$telModel) {
                $show = 1;
            } elseif (!$payModel && $telModel) {
                $show = 2;
            } elseif ($payModel && $telModel) {
                $rand = rand(1, 10);
                $show = $rand <= (int)$settings['wa_bi']/10 ? 2 : 1;
            }
            $vip = [
                'vip'            => [
                    [
                        'title' => '一日VIP',
                        'money' => $show == 2 ? $settings['tel_day_vip'] : (int) $settings['day_vip'],
                        'yuan'  => (int) $settings['day_vip'] * 2,
                        'time'  => '1日',
                        'note'  => '全站視頻',
                    ],
                    [
                        'title' => '一月VIP',
                        'money' => $show == 2 ? $settings['tel_month_vip'] : (int) $settings['month_vip'],
                        'yuan'  => (int) $settings['month_vip'] * 2,
                        'time'  => '1月',
                        'note'  => '全站視頻+福利',
                    ],
                    [
                        'title' => '半年VIP',
                        'money' => $show == 2 ? $settings['tel_quarter_vip'] : (int) $settings['quarter_vip'],
                        'yuan'  => (int) $settings['quarter_vip'] * 2,
                        'time'  => '6月',
                        'note'  => '全站視頻+福利',
                    ],
                    [
                        'title' => '一年VIP',
                        'money' => $show == 2 ? $settings['tel_year_vip'] : (int) $settings['year_vip'],
                        'yuan'  => (int) $settings['year_vip'] * 2,
                        'time'  => '12月',
                        'note'  => '全站視頻+福利',
                    ],
                    [
                        'title' => '永久VIP',
                        'money' => $show == 2 ? $settings['tel_forever_vip'] : (int) $settings['forever_vip'],
                        'yuan'  => (int) $settings['forever_vip'] * 2,
                        'time'  => '永久',
                        'note'  => '全站视频+福利',
                    ],
                ],
                'payment_wechat' => $show == 2 ? $settings['tel_payment_wechat'] : $settings['payment_wechat'],
                'payment_alipay' => $show == 2 ? $settings['tel_payment_alipay'] : $settings['payment_alipay'],
                'payment_code' => $show == 2 ? $settings['tel_payment_code'] : $settings['payment_code'],
                'payment_default' => $settings['payment_default'],
            ];

            foreach ($vip['vip'] as $key=>$item){
                if(empty($item['money'])) unset($vip['vip'][$key]);
            }
            if(count($vip['vip']) < 5){
                sort($vip['vip']);
            }
            return $this->writeJson(0, encrypt_data($vip));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
