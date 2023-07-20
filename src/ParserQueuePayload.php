<?php

namespace App;

class ParserQueuePayload
{
    public function parseQueuesArray(array $queuesArray): array
    {
        $mailsArray = [];
        foreach ($queuesArray as $queueItem) {
            $mails = $this->buildMailMessage($queueItem);
            $mailsArray = array_merge($mailsArray, $mails);
        }

        return $mailsArray;
    }

    public function buildMailMessage(EntityQueueItem $queueItem): array
    {
        $payload = $queueItem->payload;
        $payloadArray = explode("\n", $queueItem->payload);

        $id = 0;
        $dateTime = $queueItem->dateTime;
        $queueId = $queueItem->queueId;

        // from=<borodin_admin@ml.it5.su>, size=642, nrcpt=6 (queue active)\n
        $fromMatches = [];
        $fromPattern = '/^from=(.*?),.*/m';
        $fromResult = preg_match($fromPattern, $payload, $fromMatches);
        $from = '<>';
        if ($fromResult) {
            $from = $fromMatches[1];
            $from = str_replace(['<', '>'], '', $from);
        }

        // sender non-delivery notification: 7518012011C\n
        $nonDeliveryNotificationIdMatches = [];
        $nonDeliveryNotificationIdPattern = '/^sender non-delivery notification: (.*)$/m';
        $nonDeliveryNotificationIdResult = preg_match($nonDeliveryNotificationIdPattern, $payload, $nonDeliveryNotificationIdMatches);
        $nonDeliveryNotificationId = '';
        if ($nonDeliveryNotificationIdResult) {
            $nonDeliveryNotificationId = $nonDeliveryNotificationIdMatches[1];
        }

        $subject = '';
        $mailSubjectConverter = new ParserMailSubject();
        // header Subject: success message with copy from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<hiddencopy@rersre.sfds> proto=ESMTP helo=<mx.it5.su>\n
        // header Subject: =?UTF-8?Q?=D0=94=D0=BB=D0=B8=D0=BD=D0=BD=D0=B0=D1=8F_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=B0_=D1=81=D0=BE_=D0=B7=D0=BD=D0=B0=D0=BA=D0=B0?=? =?UTF-8?Q?=D0=BC=D0=B8=3B_198_=D0=BF=D1=80=D0=B5=D0= from mx.it5.su[91.223.89.239]; from=<all@ml.it5.su> to=<ady@infoservice.ru> proto=ESMTP helo=<mx.it5.su>: =?UTF-8?Q?=D0=94=D0=BB=D0=B8=D0=BD=D0=BD=D0=B0=D1=8F_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=B0_=D1=81=D0=BE_=D0=B7=D0=BD=D0=B0=D0=BA=D0=B0?=? =?UTF-8?Q?=D0=BC=D0=B8=3B_198_=D0=BF=D1=80=D0=B5=D0=BF=D0=B8=D0=BD=D0=B0?=? =?UTF-8?Q?=D0=BD=D0=B8=D1=8F_=28preps=29=2C_=D0=BD=D0=B8=D0=B6=D0=BD?=? =?UTF-8?Q?=D0=B8=D0=BC=D0=B8_=D0=BF=D0=BE=D0=B4=D1=87=D0=B5=D1=80=D0=BA?=? =?UTF-8?Q?=D0=B8=D0=B2=D0=B0=D0=BD=D0=B8=D1=8F=D0=BC=D0=B8=2C_=D1=86?=? =?UTF-8?Q?=D0=B8=D1=84=D1=80=D0=B0=D0=BC=D0=B8_1_23_=D0=B8_=D1=82=2E_?=? =?UTF-8?Q?=D0=BF=2E?=\n
        $subjectMatches = [];
        $subjectPattern = '/header Subject:.*? from .*?; from=.*?:(.*)/mu';
        $subjectResult = preg_match($subjectPattern, $payload, $subjectMatches);
        if ($subjectResult) {
            $subject = $mailSubjectConverter->convert($subjectMatches[1]);
        }

        // header Subject: =?UTF-8?Q?=D0=9F=D0=B8=D1=81=D1=8C=D0=BC=D0=BE_=D1=81_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=BE=D0=B9_=D0=BD=D0=B0_=D1=80=D1=83=D1=81=D1=81?=? =?UTF-8?Q?=D0=BA=D0=BE=D0=BC_=D1=8F=D0=B7=D1=8B=D0= from mx.it5.su[91.223.89.239]; from=<borodin_admin@ml.it5.su> to=<ady@infoservice.ru> proto=ESMTP helo=<mx.it5.su>\n
        if (!$subject) {
            $subjectPattern = '/.*?Subject: (.*?) from .*?;.*/mu';
            $subjectResult = preg_match($subjectPattern, $payload, $subjectMatches);
            if ($subjectResult) {
                $subject = $mailSubjectConverter->convert($subjectMatches[1]);
            }
        }

        $mailMessagesArray = [];
        // to=<hiddencopy@rersre.sfds>, relay=none, delay=0.23, delays=0.14/0.08/0.01/0, dsn=5.4.4, status=bounced (Host or domain name not found. Name service error for name=rersre.sfds type=A: Host not found)\n
        // to=<adyanul@mail.ru>, relay=mxs.mail.ru[217.69.139.150]:25, delay=0.89, delays=0.14/0.08/0.16/0.51, dsn=2.0.0, status=sent (250 OK id=1ptY0i-009HgJ-1h)\n
        $recipientPattern = '/^to=(.*?),(.*?)status=(\S+)\s*\((.*?)\)(.*)/s';
        foreach ($payloadArray as $payloadLine) {
            $payloadLine = trim($payloadLine);
            $recipientMatches = [];
            $recipientResult = preg_match($recipientPattern, $payloadLine, $recipientMatches);
            if ($recipientResult) {
                $to = str_replace(['<', '>'], '', $recipientMatches[1]);
                $statusText = $recipientMatches[4];
                $statusName = $recipientMatches[3];
                $statusCodePattern = '/^(\d+).*/s';
                $statusCodeMatches = [];
                $statusCodeResult = preg_match($statusCodePattern, $statusText, $statusCodeMatches);
                $statusCode = 0;
                if ($statusCodeResult) {
                    $statusCode = $statusCodeMatches[1];
                }
                $mailMessage = new EntityMailMessage(
                    id: $id,
                    dateTime: $dateTime,
                    queueId: $queueId,
                    from: $from,
                    to: $to,
                    subject: $subject,
                    statusText: $statusText,
                    statusCode: $statusCode,
                    statusName: $statusName,
                    nonDeliveryNotificationId: $nonDeliveryNotificationId,
                );
                $mailMessagesArray[] = $mailMessage;
            }
        }

        return $mailMessagesArray;
    }
}
