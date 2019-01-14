<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2019 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use IMSGlobal\LTI\Http\ClientInterface;
use IMSGlobal\LTI\HTTPMessage;

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
    public function send(HTTPMessage $message)
    {
        $request = Module::getInstance()->httpClient->createRequest()
            ->setMethod($message->method)
            ->setUrl($message->url)
            ->setContent($message->request);

        if (!empty($message->requestHeaders)) {
            if (is_string($message->requestHeaders)) {
                $request->setHeaders(explode("\n", $message->requestHeaders));
            } elseif (is_array($message->requestHeaders)) {
                $request->setHeaders(array_values($message->requestHeaders));
            }
            $message->requestHeaders = implode("\n", $request->composeHeaderLines());
        }

        $resp = $request->send();
        $message->status = $resp->statusCode;
        $message->response = $resp->getContent();
        $message->responseHeaders = implode("\n", $resp->composeHeaderLines());

        return $resp->isOk;
    }
}
