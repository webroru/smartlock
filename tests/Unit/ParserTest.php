<?php

namespace tests\App\Unit;

use App\Services\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /** @var string */
    private const MAIL = '<div style="font-family:Arial,sans-serif;margin:0;padding:30px 0;border-top:1px solid #e4e4e4;display:inline-block;width:100%;vertical-align:top;font-size:15px"> <h2 style="font-family:Arial,sans-serif;margin:0;padding:0 0 12px 0;font-size:26px;font-weight:normal;line-height:30px"> Бронирование                            <span style="color:#00b520;font-weight:bold">подтверждено</span> </h2> <div style="margin-top:30px"> <h3 style="margin:0;padding:0 0 10px 0;font-size:20px;font-weight:bold"> Бронирование №                                XKLKE_120720                 Двухместный номер с общей ванной комнатой            </h3> <table style="width:100%;border-collapse:collapse"> <tbody> <tr style="border-bottom:1px solid #e4e4e4"> <td style="padding:16px 30px"> Ваше бронирование                    </td> <td style="padding:16px 30px;text-align:right"> 3 ночи: 1 номер                    </td> </tr> <tr style="border-bottom:1px solid #e4e4e4"> <td style="padding:16px 30px"> Даты проживания                    </td> <td style="padding:16px 30px;text-align:right"> 14 июля 2020 14:00 — 17 июля 2020 12:00                     </td> </tr> <tr style="border-bottom:1px solid #e4e4e4"> <td style="padding:16px 30px"> Гости                    </td> <td style="padding:16px 30px;text-align:right"> 2 Взрослых                    </td> </tr> <tr style="border-bottom:1px solid #e4e4e4;background-color:rgba(0,0,0,.05)"> <td style="padding:16px 30px"> Проживание                    </td> <td style="padding:16px 30px;text-align:right"> 72.54 EUR                    </td> </tr> <tr style="border-bottom:1px solid #e4e4e4;font-size:20px;font-weight:bold"> <td style="background-color:rgba(0,0,0,.05);padding:16px 30px"> Общая стоимость                    </td> <td style="background-color:rgba(0,0,0,.05);padding:16px 30px;text-align:right"> 72.54 EUR                    </td> </tr> </tbody> </table> </div> <div style="margin-top:72px"> <h3 style="margin:0;padding:0 0 10px 0;font-size:20px;font-weight:bold"> Детали заказа            </h3> <div style="background-color:#f6f6f6;padding:20px 30px"> <table style="width:100%"> <tbody> <tr> <td>Дата бронирования</td> <td style="text-align:right">12-07-2020 19:56:55 (Europe/Ljubljana)</td> </tr> <tr> <td>Тарифный план</td> <td style="text-align:right">Низкая цена. Полная предоплата, без возможности отменить бронь. (+8 евро уборка, +3,13 евро за человека/ночь городской налог)</td> </tr> <tr> <td>Заказчик</td> <td style="text-align:right">Alen Mahmutovic</td> </tr> <tr> <td>Телефон</td> <td style="text-align:right">+386-40-844-057</td> </tr> <tr> <td>Эл. почта</td> <td style="text-align:right"><a href="mailto:1111@mail.ru" style="color:#27579e;text-decoration:underline" target="_blank">1111@mail.ru</a></td> </tr> </tbody> </table> </div> </div> <div style="margin-top:77px"> <h3 style="margin:0;padding:0 0 10px 0;font-size:20px;font-weight:bold"> Примечания в бронировании                </h3> <p style="font-size:15px;line-height:23px;margin:0 0 20px 0"> Понедельник обещал оплатить                </p> </div> <div style="margin-top:77px"> <h3 style="margin:0;padding:0 0 10px 0;font-size:20px;font-weight:bold"> Описание тарифа                </h3> <p style="font-size:15px;line-height:23px;margin:0 0 20px 0"> Полная предоплата, но вы уверены что это самая низкая цена. Отмена или возврат денег не возможен. Завтрак +5 евро с человека (8:00-10:00).В стоимость не входит уборка 8 евро (кроме комнаты эконом класса и хостела). В стоимость не входит городской налог 3,13евро с человека за ночь (дети 1,26 евро).                </p> <div style="margin-bottom:40px"> <p style="font-size:15px;line-height:23px;margin-bottom:0"> <b>Гарантия бронирования</b><br> </p> <p style="font-size:15px;line-height:23px;margin:0 0 20px 0"> Для бронирования требуется оплата в размере 100.00% от общей стоимости. </p> </div> <h3 style="margin:0;padding:0 0 10px 0;font-size:20px;font-weight:bold">Отмена бронирования</h3> <p> При отмене брони или незаезде, деньги не возвращаются.                </p> <p style="font-size:15px;line-height:23px;margin:0 0 20px 0"> Для отмены бронирования                    <a href="https://reservationsteps.ru/cancel_bookings/index/bcf7e896-f566-477a-afcc-3fa767509d78?bookingNumber=XKLKE_120720&amp;cancelEmail=1111@mail.ru&amp;lang=en" style="color:#27579e;text-decoration:underline" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://reservationsteps.ru/cancel_bookings/index/bcf7e896-f566-477a-afcc-3fa767509d78?bookingNumber%3DXKLKE_120720%26cancelEmail%3D1111@mail.ru%26lang%3Den&amp;source=gmail&amp;ust=1596392486552000&amp;usg=AFQjCNHbFGHef4eKTSyyFIMN_4LU3i1x5w">перейдите по ссылке</a> и введите ваш номер бронирования                    <b>№&nbsp;XKLKE_120720</b>. </p><font color="#888888"> </font></div><font color="#888888"> </font></div>';
    private const CHECKIN_DATE = '14 July 2020 14:00';
    private const CHECKOUT_DATE = '17 July 2020 12:00';
    private const GUEST_NAME = 'Alen Mahmutovic';
    private const EMAIL = '1111@mail.ru';
    private const PHONE = '+386-40-844-057';
    private const ORDER_ID = 'XKLKE_120720';

    /** @var Parser */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser(self::MAIL);
    }

    public function testGetCheckOutDate(): void
    {
        $this->assertEquals(self::CHECKOUT_DATE, $this->parser->getCheckOutDate());
    }

    public function testGetGuestName(): void
    {
        $this->assertEquals(self::GUEST_NAME, $this->parser->getGuestName());
    }

    public function testGetCheckInDate(): void
    {
        $this->assertEquals(self::CHECKIN_DATE, $this->parser->getCheckInDate());
    }

    public function testGetEmail(): void
    {
        $this->assertEquals(self::EMAIL, $this->parser->getEmail());
    }

    public function testIsChanged(): void
    {
        $this->assertFalse($this->parser->isChanged());
    }

    public function testGetPhone(): void
    {
        $this->assertEquals(self::PHONE, $this->parser->getPhone());
    }

    public function testGetOrderId(): void
    {
        $this->assertEquals(self::ORDER_ID, $this->parser->getOrderId());
    }
}
