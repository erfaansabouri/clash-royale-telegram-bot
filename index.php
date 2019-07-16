<?php

// Bot initialization
require 'vendor/autoload.php';

use Telegram\Bot\Api;

$API_KEY = 
$telegram = new Api($API_KEY);
$updates = $telegram->getWebhookUpdates();
$chat_id = $updates->getMessage()->getChat()->getId();
$text = $updates->getMessage()->getText();



// Database Connection

$servername = 
$username = 
$password = 
$dbname = 
$connection = new mysqli($servername, $username, $password, $dbname);


// Clash API

$token = "iFRFWYw0";
$opts = [
    "http" => [
        "header" => "auth:" . $token
    ]
];
$context = stream_context_create($opts);

/*
 * TODO: 1.Check if user joined in channel.
 *       2. if joined then show the keyboard
 *       3.
 * */


function isJoined($chat_id)
{
    global $API_KEY;
    $sponsored_channel = json_decode(file_get_contents("https://api.telegram.org/bot$API_KEY/getChatMember?chat_id=@RoyaleCh&user_id=" . $chat_id));
    $status = $sponsored_channel->result->status;
    return $status == 'member' ||
        $status == 'creator' ||
        $status == 'administrator';
}

if (!isJoined($chat_id)) {
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "برای استفاده از امکانات این ربات باید در کانال مخصوص اش جوین شید!\n@RoyaleCh\n@RoyaleCh\n@RoyaleCh\n@RoyaleCh\nاگر عضو شدید حالا ربات رو /start کنید!",
    ]);
} elseif (isJoined($chat_id)) {
    $user = "SELECT * FROM users WHERE chat_id = '$chat_id' LIMIT 1";
    $user = $connection->query($user);

    $query = "SELECT * FROM users WHERE chat_id = '$chat_id' LIMIT 1";
    $result = mysqli_query($connection, $query) or die(mysqli_error($connection));
    $row = mysqli_fetch_object($result);

    if ($text == '🎫 ثبت تگ اکانت کلش رویال') {
        if ($user->num_rows == 0){
            $add_new_user = "INSERT INTO users (chat_id, royale_tag , step) VALUES ('$chat_id','null' , '1')";
            $connection->query($add_new_user);
        }
        elseif ($user->num_rows > 0){
            $update_user = "UPDATE users SET step='1' , royale_tag='null' WHERE chat_id='$chat_id'";
            $connection->query($update_user);
        }

        $telegram->sendPhoto([
            'chat_id' => $chat_id,
            'photo' => 'tag.png',
            'caption' => "تگ کلش رویال خود را وارد کنید.".PHP_EOL."مثال : #QUUYUP"
        ]);

    }

    elseif ($row->step == '1'){
        if (stristr($text , '#')){
            $tag = substr($text ,1);
            $update_user = "UPDATE users SET step='0', royale_tag='$tag' WHERE chat_id='$chat_id'";
            $connection->query($update_user);
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "تگ شما با موفقیت به روز رسانی شد!",
            ]);

        }

        else{
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "تگ باید شامل علامت هشتگ(#) باشد!",
            ]);
        }
    }


    elseif ($text == '📊 دریافت اطلاعات کاربری') {

        $this_user = "SELECT * FROM users WHERE chat_id = '$chat_id' ";
        $result = $connection->query($this_user);
        $row = $result->fetch_assoc();
        $royale_tag = $row["royale_tag"];
        $get_api = file_get_contents("https://api.royaleapi.com/player/$royale_tag",true, $context);
        $display_result = json_decode($get_api, true);
        $clash_name = $display_result["name"];

        $get_api_chests = file_get_contents("https://api.royaleapi.com/player/$royale_tag/chests",true, $context);
        $display_result_chests = json_decode($get_api_chests, true);


        $bot_link = "https://t.me/royalerobot";
        $deck = $display_result["deckLink"];
        $chests = $display_result_chests["upcoming"];
        $result_chest = "";
        $counter = 1;
        foreach($chests as $chest)
            $result_chest .= "+".$counter++ . " - ". $chest . PHP_EOL;
        $output = "🚹️ نام : "."<b>". $display_result["name"] ."</b>".PHP_EOL
            ."#️⃣ تگ : " . "<b>". $display_result["tag"] ."</b>".PHP_EOL
            ."🏆 جام : " . "<b>". $display_result["trophies"] ."</b>".PHP_EOL
            ."🔝 بیشترین جام : " . "<b>". $display_result["stats"]["maxTrophies"] ."</b>".PHP_EOL
            ."🏕 نام کلن : " . "<b>". $display_result["clan"]["name"] ."</b>".PHP_EOL
            ."🃏 کارت مورد علاقه : " . "<b>". $display_result["stats"]["favoriteCard"]["name"] ."</b>".PHP_EOL
            ."⬇️ تعداد کارت های جمع آورش شده از کلن : " . "<b>". $display_result["stats"]["clanCardsCollected"] ."</b>".PHP_EOL

            ."📊 تعداد بازی های انجام داده : " . "<b>". $display_result["games"]["total"] ."</b>".PHP_EOL
            ."💪🏻 تعداد بازی های برده : " . "<b>". $display_result["games"]["wins"] ."</b>".PHP_EOL
            ."💩 تعداد بازی های باخته : " . "<b>". $display_result["games"]["losses"] ."</b>".PHP_EOL
            ."🏳️ تعداد بازی های مساوی شده : " . "<b>". $display_result["games"]["draws"] ."</b>".PHP_EOL
            ."🃏 دک کنونی : " . "<a href='$deck'>". "کلیک کنید!" ."</a>".PHP_EOL
            ."🎁 جعبه های بعدی : " . "\n<b>". "$result_chest" ."</b>"
            ."+".$display_result_chests["legendary"] .  " - <b>". "legendary " ."</b>".PHP_EOL
            ."+".$display_result_chests["epic"] .  " - <b>". "epic " ."</b>".PHP_EOL
            ."+".$display_result_chests["giant"] .  " - <b>". "giant " ."</b>".PHP_EOL
            ."+".$display_result_chests["magical"] .  " - <b>". "magical " ."</b>".PHP_EOL





            ."@RoyaleRobot | <a href='$bot_link'>ربات کلش رویال</a>";
        ;

        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $output,
            'parse_mode' => 'html',
            'disable_web_page_preview' => true
        ]);
        /*
        $keyboard = [
            ['📊 دریافت اطلاعات کاربری'],
            ['🎫 ثبت تگ اکانت کلش رویال'],
        ];

        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'از کیبورد زیر گزینه مورد نظر را انتخاب کنید.',
            'reply_markup' => $reply_markup
        ]);
        */
    }

    else{
        $keyboard = [
            ['📊 دریافت اطلاعات کاربری'],
            ['🎫 ثبت تگ اکانت کلش رویال'],
        ];

        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'از کیبورد زیر گزینه مورد نظر را انتخاب کنید.',
            'reply_markup' => $reply_markup
        ]);
    }




}


$connection->close();
