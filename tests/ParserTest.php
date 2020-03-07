<?php

namespace App\tests;

use App\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /** @var string */
    const MAIL = '<table border="0" align="center" width="510" cellpadding="0" cellspacing="0" bgcolor="ffffff"> <tbody><tr> <td style="padding:0px 0px 0px 30px" align="left"> <a style="border-style:none!important;border:0!important"><img width="150" border="0" style="display:block;margin:20px 0px 25px 0px;width:150px" src="https://ci6.googleusercontent.com/proxy/gzhXeXMvlyRwuopQLSvqHGVJBdLNr_roNclwc53JhmCxz5pSKUP6e5Hjx03_6tz5pByV6vrUz7oigRghI0LBitBy2Xo1404Nki9mCQA=s0-d-e1-ft#https://www.otelms.com/wp-content/uploads/2018/06/logo.png" class="CToWUd"></a> </td> <td align="center" style="color:#1c2029;font-size:18px;font-family:\'Varela Round\',sans-serif;line-height:24px;font-weight:bold"> New booking #15841 </td> </tr> <tr> <td colspan="2"> <table border="0" width="440" align="center" cellpadding="0" cellspacing="0"> <tbody><tr> <td align="center" style="color:#737b8c;font-size:17px;font-family:\'Varela Round\',sans-serif;line-height:24px"> <div style="line-height:24px;padding-bottom:10px"> <span style="color:#1c2029"> </span></div> </td> </tr> </tbody></table> </td> </tr> <tr> <td colspan="2" align="center"> <table border="0" align="center" width="220" cellpadding="0" cellspacing="0" bgcolor="2fbbc8" style="margin:5px 0px 15px 0px;border-radius:3px;background:#4f6df5"> <tbody><tr><td height="13" style="font-size:13px;line-height:13px">&nbsp;</td></tr> <tr> <td align="center" style="color:#ffffff;font-size:14px;font-family:\'Varela Round\',sans-serif"> <div style="line-height:24px"> <a href="https://greenslosi.otelms.com/reservations/ReservationViewForm/15841" title="" style="font-size:17px;color:#ffffff;text-decoration:none;padding:0px 10px" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://greenslosi.otelms.com/reservations/ReservationViewForm/15841&amp;source=gmail&amp;ust=1577361723110000&amp;usg=AFQjCNF5KkH99FmOaFy23XAJbktd73TIUg">View booking</a> </div> </td> </tr> <tr><td height="13" style="font-size:13px;line-height:13px">&nbsp;</td></tr> </tbody></table> </td> </tr> <tr> <td colspan="2" style="color:#737b8c;font-size:15px;font-family:\'Varela Round\',sans-serif;line-height:24px;padding:20px 30px 0px 30px"> <table style="width:100%"> <tbody> <tr> <td colspan="1" nowrap=""> <div style="color:#1c2029;font-size:24px;font-family:\'Varela Round\',sans-serif;line-height:30px"><img width="510" border="0" style="display:block;width:150px" src="https://ci3.googleusercontent.com/proxy/W9vuGI9v9HFHD2V1honp9e5r-1v1_3AM4r0_MMEQ6jRSQnmDjD2oIsuSEtaEXjJfsQ1_gHVJdNT-U5WCofthD-Q-ODerWBhGfWNtzws=s0-d-e1-ft#https://greenslosi.otelms.com/images/dc_logo/dc_logo_1.png" class="CToWUd"></div> </td> <td colspan="2" align="right"> <div style="color:#1c2029;font-size:16px;font-family:\'Varela Round\',sans-serif;line-height:22px"><b>New booking</b><br><b>3060027592 - 2739509449</b></div> </td> </tr> <tr style="display:block;margin-bottom:10px"></tr> <tr> <td><b>Date:</b></td> <td style="text-align:right" colspan="2">2019-12-24</td> </tr> <tr> <td><b>Check-in:</b></td> <td style="text-align:right" colspan="2">2019-12-24</td> </tr> <tr> <td><b>Check-out:</b></td> <td style="text-align:right" colspan="2">2019-12-25</td> </tr> <tr> </tr><tr> <td><b>Days:</b></td> <td style="text-align:right" colspan="2">1</td> </tr> <tr> <td style="vertical-align:text-top"><b>Room type:</b></td> <td style="text-align:right" colspan="2">Bed in 4-Bed Dormitory Room (327510201) </td> </tr> <tr style="padding-bottom:10px;display:block"> <td></td> </tr> <tr style="color:#1c2029;font-size:15px;font-family:\'Varela Round\',sans-serif;font-weight:normal;line-height:20px"> <td colspan="4" style="background:#f5f5f5;padding:10px"> <b>Guest wishes:</b><br> <span style="font-size:12px;line-height:17px">This guest has requested a receipt for their stay.</span> </td> </tr> <tr> <td colspan="4"><hr style="border-top:0px solid #f2f4f5"></td> </tr> <tr> <td colspan="4"> <table style="margin:0;border-collapse:collapse;width:100%"> <tbody> <tr> <th style="vertical-align:bottom;border-bottom:2px solid rgb(242,244,245);padding:4px 0px;text-align:left;font-size:15px">Rate</th> <th style="vertical-align:bottom;border-bottom:2px solid rgb(242,244,245);padding:4px 0px;text-align:left;font-size:15px">Date</th> <th style="vertical-align:bottom;border-bottom:2px solid rgb(242,244,245);padding:4px 0px;text-align:left;font-size:15px;text-align:right">Price</th> </tr> <tr> <td style="line-height:1.54;vertical-align:top;padding:8px 0px;border-top:1px solid rgb(242,244,245);font-size:14px">host soft 30 (11201209)</td> <td style="line-height:1.54;vertical-align:top;padding:8px 0px;border-top:1px solid rgb(242,244,245);font-size:14px">2019-12-24</td> <td style="line-height:1.54;vertical-align:top;padding:8px 0px;border-top:1px solid rgb(242,244,245);font-size:14px;text-align:right">8.10 (€)</td> </tr> </tbody> <tfoot> <tr> <td style="line-height:1.54;vertical-align:top;padding:8px 0px;border-top:1px solid rgb(242,244,245);font-size:14px"></td> <td style="line-height:1.54;vertical-align:top;padding:8px 0px;border-top:1px solid rgb(242,244,245);font-size:14px;text-align:right"><b>Amount due:</b></td> <td style="line-height:1.54;vertical-align:top;padding:8px 0px;border-top:1px solid rgb(242,244,245);font-size:14px;text-align:right"><b>8.10 (€)</b></td> </tr> </tfoot> </table> </td> </tr> <tr> <td colspan="4"><hr style="border-top:0px solid #f2f4f5"></td> </tr> <tr> <td><b>Booked for:</b></td> <td style="text-align:right" colspan="2">YI ZHANG</td> </tr> <tr> <td><b>Phone:</b></td> <td style="text-align:right" colspan="2">+420910763556</td> </tr> <tr> <td><b>E-mail:</b></td> <td style="text-align:right" colspan="2"><a href="mailto:yzhang.289577@guest.booking.com" target="_blank">yzhang.289577@guest.booking.com</a></td> </tr> </tbody> </table> </td> </tr> <tr><td height="30" style="font-size:30px;line-height:30px">&nbsp;</td></tr> </tbody></table>';
    const CHECKIN_DATE = '2019-12-24';
    const CHECKOUT_DATE = '2019-12-25';
    const GUEST_NAME = 'YI ZHANG';
    const EMAIL = 'yzhang.289577@guest.booking.com';
    const PHONE = '+420910763556';
    const ORDER_ID = '3060027592 - 2739509449';


    /** @var Parser */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser(self::MAIL);
    }

    public function test__construct(): void
    {
        $this->assertInstanceOf(
            Parser::class,
            ($this->parser)
        );
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
