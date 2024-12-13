<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use ceLTIc\LTI\Http\ClientInterface;
use ceLTIc\LTI\Http\HttpMessage;

/**
 * Class HttpClient
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class HttpClient implements ClientInterface
{
    /**
     * @inheritdoc
     */
    public function send(HttpMessage $message): bool
    {
        $request = Module::getInstance()->httpClient->createRequest()
            ->setMethod($message->getMethod())
            ->setUrl($message->getUrl())
            ->setContent($message->request);

        if (!empty($message->requestHeaders)) {
            $request->setHeaders(array_values($message->requestHeaders));
            $message->requestHeaders = $request->composeHeaderLines();
        }

        $resp = $request->send();
        $message->status = $resp->statusCode;
        $message->response = $resp->getContent();
        $message->responseHeaders = $resp->composeHeaderLines();

        return $resp->isOk;
    }
}
