<?php

namespace Test;

use App\ParserMailSubject;
use PHPUnit\Framework\TestCase;

class ParserMailSubjectTest extends TestCase
{
    public function testConverts()
    {
        $mailSubjectConverter = new ParserMailSubject();
        // Несколько слов
        $string = '=?UTF-8?Q?=D0=9F=D0=B8=D1=81=D1=8C=D0=BC=D0=BE_=D1=81_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=BE=D0=B9_=D0=BD=D0=B0_=D1=80=D1=83=D1=81=D1=81?=? =?UTF-8?Q?=D0=BA=D0=BE=D0=BC_=D1=8F=D0=B7=D1=8B=D0=';
        $result = $mailSubjectConverter->convert($string);
        $expected = 'Письмо с темой на русском язы...';
        $this->assertEquals($expected, $result);

        // alborodin85@mail.ru
        $string = '=?UTF-8?Q?=D0=9A=D0=BE=D1=80=D0=BE=D1=82=D0=BA=D0=B0=D1=8F_?=? =?UTF-8?Q?=D1=82=D0=B5=D0=BC=D0=B0?=';
        $expected = 'Короткая тема...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=D0=94=D0=BB=D0=B8=D0=BD=D0=BD=D0=B0=D1=8F_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=B0_=D1=81=D0=BE_=D0=B7=D0=BD=D0=B0=D0=BA=D0=B0?=? =?UTF-8?Q?=D0=BC=D0=B8=3B_198_=D0=BF=D1=80=D0=B5=D0=';
        $expected = 'Длинная тема со знаками; 198 пре...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=D0=A2=D0=B5=D0=BC=D0=B0_=D1=81_=D1=87=D0=B5=D1=82?=? =?UTF-8?Q?=D0=BD=D1=8B=D0=BC_=D0=BA=D0=BE=D0=BB=D0=B8=D1=87=D0=B5=D1=81?=? =?UTF-8?Q?=D1=82=D0=B2=D0=BE=D0=BC_=D0=B1=D0=B0=D0=B3';
        $expected = 'Тема с четным количеством ба...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=D0=A2=D0=B5=D0=BC=D0=B0_=D1=81_=D0=BD=D0=B5=D1=87?=? =?UTF-8?Q?=D0=B5=D1=82=D0=BD=D1=8B=D0=BC_=D0=BA=D0=BE=D0=BB=D0=B8=D1=87?=? =?UTF-8?Q?=D0=B5=D1=81=D1=82=D0=B2=D0=BE=D0=BC_=D0=B';
        $expected = 'Тема с нечетным количеством...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=D0=9F=D0=BE=D0=BB=D1=83_=D1=80=D1=83=D1=81=D1=81?=? =?UTF-8?Q?=D0=BA=D0=B8=D0=B9_Vasa=3B_1223=24=3B_=5F=5F_=3A_99?=';
        $expected = 'Полу русский Vasa; 1223$;    : 99...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=D0=94=D0=BB=D0=B8=D0=BD=D0=BD=D0=B0=D1=8F_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=B0_=D1=81=D0=BE=5F=D0=B7=D0=BD=D0=B0=D0=BA?=? =?UTF-8?Q?=D0=B0=D0=BC=D0=B8=3B_198_=D0=BF=D1=80=D0=B5=D';
        $expected = 'Длинная тема со знаками; 198 пр...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=D0=A0=D1=83=D1=81=D1=81=D0=BA=D0=B8=D0=B5Vasa123?=? =?UTF-8?Q?=D0=A0=D1=83=D1=81=D1=81=D0=BA34?=';
        $expected = 'РусскиеVasa123Русск34...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = 'Only eng and 8787;';
        $expected = 'Only eng and 8787;...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=3C=D0=B1=D0=B5=D0=B7_=D1=82=D0=B5=D0=BC=D1=8B=3E?=';
        $expected = '<без темы>...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=3C=D0=B1=D0=B5=D0=B7_=D1=82=D0=B5=D0=BC=D1=8B=3E?= =?UTF-8?Q?';
        $expected = '<без темы...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

        $string = '=?UTF-8?Q?=3C=D0=B1=D0=B5=D0=B7_=D1=82=D0=B5=D0=BC=D1=8B=3E?=? any text';
        $expected = '<без темы>any text...';
        $result = $mailSubjectConverter->convert($string);
        $this->assertEquals($expected, $result);

    }
}
