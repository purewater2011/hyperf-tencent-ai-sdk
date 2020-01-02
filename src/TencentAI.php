<?php
/**
 * Created by PhpStorm.
 * User: yd-yf-2018091401-001
 * Date: 2020/1/2
 * Time: 10:53 AM
 */

namespace Hyperf\TencentAiSdk;


use Hyperf\Guzzle\ClientFactory;

class TencentAI extends Client
{
    const CHUNK_SIZE = 6400;

    private $appid;

    private $appsecret;

    public function __construct(ClientFactory $client, $appid, $appsecret)
    {
        parent::__construct($client);
        $this->appid = $appid;
        $this->appsecret = $appsecret;
    }

    public function handleParams(&$params)
    {
        if (empty($params['app_id'])) {
            $params['app_id'] = $this->appid;
        }
        return $params;
    }

    /** texttrans ：调用文本翻译（AI Lab）接口
     * 参数说明
     *   - $params：type-翻译类型；text-待翻译文本。（详见http://ai.qq.com/doc/nlptrans.shtml）
     * 返回数据
     *   - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
    */
    public function TextTrans($params): array
    {
        $this->handleParams($params);
        $params['sign'] = Utils::GenSign($params, $this->appsecret);
        $response = $this->request('POST', '/nlp/nlp_texttrans', $params);
        return $response;
    }

    /**
     * 身份证OCR接口
     * @param $params
     * @return array
     */
    public function IdCardOCR($params)
    {
        $this->handleParams($params);
        $response = $this->GeneralOCR($params, '/ocr/ocr_idcardocr');
        return $response;

    }

    /** generalocr ：调用通用OCR识别接口
     * 参数说明
     *   - $params：image-待识别图片。（详见http://ai.qq.com/doc/ocrgeneralocr.shtml）
     * 返回数据
     *   - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
    */
    public function GeneralOCR($params, $uri = '/ocr/ocr_generalocr')
    {
        $this->handleParams($params);
        if (!Utils::IsBase64($params['image'])) {
            $params['image'] = base64_encode($params['image']);
        }
        $params['sign'] = Utils::GenSign($params, $this->appsecret);

        $response = $this->request('POST', $uri, $params);
        return $response;
    }

    /** wxasrs ：调用语音识别-流式版(WeChat AI)接口
     * 参数说明
     *   - $params：speech-待识别的整段语音，不需分片；
     *              format-语音格式；
     *              rate-音频采样率编码；
     *              bits-音频采样位数；
     *              speech_id-语音ID。（详见http://ai.qq.com/doc/aaiasr.shtml）
     * 返回数据
     *   - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
     */
    public function WechatAsrs($params)
    {
        $this->handleParams($params);
        $speech = Utils::IsBase64($params['speech']) ? base64_decode($params['speech']) : $params['speech'];
        unset($params['speech']);
        $speech_len = strlen($speech);
        $total_chunk = ceil($speech_len / self::CHUNK_SIZE);
        $params['cont_res'] = 0;
        for ($i = 0; $i < $total_chunk; ++$i) {
            $chunk_data = substr($speech, $i * self::CHUNK_SIZE, self::CHUNK_SIZE);
            $params['speech_chunk'] = base64_encode($chunk_data);
            $params['len'] = strlen($chunk_data);
            $params['seq'] = $i * self::CHUNK_SIZE;
            $params['end'] = ($i == ($total_chunk - 1)) ? 1 : 0;
            $response = $this->WechatAsrsPerchunk($params);
        }
        return $response;
    }

    /** wxasrs_perchunk ：调用语音识别-流式版(WeChat AI)接口
     * 参数说明
     *   - $params：speech_chunk-待识别的语音分片；
     *              seq-语音分片所在语音流的偏移量；
     *              len-分片长度；
     *              end-是否结束分片；
     *              cont_res-是否获取中间识别结果；
     *              format-语音格式；
     *              rate-音频采样率编码；
     *              bits-音频采样位数；
     *              speech_id-语音ID。（详见http://ai.qq.com/doc/aaiasr.shtml）
     * 返回数据
     *   - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
     */
    public function WechatAsrsPerchunk($params)
    {
        $this->handleParams($params);
        if (!Utils::IsBase64($params['speech_chunk'])) {
            $params['speech_chunk'] = base64_encode($params['speech_chunk']);
        }
        $params['sign'] = Utils::GenSign($params, $this->appsecret);
        $response = $this->request('POST', '/aai/aai_wxasrs', $params);
        return $response;
    }
}