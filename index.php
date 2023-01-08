<?php

use Telegram\Bot\Api;


require_once __DIR__ . '/vendor/autoload.php';

$token = '5675285572:AAFvHNDnOZmtmj9iWo4oExAfcHSQlKLblhs';
$weather_token = 'd14cf428d6476aeb2758a94bc7357fbc';
$weather_url = "https://api.openweathermap.org/data/2.5/weather?appid={$weather_token}&units=metric&lang=ru";

//https://api.openweathermap.org/data/2.5/weather?appid=d14cf428d6476aeb2758a94bc7357fbc&units=metric&lang=ru&q=London

$telegram = new Api($token);

$update = $telegram->getWebhookUpdates();


//file_put_contents(__DIR__ . '/logs.txt', print_r($update, 1), FILE_APPEND);

$chat_id = $update['message']['chat']['id'] ?? '';
$text = $update['message']['text'] ?? '';

if ($text == '/start') {
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Привет {$update['message']['chat']['first_name']}! Я бот-синоптик и могу подсказать погоду в любом городе мира! Для получения погоды отправьте геолокацию (доступно только с мобильных устройств) или укажите город в формате: <b>Город</b>.\nПримеры: <b>London</b>, <b>Москва</b> ",
        'parse_mode' => 'HTML',
        ]);

} elseif (!empty($text)) {
    $weather_url .= "&q={$text}";
    $res = json_decode(file_get_contents($weather_url));
} elseif (isset($update['message']['location'])) {
    $weather_url .= "&lat={$update['message']['location']['latitude']}&lon={$update['message']['location']['longitude']}";
    $res = json_decode(file_get_contents($weather_url));
}

if (isset($res)) {
    if (empty($res)) {
        $response = $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Укажите корректный формат',
        ]);
    } else {
        $t = round($res->main->temp);
        $answer = "<u>Инвормация о погоде</u>\nГород: <b>{$res->name}</b>\nСтрана: <b>{$res->sys->country}</b>\nПогода: <b>{$res->weather[0]->description}</b>\n Температура: <b>{$t}℃</b>";

        $response = $telegram->sendPhoto([
            'chat_id' => $chat_id,
            'photo' => "https://openweathermap.org/img/wn/{$res->weather[0]->icon}@4x.png",
            'caption' => $answer,
            'parse_mode' => 'HTML'
        ]);
    }
}

