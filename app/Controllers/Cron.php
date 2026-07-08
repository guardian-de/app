<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\CLI\CLI;

class Cron extends BaseController
{
    public function record()
    {
        $db = \Config\Database::connect();

        // 1. Tentar buscar cotação no Transfero OTC (Staging)
        $baseRate = $this->getTransferoOtcRate();

        if (!$baseRate) {
            // Fallback para Binance
            $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'; // Necessário para não ser bloqueado

            // Binance (USDT para BRL)
            $ch = curl_init('https://api.binance.com/api/v3/ticker/price?symbol=USDTBRL');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                log_message('error', 'Cron Binance API CURL Error: ' . $error);
            }
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['price']) && is_numeric($data['price'])) {
                    $baseRate = (float) $data['price'];
                }
            }
        }

        if (!$baseRate) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Falha ao buscar cotação no Transfero OTC e Binance API.']);
        }

        // Add dynamic real-time fluctuation so trend goes up and down
        $fluctuation = (mt_rand(-30, 30) / 10000);
        $baseRate += $fluctuation;

        $now = date('Y-m-d H:i:s');
        $minute = date('Y-m-d H:i:00');

        // Verifica se já gravou neste minuto
        $exists = $db->table('dollar_history')
                     ->where('created_at >=', $minute)
                     ->get()
                     ->getRow();

        if ($exists) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Já gravado neste minuto.', 'base_rate' => $baseRate]);
        }

        // 2. Gravar globalmente
        $db->table('dollar_history')->insert([
            'base_rate'  => $baseRate,
            'created_at' => $now
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Registro global gravado com sucesso!',
            'base_rate' => $baseRate
        ]);
    }

    public function populateHistory()
    {
        $db = \Config\Database::connect();
        
        $now = time();
        $records = [];
        $baseRate = 5.2530;

        for ($i = 0; $i < 100; $i++) {
            $timestamp = $now - ($i * 900); // 15 min intervals
            $variation = (mt_rand(-100, 100) / 10000); 
            $baseRate += $variation;
            
            $records[] = [
                'base_rate'  => round($baseRate, 4),
                'created_at' => date('Y-m-d H:i:s', $timestamp)
            ];
        }

        if (!empty($records)) {
            $db->table('dollar_history')->insertBatch($records);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => '100 registros globais gerados!']);
    }
    public function migrate()
    {
        $migrate = \Config\Services::migrations();
        try {
            $migrate->latest();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Migrações executadas com sucesso!']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    public function applyInterest()
    {
        $contractModel = new \App\Models\ContractModel();
        $contractModel->applyDailyInterest();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Juros diários aplicados com sucesso!']);
    }

    /**
     * Lê (OCR + IA) os comprovantes de depósito enviados pelo cliente. Roda em
     * lote pequeno por chamada para não estourar o tempo de execução — chame
     * este endpoint a cada ~20-30s (mesmo padrão de /cron/record) para que a
     * fila esvazie rapidamente mesmo com muitos comprovantes enviados de vez.
     * ponytail: varredura simples sem lock distribuído — se duas chamadas
     * concorrentes pegarem o mesmo depósito, o pior caso é reprocessar (sem
     * dano, já que só ajusta amount/ocr_status, nunca lança no ledger).
     */
    public function processDepositOcr()
    {
        set_time_limit(120);

        $depositModel = new \App\Models\DepositModel();
        $pending = $depositModel
            ->where('ocr_status', 'processing')
            ->orderBy('created_at', 'ASC')
            ->limit(5)
            ->findAll();

        if (empty($pending)) {
            return $this->response->setJSON(['status' => 'success', 'processed' => 0]);
        }

        $ocr       = new \App\Libraries\OcrSpaceClient();
        $extractor = new \App\Libraries\DeepSeekAmountExtractor();

        foreach ($pending as $deposit) {
            $ocrText  = $ocr->read($deposit['proof_file']);
            $aiResult = $extractor->extract((string) $ocrText);

            $isReadable = $aiResult['is_proof'] && $aiResult['amount'] !== null;

            $depositModel->update($deposit['id'], [
                'amount'       => $isReadable ? $aiResult['amount'] : null,
                'ai_amount'    => $aiResult['amount'],
                'ocr_status'   => $isReadable ? 'ok' : 'needs_review',
                'ocr_raw_text' => $ocrText,
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'processed' => count($pending)]);
    }

    private function getTransferoOtcRate($settlement = 'D0')
    {
        $apiKey = env('TRANSFERO_OTC_API_KEY');
        $baseUrl = 'https://staging.otc.transfero.com';

        try {
            // Passo A: Login para obter o JWT
            $ch = curl_init($baseUrl . '/v1/auth/login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['api_key' => $apiKey]));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            
            $loginResponse = curl_exec($ch);
            $loginError = curl_error($ch);
            curl_close($ch);

            if ($loginError) {
                log_message('error', 'Transfero Login CURL Error: ' . $loginError);
                return null;
            }

            $loginData = json_decode($loginResponse, true);
            $token = $loginData['token'] ?? null;

            if (!$token) {
                log_message('error', 'Transfero Login Failed: ' . $loginResponse);
                return null;
            }

            // Passo B: Buscar Tabela de Preços Geral
            $ch = curl_init($baseUrl . '/v1/prices');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);

            $pricesResponse = curl_exec($ch);
            $pricesError = curl_error($ch);
            curl_close($ch);

            if ($pricesError) {
                log_message('error', 'Transfero Prices CURL Error: ' . $pricesError);
                return null;
            }

            $pricesData = json_decode($pricesResponse, true);
            if (isset($pricesData['prices']['USDT'][$settlement]['price'])) {
                return (float) $pricesData['prices']['USDT'][$settlement]['price'];
            }

            log_message('error', 'Transfero Prices API Failure: ' . $pricesResponse);
        } catch (\Throwable $e) {
            log_message('error', 'Transfero API Exception: ' . $e->getMessage());
        }

        return null;
    }
}
