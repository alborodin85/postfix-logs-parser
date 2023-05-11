<?php

namespace Test;

use App\EntityLogRow;
use App\ParserRows;
use PHPUnit\Framework\TestCase;

class ParserRowsTest extends TestCase
{
    public function testQueueError()
    {
        $sourceLine = 'Apr 30 10:38:35 mx postfix/cleanup[672328]: ADF54120109: warning: header Subject: =?utf-8?B?UmU6INCV0YnQtSDQvtC00L3QsCDQv9GA0L7QstC10YDQutCwIERLSU0=?= from forward500a.mail.yandex.net[178.154.239.80]; from=<ady@infoservice.ru> to=<borodin_admin@ml.it5.su> proto=ESMTP helo=<forward500a.mail.yandex.net>';
        $expected = new EntityLogRow(
            id: 0,
            dateTime: '2023-04-30 07:38:35',
            hostName: 'mx',
            module: 'postfix/cleanup',
            procId: 672328,
            queueId: 'ADF54120109',
            errorLevel: 'warning',
            rowText: 'header Subject: =?utf-8?B?UmU6INCV0YnQtSDQvtC00L3QsCDQv9GA0L7QstC10YDQutCwIERLSU0=?= from forward500a.mail.yandex.net[178.154.239.80]; from=<ady@infoservice.ru> to=<borodin_admin@ml.it5.su> proto=ESMTP helo=<forward500a.mail.yandex.net>'
        );
        $rowParser = new ParserRows();
        $result = $rowParser->parse($sourceLine);
        $this->assertEquals($expected, $result);
    }

    public function testErrorLevel()
    {
        $sourceLine = 'Apr 30 10:25:22 mx postfix/anvil[671566]: statistics: max connection count 1 for (smtp:178.154.239.210) at Apr 30 10:21:49';
        $expected = new EntityLogRow(
            id: 0,
            dateTime: '2023-04-30 07:25:22',
            hostName: 'mx',
            module: 'postfix/anvil',
            procId: 671566,
            queueId: '',
            errorLevel: 'statistics',
            rowText: 'max connection count 1 for (smtp:178.154.239.210) at Apr 30 10:21:49'
        );
        $rowParser = new ParserRows();
        $result = $rowParser->parse($sourceLine);
        $this->assertEquals($expected, $result);
    }

    public function testQueueId()
    {
        $sourceLine = 'Apr 30 10:38:35 mx postfix/smtpd[672322]: ADF54120109: client=forward500a.mail.yandex.net[178.154.239.80]';
        $expected = new EntityLogRow(
            id: 0,
            dateTime: '2023-04-30 07:38:35',
            hostName: 'mx',
            module: 'postfix/smtpd',
            procId: 672322,
            queueId: 'ADF54120109',
            errorLevel: '',
            rowText: 'client=forward500a.mail.yandex.net[178.154.239.80]'
        );
        $rowParser = new ParserRows();
        $result = $rowParser->parse($sourceLine);
        $this->assertEquals($expected, $result);
    }

    public function testSimpleMessage()
    {

        $sourceLine = 'Apr 30 10:38:35 mx postfix/smtpd[672322]: disconnect from forward500a.mail.yandex.net[178.154.239.80] ehlo=2 starttls=1 mail=1 rcpt=1 data=1 quit=1 commands=7';
        $expected = new EntityLogRow(
            id: 0,
            dateTime: '2023-04-30 07:38:35',
            hostName: 'mx',
            module: 'postfix/smtpd',
            procId: 672322,
            queueId: '',
            errorLevel: '',
            rowText: 'disconnect from forward500a.mail.yandex.net[178.154.239.80] ehlo=2 starttls=1 mail=1 rcpt=1 data=1 quit=1 commands=7'
        );
        $rowParser = new ParserRows();
        $result = $rowParser->parse($sourceLine);
        $this->assertEquals($expected, $result);
    }

    public function testIncorrectLine()
    {
        $sourceLine = 'incorrect source line';
        $expected = new EntityLogRow(
            id: 0,
            dateTime: date('Y-m-d 00:00:00'),
            hostName: '',
            module: '',
            procId: 0,
            queueId: '',
            errorLevel: 'incorrect_line',
            rowText: $sourceLine,
        );
        $rowParser = new ParserRows();
        $result = $rowParser->parse($sourceLine);
        $this->assertEquals($expected, $result);
    }
}
