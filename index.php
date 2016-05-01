<?php
require_once 'vendor/autoload.php';

use Pathetic\TgBot\Bot as TgBot;
use GuzzleHttp\Client;

$httpClient = new Client(['proxy' => 'tcp://127.0.0.1:7778', 'verify' => false]);
$bot = new TgBot('87628676:AAFacIOCzRaQUpKu3XXrCoTf1kgC-SUJTug', $httpClient);

# Commands

# Usage: /echo "something"
# You can even set default values: function($message, $something = "I don't know what to say.") {}
$bot->command('echo', function ($message, $something) use ($bot) {
    # You can use $message->from->firstName instead of $message->from->first_name
    $bot->sendMessage($message->chat->id, $message->from->first_name . " says: $something");
});

# Usage: /sum 1 2 3
$bot->command('sum', function ($message, ...$numbers) use ($bot) {
    $result = 0;
    foreach ($numbers as $number) {
        $result += (int)$number;
    }

    # You can use $message->id instead of $message->message_id
    $bot->sendMessage($message->chat->id, $result, null, $message->message_id);
});

# Usage: /img description
$bot->command('img', function ($message, ...$description) use ($bot) {
    if (empty($description)) {
        return;
    }
    # Implode all arguments in one string.
    $description = implode(chr(32), $description);

    $url = "http://api.oboobs.ru/noise/0}";
    $res = file_get_contents($url);
    $res = json_decode($res, true);

    if (!isset($res) || !isset($res[0]['preview'])) {
        $res_str = 'Cannot get that boobs, trying another one...';

        $bot->sendMessage($message->chat->id, $res_str, null, $message->message_id);
    } else {
        foreach ($res as $v) {
            $res_str = 'http://media.oboobs.ru/' . $v['preview'];
            $image = fopen($res_str, 'r');

            $bot->sendPhoto($message->chat->id, $image, $description, $message->message_id);
        }
    }
});

# Events

$bot->on(
    function ($message) {
        # If you are the sender of current message, this will return true.
        return 'YourUsername' == $message->from->username;
    },

    # This function will be executed if previous returned true.
    function ($message) use ($bot) {
        # Reply to message.
        $bot->sendMessage($message->chat->id, 'I love you.', null, $message->id);

        # If this function will return false, the cycle will be broken so no other events for current message will be triggered.
        return false;
    }
);

# Updates handling

# Webhook
$bot->handle($bot->createUpdateFromRequest());

# Long polling
# This file should be runned as daemon.
while (true) {
    $bot->handle($bot->getUpdates());
    sleep(3);
}
