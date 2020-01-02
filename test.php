<?php
/**
 * Created by PhpStorm.
 * User: yd-yf-2018091401-001
 * Date: 2020/1/2
 * Time: 4:26 PM
 */

require './vendor/autoload.php';
! defined('BASE_PATH') && define('BASE_PATH', __DIR__);

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\TencentAiSdk\TencentAI;

if (!\Hyperf\Utils\ApplicationContext::hasContainer()) {
    $container = new Container((new DefinitionSourceFactory(true))());
    \Hyperf\Utils\ApplicationContext::setContainer($container);
}
$container = \Hyperf\Utils\ApplicationContext::getContainer();
$clientFactory = new \Hyperf\Guzzle\ClientFactory($container);
$appid = '';$appkey = '';

$ai = new TencentAI($clientFactory, $appid, $appkey);

$imgUrl = 'https://gss0.baidu.com/9vo3dSag_xI4khGko9WTAnF6hhy/zhidao/wh%3D600%2C800/sign=2636ec7078c6a7efb973a020cdca8369/6a600c338744ebf8db6b2d90d7f9d72a6059a703.jpg';
$imgData = file_get_contents($imgUrl);
$params = [
    'app_id'     => $appid,
    'image'      => base64_encode($imgData),
    'card_type'  => '0',
    'time_stamp' => strval(time()),
    'nonce_str'  => strval(rand()),
    'sign'       => '',
];
$rs = $ai->IdCardOCR($params);
if ($rs && $rs['ret'] == 0) {
    unset($rs['data']['frontimage'], $rs['data']['backimage']);
}
var_dump($rs);
