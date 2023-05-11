<?php

namespace App;

class ParserMailSubject
{
    public function convert(string $encodedSubject): string
    {
        // =?UTF-8?Q?=D0=9F=D0=B8=D1=81=D1=8C=D0=BC=D0=BE_=D1=81_=D1=82?=? =?UTF-8?Q?=D0=B5=D0=BC=D0=BE=D0=B9_=D0=BD=D0=B0_=D1=80=D1=83=D1=81=D1=81?=? =?UTF-8?Q?=D0=BA=D0=BE=D0=BC_=D1=8F=D0=B7=D1=8B=D0=
        // =?<charset>?<encoding>?<data>?=
        $items = explode(' ', $encodedSubject);
        $items = array_map(fn(string $item) => trim($item), $items);
        $items = array_filter($items, fn(string $item) => !!$item);
        $clearedItems = [];
        $isBrokenString = false;
        foreach ($items as $sourceItem) {
            // Если вообще не закодировано
            $encodedPattern = '/=\?(.+?)\?(.+?)\?(.*)/';
            $encodedResult = preg_match($encodedPattern, $sourceItem);
            if (!$encodedResult) {
                $clearedItems[] = $sourceItem . ' ';
                continue;
            }
            // Если заканчивается как и должно
            if (str_ends_with($sourceItem, '?=')) {
                $clearedItems[] = $sourceItem;
                continue;
            }
            // Если заканчивалось дополнительным знаком вопроса с пробелом
            if (str_ends_with($sourceItem, '?=?')) {
                $sourceItem = mb_substr($sourceItem, 0, -1);
                $clearedItems[] = $sourceItem;
                continue;
            }
            // Если просто битая строка
            $isBrokenString = true;
            $endSymbolMatches = [];
            $endSymbolPattern = '/(.*?)=(\w)$/us';
            $endSymbolResult = preg_match($endSymbolPattern, $sourceItem, $endSymbolMatches);
            if ($endSymbolResult) {
                $sourceItem = $endSymbolMatches[1];
            }
            $clearedItems[] = $sourceItem;
        }

        $decodedItemsArray = array_map(fn(string $item) => mb_decode_mimeheader($item), $clearedItems);
        $decodedItemsString = implode('', $decodedItemsArray);
        $decodedItemsString = str_replace('_', ' ', $decodedItemsString);
        $decodedItemsString = trim($decodedItemsString);

        $letterArray = mb_str_split($decodedItemsString);
        for ($i = count($letterArray) - 1; $i >= 0; $i--) {
            $symbolPattern = '/\w/u';
            $symbolResult = preg_match($symbolPattern, $letterArray[$i]);
            if ($symbolResult) {
                break;
            }
        }

        $decodedItemsString = mb_substr($decodedItemsString, 0, $i+2);

        if ($isBrokenString) {
            $decodedItemsString = mb_substr($decodedItemsString, 0, -1);
        }

        $decodedItemsString .= '...';

        return $decodedItemsString;
    }
}
