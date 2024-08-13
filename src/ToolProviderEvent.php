<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use yii\base\Event;

/**
 * ToolProviderEvent.
 *
 * @property ToolProvider $sender
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ToolProviderEvent extends Event
{
    /**
     * @inheritdoc
     * @param ToolProvider $toolProvider
     */
    public function __construct(ToolProvider $toolProvider, $config = [])
    {
        $this->sender = $toolProvider;

        parent::__construct($config);
    }
}
