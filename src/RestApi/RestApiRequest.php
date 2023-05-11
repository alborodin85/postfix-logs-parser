<?php

namespace App\RestApi;

class RestApiRequest
{
    public function makeCurlPostRequest(string $url, array $arFields): EntityResponse
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arFields));
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $arSourceData = json_decode($response, true);
        if (is_array($arSourceData)) {
            $arData = $arSourceData;
        } else {
            $arData = [
                'payload' => $response,
            ];
        }

        $arError = [];
        if ($curlError = curl_error($ch)) {
            $arError = [
                'errorCode' => 500,
                'errorModule' => 'curl_error',
                'errorText' => $curlError,
            ];
        } elseif (!$response) {
            $arError = [
                'errorCode' => 500,
                'errorModule' => 'curl_error',
                'errorText' => 'пустой ответ сервера',
            ];
        } elseif (!\str_starts_with($response, '{') || !\str_ends_with($response, '}')) {
            $arError = [
                'errorCode' => 500,
                'errorModule' => 'curl_error',
                'errorText' => 'некорректный JSON',
            ];
        }
        $arData = array_merge($arData, $arError);

        $curlInfo = curl_getinfo($ch);

        $entityResponse = new EntityResponse(
            payload: $arData['payload'],
            errorCode: $arData['errorCode'],
            httpCode: $curlInfo['http_code'],
            errorModule: $arData['errorModule'],
            errorText: $arData['errorText'],
        );

        return $entityResponse;
    }

}
