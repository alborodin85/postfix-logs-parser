<?php

namespace Test;

use App\RestApi\EntityResponse;
use App\RestApi\ServiceAddRecords;
use App\RestApi\ServiceRestApiRequest;
use PHPUnit\Framework\TestCase;

class ServiceAddRecordsTest extends TestCase
{
    public function testAddRecords()
    {
        $serviceRestApiRequest = $this
            ->getMockBuilder(ServiceRestApiRequest::class)
            ->setConstructorArgs(['', '', ''])
            ->getMock();

        $serviceAddRecords = new ServiceAddRecords($serviceRestApiRequest, '');
        $mailsArray = range(1, 57);
        $chunkSize = 10;

        $response = new EntityResponse(
            payload: '',
            errorCode: 0,
            httpCode: 200,
            errorModule: '',
            errorText: '');

        $map = [
            ['', ['records' => range(1, 10)], $response],
            ['', ['records' => range(11, 20)], $response],
            ['', ['records' => range(21, 30)], $response],
            ['', ['records' => range(31, 40)], $response],
            ['', ['records' => range(41, 50)], $response],
            ['', ['records' => range(51, 57)], $response],
        ];

        $serviceRestApiRequest
            ->expects($this->exactly(6))
            ->method('post')
            ->will($this->returnValueMap($map));


        $serviceAddRecords->addRecords($mailsArray, $chunkSize);
    }
}
