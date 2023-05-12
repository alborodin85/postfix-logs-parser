<?php

namespace Test;

use App\ComposerByQueues;
use App\EntityQueueItem;
use App\ParserRows;
use PHPUnit\Framework\TestCase;

class ComposerByQueuesTest extends TestCase
{
    public function testBuildQueuesSuccessMessage()
    {
        $lines = [];
        $lines[] = 'May 01 17:55:37 mx postfix/smtps/smtpd[760734]: 3E06E12011A: client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su';
        $lines[] = 'May 01 17:55:37 mx postfix/cleanup[760738]: 3E06E12011A: warning: header Subject: success message from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<ady@infoservice.ru> proto=ESMTP helo=<mx.it5.su>';
        $lines[] = 'May 01 17:55:37 mx postfix/cleanup[760738]: 3E06E12011A: message-id=<823d7c3159c95d10ae658cfeed254b48@ml.it5.su>';
        $lines[] = 'May 01 17:55:37 mx postfix/qmgr[710020]: 3E06E12011A: from=<borodin_admin@ml.it5.su>, size=590, nrcpt=1 (queue active)';
        $lines[] = 'May 01 17:55:38 mx postfix/smtp[760739]: 3E06E12011A: to=<ady@infoservice.ru>, relay=mx.yandex.ru[77.88.21.249]:25, delay=1, delays=0.13/0.04/0.17/0.69, dsn=2.0.0, status=sent (250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-10.myt.yp-c.yandex.net 1682952938-btNFgWJPMCg0-4gaCVu2s)';
        $lines[] = 'May 01 17:55:38 mx postfix/qmgr[710020]: 3E06E12011A: removed';

        $logRows = [];
        $rowsParser = new ParserRows();
        foreach ($lines as $sourceLine) {
            $logRows[] = $rowsParser->parseLine($sourceLine);
        }

        $composer = new ComposerByQueues();
        [$result] = $composer->buildQueues($logRows);

        $currentYear = date('Y');
        $expectedDate = "$currentYear-05-01 14:55:37";
        $expectedQueueId = '3E06E12011A';
        $expectedPayload = "client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su\n";
        $expectedPayload .= "header Subject: success message from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<ady@infoservice.ru> proto=ESMTP helo=<mx.it5.su>\n";
        $expectedPayload .= "message-id=<823d7c3159c95d10ae658cfeed254b48@ml.it5.su>\n";
        $expectedPayload .= "from=<borodin_admin@ml.it5.su>, size=590, nrcpt=1 (queue active)\n";
        $expectedPayload .= "to=<ady@infoservice.ru>, relay=mx.yandex.ru[77.88.21.249]:25, delay=1, delays=0.13/0.04/0.17/0.69, dsn=2.0.0, status=sent (250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-10.myt.yp-c.yandex.net 1682952938-btNFgWJPMCg0-4gaCVu2s)\n";
        $expectedPayload .= "removed";

        $expected = new EntityQueueItem(0, $expectedDate, $expectedQueueId, $expectedPayload);

        $this->assertEquals($expected, $result);
    }

    public function testBuildQueuesUnsaccessMessage()
    {
        $lines = [];
        $lines[] = 'May 01 17:56:04 mx postfix/smtps/smtpd[760734]: 9A7F512011A: client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su';
        $lines[] = 'May 01 17:56:04 mx postfix/cleanup[760738]: 9A7F512011A: warning: header Subject: non success message from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<adyanul34@mail.ru> proto=ESMTP helo=<mx.it5.su>';
        $lines[] = 'May 01 17:56:04 mx postfix/cleanup[760738]: 9A7F512011A: message-id=<3bcca813daad678646389a0dd4ba6692@ml.it5.su>';
        $lines[] = 'May 01 17:56:04 mx postfix/qmgr[710020]: 9A7F512011A: from=<borodin_admin@ml.it5.su>, size=596, nrcpt=1 (queue active)';
        $lines[] = 'May 01 17:56:05 mx postfix/smtp[760739]: 9A7F512011A: to=<adyanul34@mail.ru>, relay=mxs.mail.ru[217.69.139.150]:25, delay=0.64, delays=0.12/0/0.26/0.25, dsn=5.0.0, status=bounced (host mxs.mail.ru[217.69.139.150] said: 550 Message was not accepted -- invalid mailbox.  Local mailbox adyanul34@mail.ru is unavailable: user not found (in reply to end of DATA command))';
        $lines[] = 'May 01 17:56:05 mx postfix/bounce[760757]: 9A7F512011A: sender non-delivery notification: 4498A12011C';
        $lines[] = 'May 01 17:56:05 mx postfix/qmgr[710020]: 9A7F512011A: removed';

        $logRows = [];
        $rowsParser = new ParserRows();
        foreach ($lines as $sourceLine) {
            $logRows[] = $rowsParser->parseLine($sourceLine);
        }

        $composer = new ComposerByQueues();
        [$result] = $composer->buildQueues($logRows);

        $currentYear = date('Y');
        $expectedDate = "$currentYear-05-01 14:56:04";
        $expectedQueueId = '9A7F512011A';
        $expectedPayload = "client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su\n";
        $expectedPayload .= "header Subject: non success message from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<adyanul34@mail.ru> proto=ESMTP helo=<mx.it5.su>\n";
        $expectedPayload .= "message-id=<3bcca813daad678646389a0dd4ba6692@ml.it5.su>\n";
        $expectedPayload .= "from=<borodin_admin@ml.it5.su>, size=596, nrcpt=1 (queue active)\n";
        $expectedPayload .= "to=<adyanul34@mail.ru>, relay=mxs.mail.ru[217.69.139.150]:25, delay=0.64, delays=0.12/0/0.26/0.25, dsn=5.0.0, status=bounced (host mxs.mail.ru[217.69.139.150] said: 550 Message was not accepted -- invalid mailbox.  Local mailbox adyanul34@mail.ru is unavailable: user not found (in reply to end of DATA command))\n";
        $expectedPayload .= "sender non-delivery notification: 4498A12011C\n";
        $expectedPayload .= "removed";

        $expected = new EntityQueueItem(0, $expectedDate, $expectedQueueId, $expectedPayload);

        $this->assertEquals($expected, $result);
    }

    public function testBuildQueuesUnsaccessMessage2()
    {
        $lines = [];
        $lines[] = 'May 01 20:42:22 mx postfix/smtps/smtpd[768996]: 22D9112011A: client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su';
        $lines[] = 'May 01 20:42:22 mx postfix/cleanup[769000]: 22D9112011A: warning: header Subject: =?UTF-8?Q?=D0=9D=D0=B5=D1=83=D0=B4=D0=B0=D1=87=D0=BD=D0=BE=D0=B5?=? =?UTF-8?Q?_=D0=BF=D0=B8=D1=81=D1=8C=D0=BC=D0=BE=2C_=D0=BA=D0=BE=D1=82?=? =?UTF-8?Q?=D0=BE=D1=80=D0=BE=D0=B5_=D0=BF=D1=8B=D1 from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<dslkjsdaf@lkfsdalk.rsdf> proto=ESMTP helo=<mx.it5.su>';
        $lines[] = 'May 01 20:42:22 mx postfix/cleanup[769000]: 22D9112011A: message-id=<25304f506066b4ab3f87e03adec0cb69@ml.it5.su>';
        $lines[] = 'May 01 20:42:22 mx postfix/qmgr[710020]: 22D9112011A: from=<borodin_admin@ml.it5.su>, size=1215, nrcpt=1 (queue active)';
        $lines[] = 'May 01 20:42:22 mx postfix/smtp[769001]: 22D9112011A: to=<dslkjsdaf@lkfsdalk.rsdf>, relay=none, delay=0.17, delays=0.13/0.03/0.01/0, dsn=5.4.4, status=bounced (Host or domain name not found. Name service error for name=lkfsdalk.rsdf type=A: Host not found)';
        $lines[] = 'May 01 20:42:22 mx postfix/bounce[769003]: 22D9112011A: sender non-delivery notification: 4C9D912011C';
        $lines[] = 'May 01 20:42:22 mx postfix/qmgr[710020]: 22D9112011A: removed';

        $logRows = [];
        $rowsParser = new ParserRows();
        foreach ($lines as $sourceLine) {
            $logRows[] = $rowsParser->parseLine($sourceLine);
        }

        $composer = new ComposerByQueues();
        [$result] = $composer->buildQueues($logRows);

        $currentYear = date('Y');
        $expectedDate = "$currentYear-05-01 17:42:22";
        $expectedQueueId = '22D9112011A';
        $expectedPayload = "client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su\n";
        $expectedPayload .= "header Subject: =?UTF-8?Q?=D0=9D=D0=B5=D1=83=D0=B4=D0=B0=D1=87=D0=BD=D0=BE=D0=B5?=? =?UTF-8?Q?_=D0=BF=D0=B8=D1=81=D1=8C=D0=BC=D0=BE=2C_=D0=BA=D0=BE=D1=82?=? =?UTF-8?Q?=D0=BE=D1=80=D0=BE=D0=B5_=D0=BF=D1=8B=D1 from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<dslkjsdaf@lkfsdalk.rsdf> proto=ESMTP helo=<mx.it5.su>\n";
        $expectedPayload .= "message-id=<25304f506066b4ab3f87e03adec0cb69@ml.it5.su>\n";
        $expectedPayload .= "from=<borodin_admin@ml.it5.su>, size=1215, nrcpt=1 (queue active)\n";
        $expectedPayload .= "to=<dslkjsdaf@lkfsdalk.rsdf>, relay=none, delay=0.17, delays=0.13/0.03/0.01/0, dsn=5.4.4, status=bounced (Host or domain name not found. Name service error for name=lkfsdalk.rsdf type=A: Host not found)\n";
        $expectedPayload .= "sender non-delivery notification: 4C9D912011C\n";
        $expectedPayload .= "removed";

        $expected = new EntityQueueItem(0, $expectedDate, $expectedQueueId, $expectedPayload);

        $this->assertEquals($expected, $result);
    }

    public function testNoQueues()
    {
        $lines = [];
        $lines[] = 'May 11 22:02:26 mx postfix/smtpd[423568]: NOQUEUE: reject: RCPT from unknown[189.154.202.217]: 454 4.7.1 <test@hostxbay.com>: Relay access denied; from=<nouth@mx.it5.su> to=<test@hostxbay.com> proto=ESMTP helo=<INMOBILIARIA>';

        $logRows = [];
        $rowsParser = new ParserRows();
        foreach ($lines as $sourceLine) {
            $logRows[] = $rowsParser->parseLine($sourceLine);
        }

        $composer = new ComposerByQueues();
        [$result] = $composer->buildQueues($logRows);

        $this->assertNull($result);
    }
}
