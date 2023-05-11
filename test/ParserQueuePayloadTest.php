<?php

namespace Test;

use App\EntityMailMessage;
use App\EntityQueueItem;
use App\ParserQueuePayload;
use PHPUnit\Framework\TestCase;

class ParserQueuePayloadTest extends TestCase
{
    public function testSuccessOneRecipient()
    {
        $currentYear = date('Y');
        $dateTime = "$currentYear-05-01 14:55:37";
        $queueId = '3E06E12011A';
        $payload = "client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su\n";
        $payload .= "header Subject: success message from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<ady@infoservice.ru> proto=ESMTP helo=<mx.it5.su>\n";
        $payload .= "message-id=<823d7c3159c95d10ae658cfeed254b48@ml.it5.su>\n";
        $payload .= "from=<borodin_admin@ml.it5.su>, size=590, nrcpt=1 (queue active)\n";
        $payload .= "to=<ady@infoservice.ru>, relay=mx.yandex.ru[77.88.21.249]:25, delay=1, delays=0.13/0.04/0.17/0.69, dsn=2.0.0, status=sent (250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-10.myt.yp-c.yandex.net 1682952938-btNFgWJPMCg0-4gaCVu2s)\n";
        $payload .= "removed";

        $queueItem = new EntityQueueItem(0, $dateTime, $queueId, $payload);

        $parserQueuePayload = new ParserQueuePayload();

        [$result] = $parserQueuePayload->buildMailMessage($queueItem);

        $expected = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'ady@infoservice.ru',
            subject: 'success message...',
            statusText: '250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-10.myt.yp-c.yandex.net 1682952938-btNFgWJPMCg0-4gaCVu2s',
            statusCode: 250,
            statusName: 'sent',
            nonDeliveryNotificationId: '',
        );

        $this->assertEquals($expected, $result);
    }

    public function testSuccessMultyRecipients()
    {
        $currentYear = date('Y');
        $dateTime = "$currentYear-05-01 18:12:27";
        $queueId = '8EF7912011A';
        $payload = "client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su\n";
        $payload .= "header Subject: success message with copy from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<hiddencopy@rersre.sfds> proto=ESMTP helo=<mx.it5.su>\n";
        $payload .= "message-id=<a52c686233399c7c368cc9a827a0417f@ml.it5.su>\n";
        $payload .= "from=<borodin_admin@ml.it5.su>, size=642, nrcpt=6 (queue active)\n";
        $payload .= "to=<lumumba@fdlsd.ts>, relay=none, delay=0.19, delays=0.14/0.04/0.02/0, dsn=5.4.4, status=bounced (Host or domain name not found. Name service error for name=fdlsd.ts type=A: Host not found)\n";
        $payload .= "to=<hiddencopy@rersre.sfds>, relay=none, delay=0.23, delays=0.14/0.08/0.01/0, dsn=5.4.4, status=bounced (Host or domain name not found. Name service error for name=rersre.sfds type=A: Host not found)\n";
        $payload .= "to=<adyanul@mail.ru>, relay=mxs.mail.ru[217.69.139.150]:25, delay=0.89, delays=0.14/0.08/0.16/0.51, dsn=2.0.0, status=sent (250 OK id=1ptY0i-009HgJ-1h)\n";
        $payload .= "to=<alborodin85@mail.ru>, relay=mxs.mail.ru[217.69.139.150]:25, delay=0.89, delays=0.14/0.08/0.16/0.51, dsn=2.0.0, status=sent (250 OK id=1ptY0i-009HgJ-1h)\n";
        $payload .= "to=<notegsdf@mail.ru>, relay=mxs.mail.ru[217.69.139.150]:25, delay=0.89, delays=0.14/0.08/0.16/0.51, dsn=2.0.0, status=sent (250 OK id=1ptY0i-009HgJ-1h)\n";
        $payload .= "to=<ady@infoservice.ru>, relay=mx.yandex.ru[77.88.21.249]:25, delay=0.91, delays=0.14/0.06/0.19/0.52, dsn=2.0.0, status=sent (250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-16.vla.yp-c.yandex.net 1682964748-RCRUwjJPKCg0-6CAMm4HS)\n";
        $payload .= "sender non-delivery notification: 7518012011C\n";
        $payload .= "removed\n";

        $queueItem = new EntityQueueItem(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            payload: $payload,
        );

        $parserQueuePayload = new ParserQueuePayload();
        $result = $parserQueuePayload->buildMailMessage($queueItem);

        $expected = [];
        $expected[] = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'lumumba@fdlsd.ts',
            subject: 'success message with copy...',
            statusText: 'Host or domain name not found. Name service error for name=fdlsd.ts type=A: Host not found',
            statusCode: 0,
            statusName: 'bounced',
            nonDeliveryNotificationId: '7518012011C',
        );
        $expected[] = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'hiddencopy@rersre.sfds',
            subject: 'success message with copy...',
            statusText: 'Host or domain name not found. Name service error for name=rersre.sfds type=A: Host not found',
            statusCode: 0,
            statusName: 'bounced',
            nonDeliveryNotificationId: '7518012011C',
        );
        $expected[] = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'adyanul@mail.ru',
            subject: 'success message with copy...',
            statusText: '250 OK id=1ptY0i-009HgJ-1h',
            statusCode: 250,
            statusName: 'sent',
            nonDeliveryNotificationId: '7518012011C',
        );
        $expected[] = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'alborodin85@mail.ru',
            subject: 'success message with copy...',
            statusText: '250 OK id=1ptY0i-009HgJ-1h',
            statusCode: 250,
            statusName: 'sent',
            nonDeliveryNotificationId: '7518012011C',
        );
        $expected[] = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'notegsdf@mail.ru',
            subject: 'success message with copy...',
            statusText: '250 OK id=1ptY0i-009HgJ-1h',
            statusCode: 250,
            statusName: 'sent',
            nonDeliveryNotificationId: '7518012011C',
        );
        $expected[] = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'ady@infoservice.ru',
            subject: 'success message with copy...',
            statusText: '250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-16.vla.yp-c.yandex.net 1682964748-RCRUwjJPKCg0-6CAMm4HS',
            statusCode: 250,
            statusName: 'sent',
            nonDeliveryNotificationId: '7518012011C',
        );

        $this->assertEquals($expected, $result);
    }

    public function testSuccessWhithRussianSubject()
    {
        $currentYear = date('Y');
        $dateTime = "$currentYear-05-01 20:01:01";
        $queueId = 'EDE0212011A';
        $payload = "client=mx.it5.su[91.223.89.239], sasl_method=LOGIN, sasl_username=borodin_admin@ml.it5.su\n";
        $payload .= "header Subject: =?UTF-8?Q?=D0=9F=D0=B8=D1=81=D1=8C=D0=BC=D0=BE_=D1=81_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=BE=D0=B9_=D0=BD=D0=B0_=D1=80=D1=83=D1=81=D1=81?=? =?UTF-8?Q?=D0=BA=D0=BE=D0=BC_=D1=8F=D0=B7=D1=8B=D0= from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<ady@infoservice.ru> proto=ESMTP helo=<mx.it5.su>\n";
        $payload .= "message-id=<c05b71bd1968aa4cfe31c0169f66227f@ml.it5.su>\n";
        $payload .= "from=<borodin_admin@ml.it5.su>, size=817, nrcpt=1 (queue active)\n";
        $payload .= "to=<ady@infoservice.ru>, relay=mx.yandex.ru[77.88.21.249]:25, delay=0.9, delays=0.13/0.03/0.15/0.59, dsn=2.0.0, status=sent (250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-88.vla.yp-c.yandex.net 1682971262-21Tnj9KYVeA0-Z8z70YDe)\n";
        $payload .= "removed";

        $queueItem = new EntityQueueItem(0, $dateTime, $queueId, $payload);

        $parserQueuePayload = new ParserQueuePayload();

        [$result] = $parserQueuePayload->buildMailMessage($queueItem);

        $expected = new EntityMailMessage(
            id: 0,
            dateTime: $dateTime,
            queueId: $queueId,
            from: 'borodin_admin@ml.it5.su',
            to: 'ady@infoservice.ru',
            subject: 'Письмо с темой на русском язы...',
            statusText: '250 2.0.0 Ok: queued on mail-nwsmtp-mxfront-production-main-88.vla.yp-c.yandex.net 1682971262-21Tnj9KYVeA0-Z8z70YDe',
            statusCode: 250,
            statusName: 'sent',
            nonDeliveryNotificationId: '',
        );

        $this->assertEquals($expected, $result);
    }
}
