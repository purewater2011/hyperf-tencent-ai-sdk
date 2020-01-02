<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\TencentAiSdk;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                TencentAI::class => TencentAIFactory::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for tencent ai sdk.',
                    'source' => __DIR__ . '/../publish/tencentai.php',
                    'destination' => BASE_PATH . '/config/autoload/tencentai.php',
                ],
            ],
        ];
    }
}
