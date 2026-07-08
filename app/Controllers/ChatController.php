<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ChatController extends BaseController
{
    private const MIN_BUY_USDT = 5000;

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $this->recordCurrentRate();
        
        $userModel = new \App\Models\UserModel();
        $data['user'] = $userModel->find(session()->get('user_id'));
        
        $settingsModel = new \App\Models\SettingsModel();
        $data['business_hours'] = [
            'start' => $settingsModel->getConfig('business_hours_start', '08:00'),
            'end'   => $settingsModel->getConfig('business_hours_end', '16:30')
        ];
        $data['quotation_flow'] = $settingsModel->getConfig('quotation_flow', 'direct');
        $data['operator_whatsapp'] = $settingsModel->getConfig('operator_whatsapp', '');
        
        return view('dashboard/chat', $data);
    }

    public function mobile()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $this->recordCurrentRate();

        $userModel = new \App\Models\UserModel();
        $data['user'] = $userModel->find(session()->get('user_id'));

        $settingsModel = new \App\Models\SettingsModel();
        $data['business_hours'] = [
            'start' => $settingsModel->getConfig('business_hours_start', '08:00'),
            'end'   => $settingsModel->getConfig('business_hours_end', '16:30')
        ];
        $data['quotation_flow'] = $settingsModel->getConfig('quotation_flow', 'direct');
        $data['operator_whatsapp'] = $settingsModel->getConfig('operator_whatsapp', '');
        
        return view('dashboard/mobile', $data);
    }

    private function recordCurrentRate()
    {
        $db = \Config\Database::connect();
        
        // Verifica se já gravou nos últimos 30 segundos para não sobrecarregar
        $lastRecord = $db->table('dollar_history')
                         ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 seconds')))
                         ->get()
                         ->getRow();
                         
        if ($lastRecord) {
            return;
        }
        
        $rate = $this->getDollarRate(0); // Pega taxa base (0% fee)
        
        if ($rate) {
            $db->table('dollar_history')->insert([
                'base_rate'  => round($rate, 4),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function getRate()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        $feePercent = $user['fee_percent'] ?? 10.00;
        
        $deliveryType = $this->request->getGet('delivery_type') ?? 'D+0';
        $settlement = 'D0';
        if ($deliveryType === 'D+1') {
            $settlement = 'D1';
        } elseif ($deliveryType === 'D+2') {
            $settlement = 'D2';
        }

        // Pega cotação direto da Transfero OTC / Binance em tempo real
        $baseRate = $this->getDollarRate(0, $settlement);

        if (!$baseRate) {
            // Fallback caso falhe a chamada à API da Binance
            $rateData = $this->getLatestRateFromDb($feePercent);
            $baseRate = $rateData['base_rate'];
        } else {
            // Grava o histórico global a cada 30 segundos em background se a chamada for bem sucedida
            $db = \Config\Database::connect();
            $lastRecord = $db->table('dollar_history')
                             ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 seconds')))
                             ->get()
                             ->getRow();
            if (!$lastRecord) {
                $db->table('dollar_history')->insert([
                    'base_rate'  => round($baseRate, 4),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return $this->response->setJSON([
            'rate' => $baseRate * (1 + ($feePercent / 100)),
            'base_rate' => $baseRate,
            'fee_percent' => $feePercent
        ]);
    }

    public function getHistory()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        $feePercent = $user['fee_percent'] ?? 10.00;

        // Busca o histórico global
        $data = $db->table('dollar_history')
                   ->orderBy('created_at', 'DESC')
                   ->limit(15)
                   ->get()
                   ->getResultArray();

        if (empty($data)) {
            // Fallback para API externa (Binance) caso a base esteja vazia
            $apiUrl = 'https://api.binance.com/api/v3/klines?symbol=USDTBRL&interval=15m&limit=15';
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            curl_close($ch);
            $externalData = json_decode($response, true);
            $history = [];
            if (is_array($externalData)) {
                foreach ($externalData as $item) {
                    $baseRate = (float) $item[4]; // Close price na Binance
                    $rateWithFee = $baseRate * (1 + ($feePercent / 100));
                    $history[] = [
                        'time' => date('H:i', (int) ($item[0] / 1000)),
                        'value' => round($rateWithFee, 4)
                    ];
                }
            }
            return $this->response->setJSON($history);
        }

        // Processa dados da nossa base global aplicando a taxa do usuário em memória
        $history = [];
        $data = array_reverse($data); // Cronológico
        foreach ($data as $item) {
            $baseRate = (float) $item['base_rate'];
            $rateWithFee = $baseRate * (1 + ($feePercent / 100));
            
            $history[] = [
                'time' => date('H:i', strtotime($item['created_at'])),
                'value' => round($rateWithFee, 4)
            ];
        }

        return $this->response->setJSON($history);
    }

    public function updateWallet()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $json = $this->request->getJSON();
        $wallet = $json->wallet ?? '';

        $userModel = new \App\Models\UserModel();
        $userModel->update(session()->get('user_id'), ['usdt_wallet' => $wallet]);
        
        session()->set('user_wallet', $wallet);

        return $this->response->setJSON(['success' => true]);
    }

    public function updateLanguage()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $json = $this->request->getJSON();
        $lang = $json->language ?? 'pt-BR';

        $userModel = new \App\Models\UserModel();
        $userModel->update(session()->get('user_id'), ['language' => $lang]);
        
        session()->set('user_lang', $lang);

        return $this->response->setJSON(['success' => true]);
    }

    public function send()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        // Verificação de Horário Dinâmica
        $settingsModel = new \App\Models\SettingsModel();
        $start = $settingsModel->getConfig('business_hours_start', '08:00');
        $end = $settingsModel->getConfig('business_hours_end', '16:30');
        $userLang = session()->get('user_lang') ?? 'pt-BR';
        
        $nowObj = new \DateTime('now', new \DateTimeZone('America/Sao_Paulo'));
        $now = $nowObj->format('H:i');

        // Permitimos o chat fora do horário para que o usuário possa solicitar D+1 e D+2

        $json = $this->request->getJSON();
        $userMessage = $json->message ?? '';

        if (empty($userMessage)) {
            return $this->response->setJSON(['error' => 'Empty message']);
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        $feePercent = $user['fee_percent'] ?? 10.00;
        $userLang = session()->get('user_lang') ?? 'pt-BR';
        $lowerMessage = mb_strtolower(trim($userMessage));
        $transactionModel = new \App\Models\TransactionModel();
        $chatMsgModel = new \App\Models\ChatMessageModel();

        $isClosed = ($now < $start || $now > $end);
        if ($isClosed) {
            // Salva mensagem do usuário
            $chatMsgModel->save([
                'user_id' => $userId,
                'sender'  => 'user',
                'message' => $userMessage
            ]);

            // Verifica se enviou o aviso nas últimas 1 hora
            $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
            $lastNotice = $chatMsgModel->where('user_id', $userId)
                ->where('sender', 'bot')
                ->where('created_at >=', $oneHourAgo)
                ->groupStart()
                    ->like('message', 'horário de atendimento')
                    ->orLike('message', '营业时间')
                ->groupEnd()
                ->first();

            if (!$lastNotice) {
                if ($userLang === 'zh-CN') {
                    $reply = "我们的营业时间已结束 ($start - $end)，但您的消息已转发给客服人员。您现在正在直接与支持团队沟通。请耐心等待回复。";
                } else {
                    $reply = "Nosso horário de atendimento encerrou ($start às $end), mas sua mensagem foi encaminhada para um operador. Você agora está falando diretamente com o suporte. Por favor, aguarde o retorno.";
                }
                $chatMsgModel->save([
                    'user_id' => $userId,
                    'sender'  => 'bot',
                    'message' => $reply
                ]);
                return $this->response->setJSON(['reply' => $reply]);
            }

            return $this->response->setJSON(['success' => true]);
        }

        // Salva mensagem do usuário
        $chatMsgModel->save([
            'user_id' => $userId,
            'sender'  => 'user',
            'message' => $userMessage
        ]);

        // 1. Saldo (Balance)
        $balanceTriggers = ['meu saldo', 'quanto eu tenho', 'saldo', '余额', '我的余额', '我的账户余额'];
        $isBalance = false;
        foreach ($balanceTriggers as $t) { if (str_contains($lowerMessage, $t)) { $isBalance = true; break; } }

        if ($isBalance) {
            $sum = $transactionModel->where('user_id', $userId)->where('status', 'completed')->selectSum('amount_usdt')->first();
            $bal = (float)($sum['amount_usdt'] ?? 0);
            if ($userLang == 'zh-CN') {
                $reply = "您的当前余额为 " . number_format($bal, 2) . " USDT。";
            } else {
                $reply = "Seu saldo atual é de " . number_format($bal, 2, ',', '.') . " USDT.";
            }
            $chatMsgModel->save(['user_id' => $userId, 'sender' => 'bot', 'message' => $reply]);
            return $this->response->setJSON(['reply' => $reply]);
        }

        // 1.1 Saldo Devedor / Contratos (Debt)
        $debtTriggers = ['saldo devedor', 'quanto devo', 'minha dívida', 'divida', 'contratos', '账单', '待付', '欠款'];
        $isDebt = false;
        foreach ($debtTriggers as $t) { if (str_contains($lowerMessage, $t)) { $isDebt = true; break; } }

        if ($isDebt) {
            $contractModel = new \App\Models\ContractModel();
            $contracts = $contractModel->where('user_id', $userId)->whereIn('status', ['pending', 'partially_paid', 'overdue'])->findAll();
            $totalDebt = 0;
            foreach ($contracts as $c) { $totalDebt += $c['remaining_balance']; }

            if ($userLang == 'zh-CN') {
                $reply = "您的待付余额为 " . number_format($totalDebt, 2) . " USDT。您有 " . count($contracts) . " 个开放合同。";
            } else {
                $reply = "Seu saldo devedor atual é de " . number_format($totalDebt, 2, ',', '.') . " USDT. Você possui " . count($contracts) . " contrato(s) em aberto.";
            }
            $chatMsgModel->save(['user_id' => $userId, 'sender' => 'bot', 'message' => $reply]);
            return $this->response->setJSON(['reply' => $reply]);
        }

        // 1.2 Saldo / Limite (Balance / Available Limit)
        $scoreTriggers = ['meu score', 'meu limite', 'quanto posso comprar', 'score', 'limite', '信用', '额度', '我的额度'];
        $isScore = false;
        foreach ($scoreTriggers as $t) { if (str_contains($lowerMessage, $t)) { $isScore = true; break; } }

        if ($isScore) {
            $financialModel = new \App\Models\FinancialStatementModel();
            $balance = $financialModel->getBalance($userId);
            $available = max(0.0, $balance);

            if ($userLang == 'zh-CN') {
                $reply = "您的当前余额为 R$ " . number_format($balance, 2, ',', '.') . "。可用于新购买的额度为 R$ " . number_format($available, 2, ',', '.') . "。";
            } else {
                $reply = "Seu saldo atual é de R$ " . number_format($balance, 2, ',', '.') . ". Disponível para novas compras: R$ " . number_format($available, 2, ',', '.') . ".";
            }
            $chatMsgModel->save(['user_id' => $userId, 'sender' => 'bot', 'message' => $reply]);
            return $this->response->setJSON(['reply' => $reply]);
        }

        // 2.7 Taxa (Fee)
        $feeTriggers = ['minha taxa', 'qual a taxa', 'qual minha taxa', 'taxa', '手续费', '我的手续费', '费率'];
        $isFee = false;
        foreach ($feeTriggers as $t) { if (str_contains($lowerMessage, $t)) { $isFee = true; break; } }

        if ($isFee) {
            $rateData = $this->getLatestRateFromDb($feePercent);
            $rate = $rateData['rate'];
            $amount = 0;
            if (preg_match('/(\d+(?:[.,]\d+)?)/', $userMessage, $matches)) {
                $amount = (float) str_replace(',', '.', $matches[1]);
            }

            if ($amount > 0 && $rate) {
                $usdt = $amount / $rate;
                if ($userLang == 'zh-CN') {
                    $reply = "计算如下：R$ " . number_format($amount, 2, ',', '.') . " / R$ " . number_format($rate, 4, ',', '.') . " (汇率) = " . number_format($usdt, 2) . " USDT。";
                } else {
                    $reply = "O cálculo é: R$ " . number_format($amount, 2, ',', '.') . " / R$ " . number_format($rate, 4, ',', '.') . " (cotação) = " . number_format($usdt, 2, ',', '.') . " USDT.";
                }
                
                $finalResponse = [
                    'reply' => $reply,
                    'showBuy' => true,
                    'currentRate' => $rate,
                    'suggestedAmount' => $amount
                ];
                
                $chatMsgModel->save([
                    'user_id'          => $userId,
                    'sender'           => 'bot',
                    'message'          => $reply,
                    'show_buy'         => true,
                    'rate'             => $rate,
                    'suggested_amount' => $amount
                ]);

                return $this->response->setJSON($finalResponse);
            } else {
                if ($userLang == 'zh-CN') {
                    $reply = "您的当前费率为 " . number_format($feePercent, 2) . "%。此费用已包含在您实时看到的汇率中。";
                } else {
                    $reply = "Sua taxa atual é de " . number_format($feePercent, 2, ',', '.') . "%. Este valor já está incluso na cotação que você vê em tempo real.";
                }
                
                $chatMsgModel->save(['user_id' => $userId, 'sender' => 'bot', 'message' => $reply]);
                return $this->response->setJSON(['reply' => $reply]);
            }
        }

        // 2. Pendentes (Pending)
        $pendingTriggers = ['quanto tenho pra receber', 'pendente', 'receber', 'minhas compras', '待收', '待处理', '等待中'];
        $isPending = false;
        foreach ($pendingTriggers as $t) { if (str_contains($lowerMessage, $t)) { $isPending = true; break; } }

        if ($isPending) {
            $sum = $transactionModel->where('user_id', $userId)->where('status', 'pending')->selectSum('amount_usdt')->first();
            $pend = (float)($sum['amount_usdt'] ?? 0);
            if ($userLang == 'zh-CN') {
                $reply = "您有 " . number_format($pend, 2) . " USDT 待处理。一旦管理员批准，它将记入您的余额。";
            } else {
                $reply = "Você tem " . number_format($pend, 2, ',', '.') . " USDT pendentes para receber. Assim que o administrador aprovar, o valor cairá no seu saldo.";
            }
            $chatMsgModel->save(['user_id' => $userId, 'sender' => 'bot', 'message' => $reply]);
            return $this->response->setJSON(['reply' => $reply]);
        }

        // 3. Comprar (Buy) / Dólar (Rate)
        $buyTriggers = ['comprar', 'quero comprar', 'valor do dolar', 'cotação', 'valor do dólar', '买', '我想买', '汇率', '价格'];
        $isBuy = false;
        foreach ($buyTriggers as $t) { if (str_contains($lowerMessage, $t)) { $isBuy = true; break; } }

        if ($isBuy) {
            $rateData = $this->getLatestRateFromDb($feePercent);
            $rate = $rateData['rate'];
            if ($rate) {
                $suggestedAmount = 0;
                if (preg_match('/(\d+(?:[.,]\d+)?)/', $userMessage, $matches)) {
                    $suggestedAmount = (float) str_replace(',', '.', $matches[1]);
                }
                
                if ($userLang == 'zh-CN') {
                    $reply = "当前 USDT 汇率为 R$ " . number_format($rate, 4, ',', '.') . "（含税）。您想现在购买吗？只需点击下方的按钮。";
                } else {
                    $reply = "A cotação atual do USDT é R$ " . number_format($rate, 4, ',', '.') . " (taxas inclusas). Deseja realizar uma compra agora? Basta usar o botão abaixo.";
                }

                $chatMsgModel->save([
                    'user_id'          => $userId,
                    'sender'           => 'bot',
                    'message'          => $reply,
                    'show_buy'         => true,
                    'rate'             => $rate,
                    'suggested_amount' => $suggestedAmount
                ]);

                return $this->response->setJSON([
                    'reply' => $reply,
                    'showBuy' => true,
                    'currentRate' => $rate,
                    'suggestedAmount' => $suggestedAmount
                ]);
            }
        }

        // 4. Fallback (Tópicos não relacionados)
        if ($userLang == 'zh-CN') {
            $reply = "抱歉，我只能回答与您的钱包、余额、待处理交易和汇率相关的问题。";
        } else {
            $reply = "Desculpe, eu só posso responder perguntas relacionadas à sua carteira, saldo, transações pendentes e cotação do dólar.";
        }

        $chatMsgModel->save(['user_id' => $userId, 'sender' => 'bot', 'message' => $reply]);

        return $this->response->setJSON(['reply' => $reply]);
    }

    public function createTransaction()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $json = $this->request->getJSON();
        $brlAmountReq = $json->amount_brl ?? 0;
        $usdtAmountReq = $json->amount_usdt ?? 0;
        $deliveryType = $json->delivery_type ?? 'D+0';
        $type = $json->type ?? 'buy'; // 'buy' ou 'sell'

        $userLang = session()->get('user_lang') ?? 'pt-BR';
        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        // Validação de tipo de entrega permitido
        $allowed = $user['allowed_delivery_types'] ?? 'all';
        if ($allowed !== 'all' && $allowed !== $deliveryType) {
            $errMsg = ($userLang == 'zh-CN') ? "您无权选择此交付类型。" : "Você não tem permissão para usar este tipo de entrega.";
            return $this->response->setJSON(['error' => $errMsg])->setStatusCode(403);
        }

        $nowObj = new \DateTime('now', new \DateTimeZone('America/Sao_Paulo'));
        $settingsModel = new \App\Models\SettingsModel();
        $now = $nowObj->format('H:i');

        // Validação de horário por tipo de entrega
        if ($deliveryType === 'D+0') {
            $start = $settingsModel->getConfig('business_hours_start', '08:00');
            $end = $settingsModel->getConfig('business_hours_end', '16:30');
            if ($now < $start || $now > $end) {
                $msg = $userLang == 'zh-CN' ? "实时交易 (D+0) 非营业时间 ($start - $end)" : "Transações em tempo real (D+0) fora do horário de funcionamento ($start às $end).";
                return $this->response->setJSON(['error' => $msg])->setStatusCode(403);
            }
        } elseif ($deliveryType === 'D+1') {
            $start = $settingsModel->getConfig('business_hours_d1_start', '08:00');
            $end = $settingsModel->getConfig('business_hours_d1_end', '18:00');
            if ($now < $start || $now > $end) {
                $msg = $userLang == 'zh-CN' ? "D+1 交易非营业时间 ($start - $end)" : "Transações D+1 fora do horário de funcionamento ($start às $end).";
                return $this->response->setJSON(['error' => $msg])->setStatusCode(403);
            }
        } elseif ($deliveryType === 'D+2') {
            $start = $settingsModel->getConfig('business_hours_d2_start', '08:00');
            $end = $settingsModel->getConfig('business_hours_d2_end', '18:00');
            if ($now < $start || $now > $end) {
                $msg = $userLang == 'zh-CN' ? "D+2 交易非营业时间 ($start - $end)" : "Transações D+2 fora do horário de funcionamento ($start às $end).";
                return $this->response->setJSON(['error' => $msg])->setStatusCode(403);
            }
        }
        
        if ($usdtAmountReq <= 0 && $brlAmountReq <= 0) {
            $errorMsg = $userLang == 'zh-CN' ? '无效金额' : 'Valor inválido.';
            return $this->response->setJSON(['error' => $errorMsg])->setStatusCode(400);
        }

        if ($type === 'buy' && $usdtAmountReq > 0 && $usdtAmountReq < self::MIN_BUY_USDT) {
            $errMsg = $userLang == 'zh-CN'
                ? '最低购买金额为 ' . self::MIN_BUY_USDT . ' USDT。'
                : 'O valor mínimo de compra é ' . self::MIN_BUY_USDT . ' USDT.';
            return $this->response->setJSON(['error' => $errMsg])->setStatusCode(400);
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        $feePercent = $user['fee_percent'] ?? 10.00;
        
        // Define o settlement com base no prazo de entrega selecionado
        $settlement = 'D0';
        if ($deliveryType === 'D+1') {
            $settlement = 'D1';
        } elseif ($deliveryType === 'D+2') {
            $settlement = 'D2';
        }

        // Pega cotação direto do Transfero OTC / Binance em tempo real na hora de comprar
        $baseRate = $this->getDollarRate(0, $settlement);

        if (!$baseRate) {
            // Fallback caso falhe a chamada à API da Binance
            $rateData = $this->getLatestRateFromDb($feePercent);
            $baseRate = $rateData['base_rate'];
        } else {
            // Grava o histórico global a cada 30 segundos em background se a chamada for bem sucedida
            $db = \Config\Database::connect();
            $lastRecord = $db->table('dollar_history')
                             ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 seconds')))
                             ->get()
                             ->getRow();
            if (!$lastRecord) {
                $db->table('dollar_history')->insert([
                    'base_rate'  => round($baseRate, 4),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        $rate = $baseRate * (1 + ($feePercent / 100));
        $usdtAmount = $usdtAmountReq;
        $brlAmount = $brlAmountReq;

        if ($usdtAmount > 0) {
            $brlAmount = round($usdtAmount * $rate, 2);
        } else {
            $usdtAmount = round($brlAmount / $rate, 2);
        }

        // Calcula valor comercial (sem taxa) e valor da taxa em BRL
        $comercialBrl = round($usdtAmount * $baseRate, 2);
        $feeBrl = round($brlAmount - $comercialBrl, 2);

        $transactionModel = new \App\Models\TransactionModel();
        
        $contractId = null;
        $balance = 0.0;
        $contractModel = new \App\Models\ContractModel();
        $financialModel = new \App\Models\FinancialStatementModel();
        if ($type === 'buy') {
            $balance = $financialModel->getBalance($userId);

            // Valida saldo: balance - compra >= 0 (Comentado para permitir compra mesmo com saldo abaixo de 0,00)
            /*
            if (($balance - $brlAmount) < 0) {
                $errorMsg = $userLang == 'zh-CN'
                    ? "余额不足。可用余额为 R$ " . number_format(max(0.0, $balance), 2, ',', '.')
                    : "Saldo insuficiente. Disponível para compra: R$ " . number_format(max(0.0, $balance), 2, ',', '.') . ".";
                return $this->response->setJSON(['error' => $errorMsg])->setStatusCode(400);
            }
            */

            // Toda compra gera contrato (margin_lock reduz o saldo via ledger)
            {
                $daysMap = ['D+1' => 1, 'D+2' => 2];
                $days = $daysMap[$deliveryType] ?? 0;
                $dueDate = date('Y-m-d H:i:s', strtotime("+$days days"));

                $contractId = $contractModel->insert([
                    'user_id'           => $userId,
                    'total_amount'      => $usdtAmount,
                    'total_brl'         => $brlAmount,
                    'remaining_balance' => $brlAmount,
                    'type'              => strtolower($deliveryType),
                    'due_date'          => $dueDate,
                    'status'            => 'pending',
                    'fee_percent'       => $feePercent,
                    'comercial_brl'     => $comercialBrl,
                    'fee_brl'           => $feeBrl
                ]);
            }
        }

        $transactionId = $transactionModel->insert([
            'user_id'        => $userId,
            'type'           => $type,
            'amount_brl'     => $brlAmount,
            'amount_usdt'    => $usdtAmount,
            'rate'           => $rate,
            'base_rate'      => $baseRate,
            'fee_percent'    => $feePercent,
            'comercial_brl'  => $comercialBrl,
            'fee_brl'        => $feeBrl,
            'status'         => 'pending',
            'delivery_type'  => $deliveryType,
            'wallet_address' => session()->get('user_wallet') ?: 'Não informada'
        ]);

        if ($contractId) {
            $contractModel->update($contractId, ['transaction_id' => $transactionId]);

            $financialModel->insert([
                'user_id'          => $userId,
                'contract_id'      => $contractId,
                'operation_type'   => 'margin_lock',
                'nature'           => 'D',
                'amount'           => $brlAmount,
                'description'      => 'Bloqueio de Margem - Contrato #' . $contractId,
                'transaction_date' => date('Y-m-d H:i:s'),
                'fee_percent'      => $feePercent,
                'comercial_brl'    => $comercialBrl,
                'fee_brl'          => $feeBrl
            ]);

            // Auto-paga o contrato com o saldo positivo que existia antes do margin_lock
            $autoPayAmount = min($brlAmount, max(0.0, $balance));
            if ($autoPayAmount > 0.01) {
                $contractModel->registerPayment($contractId, $autoPayAmount);
            }
        }

        return $this->response->setJSON(['status' => 'success', 'transaction_id' => $transactionId]);
    }

    public function uploadProof()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        $transactionId = $this->request->getPost('transaction_id');
        $proofText = $this->request->getPost('proof_text');
        $file = $this->request->getFile('proof');

        if (!$file->isValid()) {
            return $this->response->setJSON(['error' => 'Arquivo inválido.'])->setStatusCode(400);
        }

        $transactionModel = new \App\Models\TransactionModel();
        
        // 1. Verificação de Duplicidade por Hash de Arquivo
        $fileHash = hash_file('sha256', $file->getTempName());
        $existingHash = $transactionModel->where('proof_hash', $fileHash)->first();
        if ($existingHash) {
            return $this->response->setJSON(['error' => 'Este comprovante (arquivo) já foi enviado anteriormente.'])->setStatusCode(400);
        }

        $transaction = $transactionModel->where('id', $transactionId)->where('user_id', $userId)->first();

        if (!$transaction) {
            return $this->response->setJSON(['error' => 'Transação não encontrada.'])->setStatusCode(404);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/proofs', $newName);
        $finalPath = 'uploads/proofs/' . $newName;

        // 2. Extração OCR
        $textRead = $this->performOCR($finalPath);
        
        // 3. Extração do Código de Autenticação usando regex otimizado
        $authCode = null;
        if ($textRead) {
            $authCode = $this->extractAuthCodeFromText($textRead);
            
            // Verificação de Duplicidade por Código de Autenticação
            if ($authCode) {
                $existingAuth = $transactionModel->where('auth_code', $authCode)->first();
                if ($existingAuth) {
                    // Remove o arquivo físico se for duplicado por ID
                    @unlink(FCPATH . $finalPath);
                    return $this->response->setJSON(['error' => 'Este código de autenticação (' . $authCode . ') já foi registrado em outro comprovante.'])->setStatusCode(400);
                }
            }
        }

        $transactionModel->update($transactionId, [
            'proof_path' => $finalPath,
            'proof_text' => $proofText,
            'text_read'  => $textRead,
            'proof_hash' => $fileHash,
            'auth_code'  => $authCode
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Comprovante enviado com sucesso!']);
    }

    public function getBalance()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        $transactionModel = new \App\Models\TransactionModel();
        
        $buys = $transactionModel->where('user_id', $userId)
                                 ->where('type', 'buy')
                                 ->where('status', 'completed')
                                 ->selectSum('amount_usdt')
                                 ->first();

        $sells = $transactionModel->where('user_id', $userId)
                                  ->where('type', 'sell')
                                  ->where('status', 'completed')
                                  ->selectSum('amount_usdt')
                                  ->first();

        $balance = (float)($buys['amount_usdt'] ?? 0) - (float)($sells['amount_usdt'] ?? 0);

        return $this->response->setJSON(['balance' => $balance]);
    }

    public function getDebt()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        $financialModel = new \App\Models\FinancialStatementModel();
        $balance = $financialModel->getBalance($userId);

        return $this->response->setJSON([
            'balance' => $balance,
            'score'   => (float)($user['score'] ?? 0)
        ]);
    }

    public function getPendingDeliveries()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        $contractModel = new \App\Models\ContractModel();
        $deliveries = $contractModel->getPendingDeliveries($userId);

        $result = [];
        foreach ($deliveries as $d) {
            $result[] = [
                'id'             => $d['id'],
                'total_amount'   => (float) $d['total_amount'],
                'delivered_usdt' => (float) $d['delivered_usdt'],
                'pending_usdt'   => (float) $d['pending_usdt'],
                'type'           => strtoupper($d['type']),
                'due_date'       => $d['due_date'],
                'status'         => $d['status'],
            ];
        }

        return $this->response->setJSON($result);
    }

    public function getMyDebts()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        session()->close();

        $today = date('Y-m-d');
        $contractModel = new \App\Models\ContractModel();

        $rows = $contractModel->getOpenDebtsByUser((int) $userId);

        $summarize = function (array $list): array {
            return [
                'count'              => count($list),
                'total_brl_owed'  => round(array_sum(array_column($list, 'remaining_balance')), 2),
                'total_usdt_owed' => round(array_sum(array_column($list, 'usdt_owed')), 2),
            ];
        };

        $todos    = $rows;
        $d0       = array_values(array_filter($rows, fn($c) => $c['delivery_type'] === 'D+0'));
        $d1       = array_values(array_filter($rows, fn($c) => $c['delivery_type'] === 'D+1'));
        $d2       = array_values(array_filter($rows, fn($c) => $c['delivery_type'] === 'D+2'));
        $atrasado = array_values(array_filter($rows, fn($c) => $c['status'] === 'overdue' || $c['due_date_only'] < $today));

        return $this->response->setJSON([
            'todos'    => $summarize($todos),
            'd0'       => $summarize($d0),
            'd1'       => $summarize($d1),
            'd2'       => $summarize($d2),
            'atrasado' => $summarize($atrasado),
        ]);
    }

    public function getContracts()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        $contractModel = new \App\Models\ContractModel();

        $contracts = $contractModel
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
            ->orderBy('due_date', 'ASC')
            ->findAll();

        $today = date('Y-m-d');
        $result = [];

        foreach ($contracts as $c) {
            $dueDate        = date('Y-m-d', strtotime($c['due_date']));
            $fullyDelivered = (float)$c['delivered_usdt'] >= (float)$c['total_amount'];
            $daysDiff       = (int)round((strtotime($today) - strtotime($dueDate)) / 86400);

            $result[] = [
                'id'                    => $c['id'],
                'total_amount'          => (float) $c['total_amount'],
                'delivered_usdt'        => (float) $c['delivered_usdt'],
                'total_brl'             => (float) $c['total_brl'],
                'paid_amount'           => (float) $c['paid_amount'],
                'remaining_balance'     => (float) $c['remaining_balance'],
                'interest_accumulated'  => (float) $c['interest_accumulated'],
                'type'                  => strtoupper($c['type']),
                'due_date'              => $c['due_date'],
                'status'               => $c['status'],
                'due_today'             => $dueDate === $today,
                'delivery_days_overdue' => $fullyDelivered ? null : $daysDiff,
            ];
        }

        return $this->response->setJSON($result);
    }

    public function getStatement()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = (int) session()->get('user_id');
        session()->close();

        $page   = max(1, (int) ($this->request->getGet('page') ?: 1));
        $nature = $this->request->getGet('nature') ?: '';
        $search = $this->request->getGet('q') ?: '';

        $model  = new \App\Models\FinancialStatementModel();
        $result = $model->getUserStatement($userId, $page, 20, $nature, $search);

        return $this->response->setJSON($result);
    }

    public function getNotifications()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = (int) session()->get('user_id');
        session()->close();

        $model  = new \App\Models\FinancialStatementModel();
        $result = $model->getUserStatement($userId, 1, 30);

        return $this->response->setJSON($result);
    }

    public function getChatMessages()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        $lastId = (int)$this->request->getGet('last_id');
        
        $builder = $db->table('chat_messages')
            ->where('user_id', $userId);
            
        if ($lastId > 0) {
            $builder->where('id >', $lastId);
        } else {
            $builder->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        }
        
        $messages = $builder->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
            
        return $this->response->setJSON($messages);
    }

    private function extractAuthCodeFromText($ocrText)
    {
        if (empty($ocrText)) {
            return null;
        }

        // 1. Procura padrão de ID de transação PIX (EndToEndId): começa com 'E' seguido de 31 caracteres alfanuméricos
        if (preg_match('/\b(E[A-Za-z0-9]{31})\b/', $ocrText, $matches)) {
            return $matches[1];
        }

        // 2. Procura padrão de autenticação com hífen/pontos comum em bancos (ex: A1B2.C3D4... ou UUIDs)
        if (preg_match('/\b([A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12})\b/', $ocrText, $matches)) {
            return $matches[1];
        }

        // 3. Procura por termos como "Autenticação", "Protocolo", "Transação", "Controle", "Código" seguido de caracteres
        $keywords = ['autenticacao', 'autenticação', 'protocolo', 'transacao', 'transação', 'controle', 'codigo', 'código', 'id'];
        foreach ($keywords as $kw) {
            if (preg_match('/' . preg_quote($kw, '/') . '\s*[:=-]?\s*([A-Za-z0-9.\/-]{8,40})/i', $ocrText, $matches)) {
                $code = trim($matches[1], " \t\n\r\0\x0B.:-");
                if (strlen($code) >= 8) {
                    return $code;
                }
            }
        }

        // 4. Qualquer string alfanumérica contínua longa que pareça um hash/id (comprimento entre 16 e 40)
        if (preg_match_all('/\b([A-Za-z0-9]{16,40})\b/', $ocrText, $matches)) {
            foreach ($matches[1] as $candidate) {
                // Se contiver letras e números (para evitar pegar um monte de zeros ou números de conta normais)
                if (preg_match('/[A-Za-z]/', $candidate) && preg_match('/\d/', $candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function performOCR($filePath)
    {
        $fullPath = realpath(FCPATH . $filePath);
        if (!$fullPath || !file_exists($fullPath)) {
            log_message('error', 'OCR: Arquivo não encontrado em ' . FCPATH . $filePath);
            return null;
        }

        // Utilizando OCR.space Free API
        // Nota: 'helloworld' é uma chave de demonstração com limites estritos.
        // Registre-se em https://ocr.space/ocrapi para obter uma chave gratuita.
        $apiKey = 'helloworld'; 
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.ocr.space/parse/image');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'apikey' => $apiKey,
                'file' => new \CURLFile($fullPath),
                'language' => 'por',
                'isOverlayRequired' => 'false',
                'isTable' => 'true',
                'OCREngine' => '2' // Engine 2 é melhor para recibos
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            
            if ($err) {
                log_message('error', 'OCR cURL Error: ' . $err);
                return null;
            }

            $result = json_decode($response, true);
            
            if (isset($result['ParsedResults'][0]['ParsedText'])) {
                return $result['ParsedResults'][0]['ParsedText'];
            }
            
            if (isset($result['ErrorMessage'])) {
                log_message('error', 'OCR API Error: ' . implode(', ', (array)$result['ErrorMessage']));
            }
        } catch (\Throwable $e) {
            log_message('error', 'OCR Exception: ' . $e->getMessage());
        }
        
        return null;
    }

    private function getLatestRateFromDb($feePercent)
    {
        $db = \Config\Database::connect();
        $lastRecord = $db->table('dollar_history')
                         ->orderBy('created_at', 'DESC')
                         ->limit(1)
                         ->get()
                         ->getRow();

        if ($lastRecord) {
            $baseRate = (float) $lastRecord->base_rate;
        } else {
            $baseRate = $this->getDollarRate(0);
        }

        return [
            'base_rate' => $baseRate,
            'rate' => $baseRate * (1 + ($feePercent / 100))
        ];
    }

    private function getDollarRate($feePercent, $settlement = 'D0')
    {
        $cacheKey = 'dollar_rate_' . $settlement;
        $baseRate = cache($cacheKey);

        if ($baseRate === null) {
            // 1. Tenta buscar cotação no Transfero OTC (Staging)
            $transferoRate = $this->getTransferoOtcRate($settlement);
            
            if ($transferoRate !== null) {
                $baseRate = $transferoRate;
            } else {
                // 2. Fallback para Binance
                $apiUrl = 'https://api.binance.com/api/v3/ticker/price?symbol=USDTBRL';
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
                $response = curl_exec($ch);
                $error = curl_error($ch);
                curl_close($ch);
                
                if (!$error) {
                    $data = json_decode($response, true);
                    if (isset($data['price'])) {
                        $baseRate = (float) $data['price'];
                    }
                } else {
                    log_message('error', 'Binance API CURL Error: ' . $error);
                }
            }

            if ($baseRate !== null) {
                // Cache the baseline rate for 5 seconds to avoid external rate-limiting
                cache()->save($cacheKey, $baseRate, 5);
            }
        }

        if ($baseRate !== null) {
            // Add dynamic real-time fluctuation so trend goes up and down
            $fluctuation = (mt_rand(-30, 30) / 10000);
            $baseRate += $fluctuation;

            $rateWithFee = $baseRate * (1 + ($feePercent / 100));
            return $rateWithFee;
        }

        return null;
    }

    private function getTransferoOtcRate($settlement = 'D0')
    {
        $apiKey = env('TRANSFERO_OTC_API_KEY');
        $baseUrl = 'https://staging.otc.transfero.com';

        try {
            // 1. Tentar ler os preços do cache local (válido por 10 segundos)
            $cachedPrices = cache()->get('transfero_otc_prices');
            if (is_array($cachedPrices) && isset($cachedPrices[$settlement])) {
                return $cachedPrices[$settlement];
            }

            // 2. Tentar ler o token JWT do cache local (válido por 25 minutos)
            $token = cache()->get('transfero_otc_jwt');
            
            if (!$token) {
                // Passo A: Login para obter o JWT
                $ch = curl_init($baseUrl . '/v1/auth/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['api_key' => $apiKey]));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                
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

                // Salva o JWT no cache por 25 minutos (1500 segundos)
                cache()->save('transfero_otc_jwt', $token, 1500);
            }

            // Passo B: Buscar Tabela de Preços Geral
            $ch = curl_init($baseUrl . '/v1/prices');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $pricesResponse = curl_exec($ch);
            $pricesError = curl_error($ch);
            curl_close($ch);

            if ($pricesError) {
                // Se der erro de autorização (token expirado no servidor antes de 25 min), apaga o token do cache
                cache()->delete('transfero_otc_jwt');
                log_message('error', 'Transfero Prices CURL Error: ' . $pricesError);
                return null;
            }

            $pricesData = json_decode($pricesResponse, true);
            
            // Se o token for inválido (HTTP 401), removemos do cache
            if (isset($pricesData['statusCode']) && $pricesData['statusCode'] == 401) {
                cache()->delete('transfero_otc_jwt');
                return null;
            }

            if (isset($pricesData['prices']['USDT'])) {
                $usdtPrices = $pricesData['prices']['USDT'];
                $pricesToCache = [];
                foreach (['D0', 'D1', 'D2'] as $s) {
                    if (isset($usdtPrices[$s]['price'])) {
                        $pricesToCache[$s] = (float) $usdtPrices[$s]['price'];
                    }
                }

                if (!empty($pricesToCache)) {
                    // Salva as cotações no cache por 10 segundos
                    cache()->save('transfero_otc_prices', $pricesToCache, 10);
                    return $pricesToCache[$settlement] ?? null;
                }
            }

            log_message('error', 'Transfero Prices API Failure: ' . $pricesResponse);
        } catch (\Throwable $e) {
            log_message('error', 'Transfero API Exception: ' . $e->getMessage());
        }

        return null;
    }

    public function depositStore()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = session()->get('user_id');
        $amounts = $this->request->getPost('amounts');
        $notesList = $this->request->getPost('notes');
        $uploadedFiles = $this->request->getFileMultiple('proofs');

        $depositModel = new \App\Models\DepositModel();

        if (is_array($amounts)) {
            if (empty($amounts)) {
                return $this->response->setJSON(['error' => 'Adicione pelo menos um depósito.'])->setStatusCode(400);
            }

            // Validate amounts
            foreach ($amounts as $val) {
                if ((float)$val <= 0) {
                    return $this->response->setJSON(['error' => 'Informe um valor válido em todos os depósitos.'])->setStatusCode(400);
                }
            }

            // Check if files array exists
            if (!$uploadedFiles || count($uploadedFiles) < count($amounts)) {
                return $this->response->setJSON(['error' => 'Comprovante obrigatório para todos os depósitos.'])->setStatusCode(400);
            }

            for ($i = 0; $i < count($amounts); $i++) {
                $amount = (float) $amounts[$i];
                $notes = $notesList[$i] ?? '';
                $file = $uploadedFiles[$i] ?? null;

                if (!$file || !$file->isValid()) {
                    return $this->response->setJSON(['error' => 'Comprovante inválido ou ausente.'])->setStatusCode(400);
                }

                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/deposits', $newName);
                $proofPath = 'uploads/deposits/' . $newName;

                $depositModel->insert([
                    'user_id'    => $userId,
                    'amount'     => $amount,
                    'proof_file' => $proofPath,
                    'status'     => 'pending',
                    'notes'      => $notes,
                ]);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Depósitos enviados e aguardando validação.']);
        } else {
            // Fallback for single item (old API)
            $amount = (float) $this->request->getPost('amount');
            $notes  = $this->request->getPost('notes') ?? '';
            $singleFile = $this->request->getFile('proof');

            if ($amount <= 0) {
                return $this->response->setJSON(['error' => 'Informe um valor válido.'])->setStatusCode(400);
            }

            if (!$singleFile || !$singleFile->isValid()) {
                return $this->response->setJSON(['error' => 'Comprovante obrigatório.'])->setStatusCode(400);
            }

            $newName = $singleFile->getRandomName();
            $singleFile->move(FCPATH . 'uploads/deposits', $newName);
            $proofPath = 'uploads/deposits/' . $newName;

            $depositModel->insert([
                'user_id'    => $userId,
                'amount'     => $amount,
                'proof_file' => $proofPath,
                'status'     => 'pending',
                'notes'      => $notes,
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Depósito enviado e aguardando validação.']);
        }
    }
}
