<?php

require ("classes.php");
require ("const.php");


$update = new Update();

switch ($update->text){
    case "Пн":
        define("DAY_ENG", 'monday');
        define("DAY_RUS", 'понедельник');
        break;

    case "Вт":
        define("DAY_ENG", 'tuesday');
        define("DAY_RUS", 'вторник');
        break;

    case "Ср":
        define("DAY_ENG", 'wednesday');
        define("DAY_RUS", 'среду');
        break;

    case "Чт":
        define("DAY_ENG", 'thursday');
        define("DAY_RUS", 'четверг');
        break;

    case "Пт":
        define("DAY_ENG", 'friday');
        define("DAY_RUS", 'пятницу');
        break;

    case "Сб":
        define("DAY_ENG", 'saturday');
        define("DAY_RUS", 'субботу');
        break;
}

if ($update->text == "/start")
{
    $request = new Message();
    $text = "Привет, на какой срок показать расписание?";
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}


if ($update->text == "/chatid")
{
    $request = new Message();
    $text = CHAT_ID;
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}


if ($update->text == "Сейчас")
{
    Holiday::check("Сегодня");

    $database = new Database();

    $select  = $database->select(Database::$sqlCurDay);
    $text    = Timetable::getNowPair($select);

    $request = new Message();
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}


if ($update->text == "Сегодня")
{
    Holiday::check("Сегодня");

    $database = new Database();

    $select  = $database->select(Database::$sqlCurDay);
    $text    = Timetable::getTodayPair($select);

    $request = new Message();
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}


if ($update->text == "Завтра")
{
    Holiday::check("Завтра");

    $database = new Database();

    $select = $database->select(Database::$sqlTomorrow);
    $text = Timetable::getTomorrowPair($select);

    $request = new Message();
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}


if (defined('DAY_ENG')) {

    $database = new Database();

    $select  = $database->select(Database::$sqlDay);
    $text    = Timetable::getDayPair($select);

    $request = new Message();
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}


$text    = "Я тебя не понимаю 😥";
$request = new Message();
$request->sendMessage($text, Keyboards::$selectDay);
