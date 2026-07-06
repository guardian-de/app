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
