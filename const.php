<?php

//текущая дата
define("CUR_DATE", date("U") + 3 * 60 * 60);


//текущее время
define("CUR_TIME", date ('H:i', CUR_DATE));


//четная или нечетная неделя
if(date("W", CUR_DATE) % 2 == 0) $cur_week = 2;
else $cur_week = 1;

define("CUR_WEEK", $cur_week);


//сегодня
define("CUR_DAY", strtolower( date("l", CUR_DATE)));


//завтра
define("TOMORROW", strtolower( date("l", CUR_DATE + 24 *60 * 60)));


//URL сайта
define('WEBHOOK_URL','https://pi-timetable.herokuapp.com/');


//token бота
define('API_URL', 'https://api.telegram.org/bot' . '255704702:AAGm_IG22M0tBeWp8JfhYKxj0EJFe18-IQQ' . '/');


//database
const DB_HOST     = 'server27.hosting.reg.ru';
const DB_NAME     = 'u0167959_timetable';
const DB_USER     = 'u0167959_status';
const DB_PASSWORD = 'Pasha635';
