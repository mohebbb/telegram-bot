<?php

define("token", "6141138107:AAEV1Phzd9uw7PUGW6VIty17WMQ9IMA2DS8");
define("api", "https://api.telegram.org/bot". token);

// اتصال به دیتابیس
function connect_to_db(){
    
    $conn = new mysqli("localhost", "root", "", "telegrambot_tsnumbot");
    
    if ($conn -> connect_error)
        echo "Failed: " . $conn -> connect_error;
    
    return $conn;
}

// دریافت اطلاعات کاربر
$getUp   = file_get_contents(api."/getUpdates?offset=93468649");
$arrayUp = json_decode($getUp, true);
if (isset($arrayUp["result"]["callback_query"])) {
    // برای دریافت کالبک باید رو سرور آنلاین باشه
    // $data = $arrayUp;
    // var_dump($data);
} else if(isset($arrayUp["result"]["0"]["message"])) {
    $chatId        = $arrayUp["result"]["0"]["message"]["chat"]["id"];
    $userText      = $arrayUp["result"]["0"]["message"]["text"];
    $userFirstName = $arrayUp["result"]["0"]["message"]["from"]["first_name"];
}

// دیافت ورودی از کاربر و انجام عملیات مورد نظر
switch ($userText) {
    case '/start': show_menu($chatId , $userFirstName); break;
    case 'بازگشت به منوی اصلی': show_menu($chatId , $userFirstName); break;
    case 'ثبت سفارش 👁': show_order($chatId); break;
    case 'استعلام قیمت 📃': call_for_price($chatId); break;
    case 'خرید شماره مجازی ویژه': get_num_vizhe($chatId); break;
    case 'خرید شماره مجازی اقتصادی': get_num_eghtesadi($chatId); break;
}

// تابع نمایش گزینه‌های کیبرد منو
function show_menu($chatId , $userFirstName)
{
    $welcomeText    = urlencode("سلام $userFirstName\nبه ربات شماره مجازی خوش اومدی :)");
    
    $key1 = "ثبت سفارش 👁";
    $key2 = "استعلام قیمت 📃";
    $key3 = "پیگیری درخواست‌ها 📍";
    $key4 = "افزایش موجودی 💰";
    $key5 = "اطلاعات حساب 🌐";
    $key6 = "تماس با ما 📞";
    $key7 = "زیرمجموعه گیری ♻";
    $key8 = "قوانین 📒";
    $key9 = "راهنما 🚧";
    
    $resp = [
        "keyboard" => [
            [$key1 , $key2 , $key3],
            [$key4 , $key5 , $key6],
            [$key7],
            [$key8 , $key9]
        ],
        "resize_keyboard" => true,
        "one_time_keyboard" => false,
        "input_field_placeholder" => "گزینه مورد نظر خود را انتخاب کنید."
    ];
    
    $reply = json_encode($resp);
    
    $url = api . "/sendmessage?chat_id=$chatId&text=$welcomeText&reply_markup=$reply";
    file_get_contents($url);
}

// تابع نمایش گزینه ثبت سفارش
function show_order($chatId)
{
    $text = "لطفا خدمات مورد نظر خود را انتخاب کنید.";

    $key1 = "خرید شماره مجازی ویژه";
    $key2 = "خرید شماره مجازی اقتصادی";
    $key3 = "اجاره شماره مجازی" ;
    $key4 = "خرید شماره مجازی انبوه";
    $key5 = "بازگشت به منوی اصلی";

    $resp = [
        'keyboard' => [
            [$key1 , $key2],
            [$key3 , $key4],
            [$key5]
        ],
        "resize_keyboard" => true,
        "one_time_keyboard" => false,
        "input_field_placeholder" => "گزینه مورد نظر خود را انتخاب کنید."
    ];

    $reply = json_encode($resp);

    file_get_contents(api."/sendMessage?chat_id=$chatId&text=$text&reply_markup=$reply");
}
    
// تابع نمایش گزینه استعلام قیمت
function call_for_price($chatId)
{
    $connection = connect_to_db();
    
    $result = $connection -> query("SELECT `id`, `number`, `country`, `type` FROM `number_liste` WHERE `status` = 1");

    $keyboard = [
        [
            ['text' => "شماره", 'callback_data' => "="],
            ['text' => "کشور", 'callback_data' => "="],
            ['text' => "نوع شماره", 'callback_data' => "="]
        ]
    ];

    while ($row = $result -> fetch_assoc()) {

        switch ($row['type']) {
            case 'vizhe':
                $type = "ویژه";
                break;

            case 'eghtesadi':
                $type = "اقتصادی";
                break;
            
            default:
                $type = "نامشخص";
                break;
        };

        $keyboard[] = [
            ['text' => $row['number'], 'callback_data' => 'Get_' . $row['id']],
            ['text' => $row['country'], 'callback_data' => 'Get_' . $row['id']],
            ['text' => $type, 'callback_data' => 'Get_' . $row['id']],
        ];
    }

    $text = "لطفا شماره مورد نظر خود را انتخاب فرمایید.👇👇";
    
    $inline_key_options = [
        'inline_keyboard' => $keyboard
    ];
    
    $reply = json_encode($inline_key_options);
    
    file_get_contents(api."/sendMessage?chat_id=$chatId&text=$text&reply_markup=$reply");
}

// تابع نمایش گزینه خرید شماره مجازی ویژه
function get_num_vizhe($chatId)
{
    $connection = connect_to_db();
    
    $result = $connection -> query("SELECT `id`, `number`, `country`, `type` FROM `number_liste` WHERE `status` = 1 and `type` = 'vizhe'");

    $keyboard = [
        [
            ['text' => "شماره", 'callback_data' => "="],
            ['text' => "کشور", 'callback_data' => "="],
            ['text' => "نوع شماره", 'callback_data' => "="]
        ]
    ];

    while ($row = $result -> fetch_assoc()) {

        switch ($row['type']) {
            case 'vizhe':
                $type = "ویژه";
                break;

            case 'eghtesadi':
                $type = "اقتصادی";
                break;
            
            default:
                $type = "نامشخص";
                break;
        };

        $keyboard[] = [
            ['text' => $row['number'], 'callback_data' => 'Get_' . $row['id']],
            ['text' => $row['country'], 'callback_data' => 'Get_' . $row['id']],
            ['text' => $type, 'callback_data' => 'Get_' . $row['id']],
        ];
    }

    $text = "لطفا شماره مورد نظر خود را انتخاب فرمایید.👇👇";
    
    $inline_key_options = [
        'inline_keyboard' => $keyboard
    ];
    
    $reply = json_encode($inline_key_options);
    
    file_get_contents(api."/sendMessage?chat_id=$chatId&text=$text&reply_markup=$reply");
}

// تابع نمایش گزینه خرید شماره مجازی اقتصادی
function get_num_eghtesadi($chatId)
{
    $connection = connect_to_db();
    
    $result = $connection -> query("SELECT `id`, `number`, `country`, `type` FROM `number_liste` WHERE `status` = 1 and `type` = 'eghtesadi'");

    $keyboard = [
        [
            ['text' => "شماره", 'callback_data' => "="],
            ['text' => "کشور", 'callback_data' => "="],
            ['text' => "نوع شماره", 'callback_data' => "="]
        ]
    ];

    while ($row = $result -> fetch_assoc()) {

        switch ($row['type']) {
            case 'vizhe':
                $type = "ویژه";
                break;

            case 'eghtesadi':
                $type = "اقتصادی";
                break;
            
            default:
                $type = "نامشخص";
                break;
        };

        $keyboard[] = [
            ['text' => $row['number'], 'callback_data' => 'Get_' . $row['id']],
            ['text' => $row['country'], 'callback_data' => 'Get_' . $row['id']],
            ['text' => $type, 'callback_data' => 'Get_' . $row['id']],
        ];
    }

    $text = "لطفا شماره مورد نظر خود را انتخاب فرمایید.👇👇";
    
    $inline_key_options = [
        'inline_keyboard' => $keyboard
    ];
    
    $reply = json_encode($inline_key_options);
    
    file_get_contents(api."/sendMessage?chat_id=$chatId&text=$text&reply_markup=$reply");
}


?>