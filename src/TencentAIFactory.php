<?php
/**
 * Created by PhpStorm.
 * User: yd-yf-2018091401-001
 * Date: 2020/1/2
 * Time: 11:43 AM
 */

namespace Hyperf\TencentAiSdk;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Guzzle\ClientFactory;

class TencentAIFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $clientFactory = new ClientFactory($container);
        $ai = new TencentAI($clientFactory, $config->get('tencentai.appid'), $config->get('tencentai.appsecret'));
        return $ai;
    }
}