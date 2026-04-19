<?php

namespace App;

use App\Models\User;
use App\Response\Responser;
use JetBrains\PhpStorm\NoReturn;

class Application
{
    #[NoReturn] public function __construct()
    {
        if (config('DEV')):
            $last_offset_id = 1;

            while (true):
                echo $last_offset_id . "\n";
                $data = file_get_contents(BotService::BASE() . '/getUpdates?offset=' . $last_offset_id);
                $pull = json_decode($data, true);

                if (is_array($pull) && $pull['ok'] && count($pull['result']) > 0) {
                    $result = $pull['result'];

                    foreach ($result as $message):
                        $last_offset_id = $message['update_id'] + 1;
//                        $this->syncUser($message);
//                        $this->handleMessage($message);
                        $this->handleCallback($message);
                    endforeach;
                } else {
                    error_log('Something went wrong! Err: Not OK, LOI: ' . $last_offset_id . "\n");
                }
                sleep(1);
            endwhile;
        else:
            $update = file_get_contents("php://input");
            $message = json_decode($update, true);

            $this->syncUser($message);
            $this->handleMessage($message);
            $this->handleCallback($message);
        endif;

        http_response_code(200);
        echo 'OK';
        exit();
    }


    public function syncUser($message): void
    {
        if (!is_array($message)) return;

        if (array_key_exists('message', $message)):
            $objects = array_values([
                (array_key_exists('chat', $message['message']) ? $message['message']['chat'] : []),
                (array_key_exists('from', $message['message']) ? $message['message']['from'] : [])
            ]);
        elseif (array_key_exists('callback_query', $message) && array_key_exists('message', $message['callback_query'])):
            $objects = array_values([
                (array_key_exists('chat', $message['callback_query']['message']) ? $message['callback_query']['message']['chat'] : []),
                (array_key_exists('from', $message['callback_query']) ? $message['callback_query']['from'] : [])
            ]);
        else:
            return;
        endif;

        $objects = array_map(function (array $object) {
            if (empty($object)) return null;

            $name = null;

            if (array_key_exists('first_name', $object)) {
                $name = $object['first_name'] . (array_key_exists('last_name', $object) ? ' ' . $object['last_name'] : '');
            } else if (array_key_exists('title', $object)) {
                $name = $object['title'];
            }

            return [
                'id' => $object['id'],
                'name' => trim($name),
                'username' => array_key_exists('username', $object) ? $object['username'] : null,
                'type' => array_key_exists('type', $object) ? $object['type'] : 'private',
            ];
        }, $objects);

        $objects = array_values(array_filter($objects));

        $users = [];
        foreach ($objects as $object):
            $users[$object['id']] = $object;
        endforeach;


        foreach ($users as $key => $user) {

            $userModel = User::query();
            $fetched = $userModel->find($key);

            if ($fetched) {
                $userModel
                    ->where('id', $fetched->id)
                    ->update([
                        'name' => $user['name'],
                        'username' => $user['username'],
                    ]);
            } else {
                $userModel->insert([
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'type' => $user['type'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public function handleMessage($message): void
    {
        if (!is_array($message) || !array_key_exists('message', $message)) return;
        if (in_array('forward_origin', $message) || in_array('forward_from_chat', $message)) return;
        if (!is_array($message['message']) || !array_key_exists('text', $message['message'])) return;

        new Responser(
            message: $message['message']['text'],
            user_id: $message['message']['from']['id'],
            chat_id: $message['message']['chat']['id'],
            message_id: $message['message']['message_id'],
        )->message()->send();
    }

    public function handleCallback($message): void
    {
        if (!is_array($message) || !array_key_exists('callback_query', $message)) return;
        if (!is_array($message['callback_query']) || !array_key_exists('data', $message['callback_query'])) return;

        new Responser(
            message: $message['callback_query']['message']['text'],
            user_id: $message['callback_query']['from']['id'],
            chat_id: $message['callback_query']['message']['chat']['id'],
            message_id: $message['callback_query']['message']['message_id'],
            data: $message['callback_query']['data'],
            query_id: $message['callback_query']['id'],
        )->callback()->send();
    }
}