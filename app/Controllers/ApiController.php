<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ApiController extends BaseController
{
    /**
     * Retorna a cotação do USDT/BRL extraída da Binance em tempo real,
     * estruturada de acordo com o formato solicitado.
     * Possui cache de 5 segundos e fallback para o histórico do banco de dados.
     */
    public function usdtCotacao()
    {
        $cacheKey = 'api_usdt_cotacao_data';
        $cachedData = cache($cacheKey);

        if ($cachedData !== null) {
            return $this->response->setJSON($cachedData);
        }

        $usdtSpot = null;
        $askPrice = null;
        $bidPrice = null;

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36';

        // 1. Tentar obter preço de cotação atual (Spot/Last Price)
        $ch = curl_init('https://api.binance.com/api/v3/ticker/price?symbol=USDTBRL');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $resPrice = curl_exec($ch);
        curl_close($ch);

        if ($resPrice) {
            $dataPrice = json_decode($resPrice, true);
            if (isset($dataPrice['price']) && is_numeric($dataPrice['price'])) {
                $usdtSpot = (float) $dataPrice['price'];
            }
        }

        // 2. Tentar obter o melhor Bid/Ask (Order Book)
        $ch = curl_init('https://api.binance.com/api/v3/ticker/bookTicker?symbol=USDTBRL');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $resBook = curl_exec($ch);
        curl_close($ch);

        if ($resBook) {
            $dataBook = json_decode($resBook, true);
            if (isset($dataBook['bidPrice']) && is_numeric($dataBook['bidPrice'])) {
                $bidPrice = (float) $dataBook['bidPrice'];
            }
            if (isset($dataBook['askPrice']) && is_numeric($dataBook['askPrice'])) {
                $askPrice = (float) $dataBook['askPrice'];
            }
        }

        // 3. Fallback: Se as requisições falharem, buscar o valor mais recente gravado no histórico
        if ($usdtSpot === null || $bidPrice === null || $askPrice === null) {
            $db = \Config\Database::connect();
            $lastRecord = $db->table('dollar_history')
                             ->orderBy('created_at', 'DESC')
                             ->limit(1)
                             ->get()
                             ->getRow();

            $fallbackRate = $lastRecord ? (float) $lastRecord->rate : 5.0;

            if ($usdtSpot === null) {
                $usdtSpot = $fallbackRate;
            }
            if ($bidPrice === null) {
                // Estimar bid price com um spread aproximado de 0.25% abaixo
                $bidPrice = round($usdtSpot * 0.9975, 4);
            }
            if ($askPrice === null) {
                // Estimar ask price com um spread aproximado de 0.25% acima
                $askPrice = round($usdtSpot * 1.0025, 4);
            }
        }

        // 4. Formatar data UTC com milissegundos (ex: 2026-07-08T13:26:56.323Z)
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastUpdate = $now->format('Y-m-d\TH:i:s.v\Z');

        // 5. Montar estrutura final
        $result = [
            'lastUpdate'       => $lastUpdate,
            'currentValidLink' => 'coinBaseData',
            'coinBaseData'     => [
                'usdtSpot' => $usdtSpot,
                'usdt'     => 1,
            ],
            'aditional'        => 0,
            'investingData'    => [
                'usdtSpot' => 0,
                'usdt'     => 1,
            ],
            'currencyDataFeedAskData' => [
                'usdt'     => 1,
                'usdtSpot' => $askPrice,
            ],
            'currencyDataFeedBidData' => [
                'usdt'     => 1,
                'usdtSpot' => $bidPrice,
            ],
        ];

        // 6. Gravar em cache por 5 segundos
        cache()->save($cacheKey, $result, 5);

        return $this->response->setJSON($result);
    }
}
