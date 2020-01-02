## Description
腾讯AI开放平台，未提供php好用的composer包。基于这样的痛点下，开发一个适合hyperf框架的Tencent AI SDK功能包。
目前已实现：通用ocr，身份证识别，语音识别。

## Installation
安装:

    composer require hyperf/tencent-ai-sdk
    
## Getting started

###依赖注入
    use Hyperf\TencentAiSdk\TencentAI;
    /**
    * @Inject()
    * @var TencentAI
    */
    public $tencentAi;