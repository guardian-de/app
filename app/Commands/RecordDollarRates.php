<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RecordDollarRates extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'app:record-dollar';
    protected $description = 'Grava o valor base do dólar global na tabela dollar_history';

    public function run(array $params)
    {
        // 1. Tentar buscar cotação na Binance
        $baseRate = null;
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
            CLI::error("CURL Error: " . $error);
        }
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['price']) && is_numeric($data['price'])) {
                $baseRate = (float) $data['price'];
            }
        }

        if (!$baseRate) {
            CLI::error("Não foi possível buscar a cotação na Binance API.");
            return;
        }
        CLI::write("Cotação Base: R$ " . $baseRate);

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $minute = date('Y-m-d H:i:00');

        // Verifica se já gravou neste minuto
        $exists = $db->table('dollar_history')
                     ->where('created_at >=', $minute)
                     ->get()
                     ->getRow();

        if ($exists) {
            CLI::write("Já existe um registro para este minuto.");
            return;
        }

        // 2. Gravar o valor base global
        $db->table('dollar_history')->insert([
            'base_rate'  => $baseRate,
            'created_at' => $now
        ]);

        CLI::write("Registro global gravado com sucesso.");
    }
}
