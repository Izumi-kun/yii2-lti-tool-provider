<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use yii\base\Event;

/**
 * ToolEvent.
 *
 * @property Tool $sender
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ToolEvent extends Event
{
    /**
     * @inheritdoc
     * @param Tool $tool
     */
    public function __construct(Tool $tool, $config = [])
    {
        $this->sender = $tool;

        parent::__construct($config);
    }
}
