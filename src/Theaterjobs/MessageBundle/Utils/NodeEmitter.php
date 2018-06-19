<?php

namespace Theaterjobs\MessageBundle\Utils;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Acl\Exception\Exception;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Class NodeEmitter
 * @package Theaterjobs\MessageBundle\Utils
 */
class NodeEmitter
{
    /** @var string Api params */
    private  $nodeApi;
    private  $nodeAPIToken;

    const API_REPLY_MESSAGE = "/api/messages/emit";
    const API_DELETE_MESSAGE = "/api/messages/delete";

    /**
     * NodeEmitter constructor.
     *
     * @param $nodeApi
     * @param $nodeAPIToken
     */
    public function __construct($nodeApi, $nodeAPIToken)
    {
        $this->nodeApi = $nodeApi;
        $this->nodeAPIToken= $nodeAPIToken;
    }

    /**
     * Sends a message to node server
     *
     * @param array $templates
     * @param string $socketUserID
     * @param string $roomID
     * @param User $user
     *
     * @return bool
     * @throws Exception
     */
    public function emitMessage($templates, $socketUserID, $roomID, $user)
    {
        try{
            $apiUrl = $this->nodeApi;
            $token = $this->nodeAPIToken;
            $apiUrl = $apiUrl . self::API_REPLY_MESSAGE;

            $params = [
                'roomID'    => $roomID,
                'socketID'  => $socketUserID,
                'messages'  => $templates,
                'senderID'  => md5($user->getId()),
                '_token'    => $token
            ];

            $options = ['form_params' => ['params' => json_encode($params)]];

            $client = new Client();
            $response = $client->post($apiUrl, $options);
            $response = json_decode($response->getBody());

            return $response->success ? true : false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function emitDelete($template, $socketUserID, $roomID, $user)
    {
        try{
            $apiUrl = $this->nodeApi;
            $token = $this->nodeAPIToken;
            $apiUrl = $apiUrl . "/api/messages/delete";

            $params = [
                'roomID'    => $roomID,
                'socketID'  => $socketUserID,
                'template'  => $template,
                'senderID'  => md5($user->getId()),
                '_token'    => $token
            ];

            $options = ['form_params' => ['params' => json_encode($params)] ];

            $client = new Client();
            $response = $client->post($apiUrl, $options);
            $response = json_decode($response->getBody());

            return $response->success ? true : false;
        } catch (\Exception $exception) {
            return false;
        }
    }
}