<?php

namespace izumi\longpoll;

use yii\web\Response;

/**
 * Usage:
 *
 * ```php
 * class SiteController extends Controller
 * {
 *     public function actions()
 *     {
 *         return [
 *             'polling' => [
 *                 'class' => 'izumi\longpoll\LongPollAction',
 *                 'events' => ['eventId'],
 *                 'callback' => [$this, 'longPollCallback'],
 *             ],
 *         ];
 *     }
 *
 *     public function longPollCallback(Server $server)
 *     {
 *         $server->responseData = 'any data';
 *     }
 * }
 * ```
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class LongPollAction extends BaseLongPollAction
{

    /**
     * @return Response
     */
    public function run()
    {
        return $this->runInternal();
    }
}
