<?php

namespace Test;

use App\RestApi\EntityResponse;
use App\RestApi\RestApiRequest;
use PHPUnit\Framework\TestCase;

class WebApiRequestTest extends TestCase
{
    public function testNormalJson()
    {
        $restApiRequest = new RestApiRequest();
        $url = $_ENV['WEB_PATH_SCHEMA'] . '://' . $_ENV['WEB_PATH_DOMAIN'] . '/api/return-response';
        $fields = ['endpoint_api_token' => $_ENV['ENDPOINT_API_TOKEN'], 'test-field-name' => 'test-field-value'];
        $result = $restApiRequest->makeCurlPostRequest($url, $fields);

        $expected = new EntityResponse(
            payload: ['test-field-name' => 'test-field-value', 'endpoint_api_token' => $_ENV['ENDPOINT_API_TOKEN']],
            errorCode: 0,
            httpCode: 200,
            errorModule: '',
            errorText: ''
        );

        $this->assertEquals($expected, $result);
    }

    public function testHtmlResult()
    {
        $restApiRequest = new RestApiRequest();
        $url = $_ENV['WEB_PATH_SCHEMA'] . '://' . $_ENV['WEB_PATH_DOMAIN'] . '/api/return-response';
        $fields = ['endpoint_api_token' => $_ENV['ENDPOINT_API_TOKEN'], 'throwHtml' => 1];
        $result = $restApiRequest->makeCurlPostRequest($url, $fields);

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
        $restApiRequest = new RestApiRequest();
        $url = $_ENV['WEB_PATH_SCHEMA'] . '://' . 'not-exists-domain' . '/api/return-response';
        $fields = ['endpoint_api_token' => $_ENV['ENDPOINT_API_TOKEN'], 'throwError' => 1];
        $result = $restApiRequest->makeCurlPostRequest($url, $fields);

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
        $restApiRequest = new RestApiRequest();
        $url = $_ENV['WEB_PATH_SCHEMA'] . '://' . $_ENV['WEB_PATH_DOMAIN'] . '/api/return-response';
        $fields = ['endpoint_api_token' => $_ENV['ENDPOINT_API_TOKEN'], 'throwEmpty' => 1];
        $result = $restApiRequest->makeCurlPostRequest($url, $fields);

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
        $restApiRequest = new RestApiRequest();
        $url = $_ENV['WEB_PATH_SCHEMA'] . '://' . $_ENV['WEB_PATH_DOMAIN'] . '/api/return-response';
        $fields = [];
        $result = $restApiRequest->makeCurlPostRequest($url, $fields);

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
}
