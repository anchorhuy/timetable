<?php

#CONST

//текущая дата
define("CUR_DATE", date("U") - 3 * 60 * 60);


//текущее время
define("CUR_TIME", date ('H:i', CUR_DATE));


//четная или нечетная неделя
if((strftime("%V") % 2) == 0) $cur_week = 2;
else $cur_week = 1;

define("CUR_WEEK", $cur_week);


//день недели
define("CUR_DAY", strtolower( date("l", CUR_DATE)));


//URL сайта
define('WEBHOOK_URL','https://pi-timetable.herokuapp.com/');


//token бота
define('API_URL', 'https://api.telegram.org/bot' . '255704702:AAGm_IG22M0tBeWp8JfhYKxj0EJFe18-IQQ' . '/');


//database
const DB_HOST     = 'server27.hosting.reg.ru';
const DB_NAME   = 'u0167959_timetable';
const DB_USER     = 'u0167959_status';
const DB_PASSWORD = 'Pasha635';


class Timetable
{
    private static $timetable;
    private static $headersForNow = [
        ["<b>Сейчас идет</b>:\n\r\n\r", "<b>Следующая пара</b>:\n\r\n\r"],
        ["До конца ", "До начала "]
    ];


    public static function getToday($select)
    {
        self::getTimetableToArray($select);

        $text = self::createTimetableToday();

        return$text;
    }

    public static function getNow($select)
    {
        self::getTimetableToArray($select);
        self::removeLastPair();

        if (CUR_TIME > self::$timetable[0]['close']) {
            return "<i>Сегодня больше нет пар 🍻</i>";
        }

        $text = self::createTimetableNow();

        return $text;
    }

    private function createTimetableNow(){

        // Сколько до начала и конца пар
        $timeTo    = self::pairTimeTo();

        $text = "";

        for ($i=0, $j=0; $i < 2; $i++, $j++){


            if (self::$timetable[$i]['open'] > CUR_TIME and $i != 1) {
                $j++;
            }

            $pair  = self::$timetable[$i];

            $text .= self::$headersForNow[0][$j];
            $text .= "<b>" . $pair['subject']       . "</b>\n\r";
            $text .= "🕒 "  . $pair['open'] .  " - " . $pair['close']  . "\n\r";
            $text .= "🏤 "  . $pair['cab']                             . "\n\r\n\r";
            $text .= self::$headersForNow[1][$j]    . $timeTo[$i]     . "\n\r";


            // Сейчас последняя пара
            if(count(self::$timetable) == $i + 1) {
                $text .= "Это последняя пара 🍻";
            }
            else {
                $text .= "\n\r\n\r";
            }

            if (self::$timetable[$i]['open'] > CUR_TIME) {
                $i++;
            }
        }
        return $text;
    }

    private function createTimetableToday(){

        $text = "<i>Расписание на сегодня:</i>\n\r\n\r";

        foreach (self::$timetable as $row)
        {
            $text .= "<b>" . $row["subject"] . "</b>" . "\n\r";
            $text .= "🕒 " . $row['open'] . " - " . $row['close'] . "\n\r";
            $text .= "🏤 " . $row['cab']  . "\n\r\n\r";
        }

        return $text;
    }

    private function removeLastPair(){
        for ( $i = 0; $i <= count(self::$timetable); $i++ )
        {
            if (self::$timetable[$i]['close'] < CUR_TIME){
                unset(self::$timetable[$i]);
            }

        }
        self::$timetable = array_values(self::$timetable);
    }

    private function getTimetableToArray($select)
    {
        foreach ($select as $row) {
            self::$timetable[] = array (
                'cab'     => $row['cab'],
                'open'    => substr($row['open'], 0, 5),
                'close'   => substr($row['close'], 0, 5),
                'time'    => $row['time'],
                'week'    => $row['week'],
                'subject' => $row['subject']
            );
        }
    }

    private function pairTimeTo(){

        self::$timetable = array_slice(self::$timetable, 0, 3);
        $items     = count(self::$timetable);

        foreach (self::$timetable as $row){
            $begin[] = $row["open"];
            $end[]   = $row["close"];
        }

        //Пара идет
        if (CUR_TIME >= $begin[0]){
            $result[] = date("H:i", strtotime($end[0]) - strtotime(CUR_TIME));

            //Следующая
            if ($items >= 2){
                $result[] = date("H:i", strtotime($begin[1]) - strtotime(CUR_TIME));

            }
        }


        //Следующая
        elseif (CUR_TIME < $begin[0]){
            $result[] = date("H:i", strtotime($begin[0]) - strtotime(CUR_TIME));
        }

        return $result;

    }
}

class Holiday
{
    public static $Holidays = ['monday', 'sunday'];

    public static function check()
    {
        foreach (self::$Holidays as $holiday){
            if (CUR_DAY == $holiday) {
                $text    = "Сегодня выходfaной 🍻";
                $request = new Message();
                $request->sendMessage($text, Keyboards::$selectDay);
                exit();
            }
        }
    }
}

class Keyboards
{
    public static $selectDay = [
        ['Сейчас', 'Сегодня', 'Завтра'],
        ['Вт', 'Ср', 'Чт', 'Пт', 'Сб']
    ];
}


class Update
{
    public $text;
    public $audio;
    public $document;
    public $photo;
    public $sticker;
    public $video;
    public $voice;
    public $caption;
    public $contact;
    public $location;
    public $venue;


    public function __construct()
    {
        $content = file_get_contents("php://input");
        $update = json_decode($content, true);

        if (!$update){
//            exit();
        }

        if (isset($update["message"])) {
            $message = ($update["message"]);

            define("MESSAGE_ID", $message['message_id']);
            define("CHAT_ID",    $message['chat']['id']);
            define("USER_NAME",  html_entity_decode($message['chat']['first_name'])
                .html_entity_decode($message['chat']['last_name']));
        }

        if (isset($message['text'])) {
            $this->text = $message['text'];
        }

        if (isset($message['audio'])) {
            $this->audio = $message['audio'];
        }

        if (isset($message['document'])) {
            $this->document = $message['document'];
        }

        if (isset($message['photo'])) {
            $this->photo = $message['photo'];
        }

        if (isset($message['sticker'])) {
            $this->sticker = $message['sticker'];
        }

        if (isset($message['video'])) {
            $this->video = $message['video'];
        }

        if (isset($message['voice'])) {
            $this->voice = $message['voice'];
        }

        if (isset($message['caption'])) {
            $this->caption = $message['caption'];
        }

        if (isset($message['contact'])) {
            $this->contact = $message['contact'];
        }

        if (isset($message['location'])) {
            $this->location = $message['location'];
        }

        if (isset($message['venue'])) {
            $this->venue = $message['venue'];
        }

    }
}

class Database
{
    private $host;
    private $dbname;
    private $user;
    private $password;

    public  $pdo;
    public $timetable;

    public static $sqlCurDay =
        "SELECT cab, open, close, time, subject
                            FROM " . CUR_DAY . "
                            INNER JOIN subjects
                            ON name = subject_id
                    
                            INNER JOIN time
                            ON time = id
                    
                            INNER JOIN cabs
                            ON cabinet = cab_id
                    
                            WHERE week = " . CUR_WEEK. "
                            
                            ORDER BY time ASC
                            ";

    public function __construct($host, $dbname, $user, $password)
    {
        $this->host     = $host;
        $this->dbname   = $dbname;
        $this->user     = $user;
        $this->password = $password;

        $this->connect();
    }


    private function connect()
    {
        $host       = $this->host;
        $dbname     = $this->dbname;
        $user       = $this->user;
        $password   = $this->password;

        try
        {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", "$user", "$password");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('SET NAMES "utf8"');
        }
        catch(PDOException $e)
        {
            echo 'Ошибка!' . $e->getMessage();
            exit();
        }

        $this->pdo = $pdo;
    }




    public function select($sql, array $values = null){

        try
        {
            $s = $this->pdo->prepare($sql);

            if(isset($values)) {
                foreach ($values as $key => $value) {
                    $s->bindValue(":$key", $value);
                }
            }

            $s->execute();
        }
        catch(PDOException $e)
        {
            echo 'Ошибка!' . $e->getMessage();
            exit();
        }

        return $s->fetchAll();
    }
}

class Message
{
    private $parse_mode;
    private $one_time_keyboard;
    private $resize_keyboard;


    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        if (!isset($this->parse_mode)) {
            $this->parse_mode = "HTML";
        }

        if (!isset($this->one_time_keyboard)) {
            $this->one_time_keyboard = true;
        }

        if (!isset($this->resize_keyboard)) {
            $this->resize_keyboard = true;
        }
    }

    public function sendMessage($text, array $keyboard = null)
    {
        $parameters = $this->setKeyboard($keyboard);

        $parameters['method']        = "sendMessage";
        $parameters['text']          = "$text";
        $parameters['chat_id']       = CHAT_ID;
        $parameters['parse_mode']    = $this->parse_mode;
        $handle = curl_init(API_URL);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        return $this->exec_curl_request($handle);
    }


    private function setKeyboard($keyboard)
    {
        if (isset($keyboard)) {
            $parameters['reply_markup'] =
                [
                    'keyboard' => $keyboard,
                    'one_time_keyboard' => $this->one_time_keyboard,
                    'resize_keyboard' => $this->resize_keyboard
                ];
        }

        return $parameters;
    }

    private function exec_curl_request($handle) {
        $response = curl_exec($handle);

        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);

        if ($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);
            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Invalid access token provided');
            }
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successfull: {$response['description']}\n");
            }
            $response = $response['result'];
        }

        return $response;
    }
}



$update = new Update();

if ($update->text == "/start") {
    $request = new Message();
    $text = "Привет, на какой срок показать расписание?";
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}

if ($update->text == "/chatid") {
    $request = new Message();
    $text = CHAT_ID;
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}

if ($update->text == "Сейчас") {

    Holiday::check();

    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);

    $select  = $database->select(Database::$sqlCurDay);
    $text    = Timetable::getNow($select);

    $request = new Message();
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}

if ($update->text == "Сегодня") {

    Holiday::check();

    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);

    $select  = $database->select(Database::$sqlCurDay);
    $text    = Timetable::getToday($select);

    $request = new Message();
    $request->sendMessage($text, Keyboards::$selectDay);

    exit();
}


