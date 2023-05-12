<?php

namespace Test;

use App\RestApi\EntityRequestAddRecords;
use App\RestApi\EntityResponse;
use App\RestApi\EntityResponseGetLastArchive;
use App\RestApi\ServiceRestApiRequest;
use PHPUnit\Framework\TestCase;

class WebApiRequestTest extends TestCase
{
    private ServiceRestApiRequest $serviceRestApiRequest;

    public function setUp(): void
    {
        $this->serviceRestApiRequest = new \App\RestApi\ServiceRestApiRequest(
            $_ENV['WEB_PATH_SCHEMA'], $_ENV['WEB_PATH_DOMAIN'], $_ENV['ENDPOINT_API_TOKEN']
        );
    }

    public function testNormalJson()
    {
        $result = $this->serviceRestApiRequest->post(
            $_ENV['ENDPOINT_RETURN_RESPONSE'], ['test-field-name' => 'test-field-value']
        );

        $expected = new EntityResponse(
            payload: ['test-field-name' => 'test-field-value', 'endpoint_api_token' => $_ENV['ENDPOINT_API_TOKEN']],
            errorCode: 0,
            httpCode: 200,
            errorModule: '',
            errorText: ''
        );

        $this->assertEquals($expected, $result);

        $endpoint = mb_substr($_ENV['ENDPOINT_RETURN_RESPONSE'], 1, mb_strlen($_ENV['ENDPOINT_RETURN_RESPONSE'])-1);
        $result = $this->serviceRestApiRequest->post(
            $endpoint, ['test-field-name' => 'test-field-value']
        );

        $this->assertEquals($expected, $result);
    }

    public function testHtmlResult()
    {
        $result = $this->serviceRestApiRequest->post(
            $_ENV['ENDPOINT_RETURN_RESPONSE'], ['throwHtml' => 1]
        );

        $expected = new EntityResponse(
            payload: '<!DOCTYPE html><html lang="en"><head><title>Title</title></head></html>',
            errorCode: 500,
            httpCode: 200,
            errorModule: 'curl_error',
            errorText: 'некорректный JSON'
        );

        $this->assertEquals($expected, $result);
    }

    public function testCurlError()
    {
        $restApiRequest = new ServiceRestApiRequest(
            $_ENV['WEB_PATH_SCHEMA'], 'not-exists-domain', $_ENV['ENDPOINT_API_TOKEN']
        );

        $result = $restApiRequest->post(
            $_ENV['ENDPOINT_RETURN_RESPONSE'], []
        );

        $expected = new EntityResponse(
            payload: false,
            errorCode: 500,
            httpCode: 0,
            errorModule: 'curl_error',
            errorText: 'Could not resolve host: not-exists-domain'
        );

        $this->assertEquals($expected, $result);
    }

    public function testEmptyResponse()
    {
        $result = $this->serviceRestApiRequest->post(
            $_ENV['ENDPOINT_RETURN_RESPONSE'], ['throwEmpty' => 1]
        );

        $expected = new EntityResponse(
            payload: '',
            errorCode: 500,
            httpCode: 200,
            errorModule: 'curl_error',
            errorText: 'пустой ответ сервера'
        );

        $this->assertEquals($expected, $result);
    }

    public function testEmptyApiToken()
    {
        $restApiRequest = new ServiceRestApiRequest(
            $_ENV['WEB_PATH_SCHEMA'], $_ENV['WEB_PATH_DOMAIN'], ''
        );

        $result = $restApiRequest->post(
            $_ENV['ENDPOINT_RETURN_RESPONSE'], []
        );

        $expected = new EntityResponse(
            payload: '',
            errorCode: 500,
            httpCode: 401,
            errorModule: 'curl_error',
            errorText: 'некорректный JSON'
        );

        $this->assertEquals($expected->httpCode, $result->httpCode);
        $this->assertEquals($expected->errorText, $result->errorText);
    }

    public function testEntityRequestAddRecords()
    {
        $entityRequestAddRecords = new EntityRequestAddRecords(['record']);
        $expected = ['records' => ['record']];
        $this->assertEquals($expected, $entityRequestAddRecords->toArFields());
    }

    public function testEntityResponseGetLastArchive()
    {
        $entityResponse = new EntityResponse(
            payload: 'last-archive-name',
            errorCode: 0,
            httpCode: 200,
            errorModule: '',
            errorText: ''
        );

        $entityResponseGetLastArchive = new EntityResponseGetLastArchive($entityResponse);

        $this->assertEquals('last-archive-name', $entityResponseGetLastArchive->lastArchiveFileName);
    }
}
