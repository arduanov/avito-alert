<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maknz\Slack\Attachment;
use Maknz\Slack\AttachmentField;

class SlackTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slack:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $attachment = new Attachment([
            'fallback' => 'Some fallback text',
            'text' => 'The attachment text',
            'fields' => [
                new AttachmentField([
                    'title' => 'A title',
                    'value' => 'A value',
                    'short' => true
                ])
            ]
        ]);

        \Slack::createMessage()
              ->attach([
                  'text' => '
                  *title*
                  Цена: 18,000
Игорь

Телевизор в отличном состоянии,всё работает! Нет очков, потерял при переезде. 
Основные характеристики ЖК телевизора LG 47LK950
Страна    Россия
ЭКРАН
Диагональ    47 дюймов 
Тип матрицы    IPS
Формат    16:9
Full HD    Есть Другие товары
Разрешение    1920x1080 Пикс
Контрастность    150000:1
Угол обзора по горизонтали    178 °
Угол обзора по вертикали    178 °
Время отклика    2 мс
Прогрессивная развертка    Есть
ФУНКЦИИ
Воспроизведение видео через USB    Есть 
Таймер выключения    Есть
Часы    Есть
Защита от детей    Есть
Русифицированное меню    Есть
Телетекст    Есть
Гид по программам    Есть
ОБРАБОТКА ИЗОБРАЖЕНИЯ
Цифровое шумоподавление    Есть
Технологии улучшения изображения    Tru Motion 100 Hz
НАСТРОЙКА ИЗОБРАЖЕНИЯ
Масштабирование    Есть
Изменение формата    Есть
Регулировка контрастности    Есть
Регулировка температуры цвета    Есть
Предустановки изображения    Есть
ТЮНЕР
Количество тюнеров    1
Цветовая система    PAL, SECAM, NTSC
Автоматическая настройка    Есть
Ручная настройка    Есть
ЗВУКОВАЯ СИСТЕМА
Кол-во встроенных динамиков    2
Мощность    2х10 Вт
Система окружающего звучания    Есть
Предустановки    Есть
Технологии улучшения звука    Clear Voice II
ИНТЕРФЕЙСЫ
Композитный вход    Есть
SCART-RGB    Есть
Компонентный    Есть
HDMI    2 шт.
Вход для ПК    Есть
Оптический аудиовыход    Есть
Выход для наушников    Есть
USB    Есть
КОМПЛЕКТАЦИЯ
Пульт ДУ    Есть
Подставка    Есть
ГАБАРИТЫ
Высота с подставкой    75.8 см
Глубина с подставкой    27 см
Ширина с подставкой    113.7 см
Высота    68.5 см
Глубина    7.6 см
Ширина    113.7 см
Вес с подставкой    20.3 кг
Вес    18.3 кг
Дополнительная информация    4 пары 3D очков в комплекте, конвертация 2D видео в 3D
Цвет    черный',
                  'image_url' => 'https://avatars1.githubusercontent.com/u/8693029?v=3&s=460'
              ])
              ->send();;
    }
}
