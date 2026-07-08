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
        $inputMode = $json->input_mode ?? ($usdtAmountReq > 0 ? 'usdt' : 'brl');
        if ($inputMode === 'brl') {
            // Recalcula o USDT a partir do BRL informado, ignorando qualquer amount_usdt pré-calculado no cliente
            $usdtAmountReq = 0;
        }

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

        if ($type === 'buy' && $usdtAmount < self::MIN_BUY_USDT) {
            $errMsg = $userLang == 'zh-CN'
                ? '最低购买金额为 ' . self::MIN_BUY_USDT . ' USDT。'
                : 'O valor mínimo de compra é ' . self::MIN_BUY_USDT . ' USDT.';
            return $this->response->setJSON(['error' => $errMsg])->setStatusCode(400);
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

        if (($user['purchase_model'] ?? 'usdt') === 'both' && $user['last_purchase_mode'] !== $inputMode) {
            $userModel->update($userId, ['last_purchase_mode' => $inputMode]);
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

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?: '',
            'end_date'   => $this->request->getGet('end_date')   ?: '',
            'type'       => $this->request->getGet('type')       ?: '',
            'status'     => $this->request->getGet('status')     ?: '',
        ];

        $model  = new \App\Models\FinancialStatementModel();
        $result = $model->getUserStatement($userId, $page, 20, $nature, $search, $filters);

        return $this->response->setJSON($result);
    }

    public function exportStatementPdf()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(url_to('login'));
        }

        $userId = (int) session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        $nature = $this->request->getGet('nature') ?: '';
        $search = $this->request->getGet('q') ?: '';

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?: '',
            'end_date'   => $this->request->getGet('end_date')   ?: '',
            'type'       => $this->request->getGet('type')       ?: '',
            'status'     => $this->request->getGet('status')     ?: '',
        ];

        $model  = new \App\Models\FinancialStatementModel();
        $result = $model->getUserStatement($userId, 1, -1, $nature, $search, $filters);
        $items  = $result['data'] ?? [];

        $typeLabels = [
            'deposit'              => 'Depósito Aprovado',
            'withdrawal'           => 'Saída / Retirada',
            'margin_lock'          => 'Compra de USDT',
            'limit_release'        => 'Liberação de Limite',
            'partial_amortization' => 'Amortização Parcial',
            'full_settlement'      => 'Liquidação Integral',
            'late_fee'             => 'Multa por Atraso',
            'adjustment_add'       => 'Ajuste de Crédito',
            'adjustment_subtract'  => 'Ajuste de Débito',
            'deposit_pending'      => 'Depósito Pendente',
            'deposit_rejected'     => 'Depósito Rejeitado',
        ];

        $dateFilterStr = 'Período: ';
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $dateFilterStr .= date('d/m/Y', strtotime($filters['start_date'])) . ' até ' . date('d/m/Y', strtotime($filters['end_date']));
        } elseif (!empty($filters['start_date'])) {
            $dateFilterStr .= 'A partir de ' . date('d/m/Y', strtotime($filters['start_date']));
        } elseif (!empty($filters['end_date'])) {
            $dateFilterStr .= 'Até ' . date('d/m/Y', strtotime($filters['end_date']));
        } else {
            $dateFilterStr .= 'Todo o histórico';
        }

        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Extrato Financeiro - ' . esc($user['login']) . '</title>
    <style>
        body { font-family: "Segoe UI", Helvetica, Arial, sans-serif; color: #1e293b; background: #fff; margin: 30px; font-size: 13px; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: 700; margin: 0 0 5px 0; color: #0f172a; }
        .header p { margin: 2px 0; color: #64748b; }
        .info { margin-bottom: 20px; }
        .info span { font-weight: bold; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 600; text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; padding: 12px; vertical-align: top; }
        tr:nth-child(even) td { background: #fafafa; }
        .amount-c { color: #16a34a; font-weight: bold; }
        .amount-d { color: #dc2626; font-weight: bold; }
        .amount-p { color: #d97706; }
        .amount-r { color: #dc2626; text-decoration: line-through; }
        .details { font-size: 11px; color: #64748b; margin-top: 4px; background: #f8fafc; padding: 6px 10px; border-radius: 6px; display: inline-block; }
        @media print {
            body { margin: 15px; font-size: 12px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>GUARDIAN CORRETORA</h1>
            <p>Relatório de Extrato Financeiro</p>
        </div>
        <div style="text-align: right;">
            <p><span>Cliente:</span> ' . esc($user['login']) . '</p>
            <p>' . $dateFilterStr . '</p>
            <p>Gerado em: ' . date('d/m/Y H:i') . '</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Operação</th>
                <th>Descrição</th>
                <th style="text-align: right;">Valor</th>
            </tr>
        </thead>
        <tbody>';

        if (empty($items)) {
            echo '<tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">Nenhuma transação encontrada para os filtros selecionados.</td></tr>';
        } else {
            foreach ($items as $item) {
                $isPending = $item['operation_type'] === 'deposit_pending';
                $isRejected = $item['operation_type'] === 'deposit_rejected';
                $isCredit = $item['nature'] === 'C' || $item['operation_type'] === 'withdrawal';

                $class = 'amount-d';
                $sign = '− ';
                if ($isPending) { $class = 'amount-p'; $sign = ''; }
                elseif ($isRejected) { $class = 'amount-r'; $sign = ''; }
                elseif ($isCredit) { $class = 'amount-c'; $sign = '+ '; }

                $label = $typeLabels[$item['operation_type']] ?? $item['operation_type'];
                $dateStr = date('d/m/Y H:i', strtotime($item['transaction_date']));
                $amount = number_format($item['amount'], 2, ',', '.');
                $amountStr = $item['unit'] === 'USDT' ? $amount . ' USDT' : 'R$ ' . $amount;

                $details = '';
                if ($item['operation_type'] === 'margin_lock') {
                    $parts = [];
                    if ($item['usdt_amount'] != null) {
                        $parts[] = 'USDT: ' . number_format($item['usdt_amount'], 2, ',', '.') . ' USDT';
                    }
                    if ($item['spot_rate'] != null) {
                        $parts[] = 'Cotação: R$ ' . number_format($item['spot_rate'], 4, ',', '.');
                    }
                    if ($item['purchase_hash']) {
                        $parts[] = 'Hash: ' . esc($item['purchase_hash']);
                    }
                    if (!empty($parts)) {
                        $details = '<div class="details">' . implode(' &middot; ', $parts) . '</div>';
                    }
                } elseif ($item['operation_type'] === 'withdrawal' && !empty($item['notes'])) {
                    $details = '<div class="details">Destino: ' . esc($item['notes']) . '</div>';
                }

                echo '<tr>
                    <td style="white-space: nowrap;">' . $dateStr . '</td>
                    <td><strong>' . esc($label) . '</strong></td>
                    <td>
                        ' . esc($item['description']) . '
                        ' . $details . '
                    </td>
                    <td style="text-align: right;" class="' . $class . '">' . $sign . $amountStr . '</td>
                </tr>';
            }
        }

        echo '</tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>';
        exit;
    }

    public function exportStatementXlsx()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(url_to('login'));
        }

        $userId = (int) session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        $nature = $this->request->getGet('nature') ?: '';
        $search = $this->request->getGet('q') ?: '';

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?: '',
            'end_date'   => $this->request->getGet('end_date')   ?: '',
            'type'       => $this->request->getGet('type')       ?: '',
            'status'     => $this->request->getGet('status')     ?: '',
        ];

        $model  = new \App\Models\FinancialStatementModel();
        $result = $model->getUserStatement($userId, 1, -1, $nature, $search, $filters);
        $items  = $result['data'] ?? [];

        $typeLabels = [
            'deposit'              => 'Depósito Aprovado',
            'withdrawal'           => 'Saída / Retirada',
            'margin_lock'          => 'Compra de USDT',
            'limit_release'        => 'Liberação de Limite',
            'partial_amortization' => 'Amortização Parcial',
            'full_settlement'      => 'Liquidação Integral',
            'late_fee'             => 'Multa por Atraso',
            'adjustment_add'       => 'Ajuste de Crédito',
            'adjustment_subtract'  => 'Ajuste de Débito',
            'deposit_pending'      => 'Depósito Pendente',
            'deposit_rejected'     => 'Depósito Rejeitado',
        ];

        $filename = 'extrato_' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $user['login'])) . '_' . date('Ymd_His') . '.xls';

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "\xEF\xBB\xBF";

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
    <style>
        table { border-collapse: collapse; }
        th { background: #3b82f6; color: white; font-weight: bold; border: 1px solid #ccc; padding: 8px; }
        td { border: 1px solid #ccc; padding: 8px; vertical-align: top; }
        .amount-c { color: #16a34a; font-weight: bold; }
        .amount-d { color: #dc2626; font-weight: bold; }
        .amount-p { color: #d97706; }
    </style>
</head>
<body>
    <h3>GUARDIAN CORRETORA - EXTRATO FINANCEIRO</h3>
    <p><b>Cliente:</b> ' . esc($user['login']) . '</p>
    <p><b>Data de Emissão:</b> ' . date('d/m/Y H:i') . '</p>
    <br/>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Operação</th>
                <th>Descrição</th>
                <th>Natureza</th>
                <th>Unidade</th>
                <th>Valor</th>
                <th>Taxa %</th>
                <th>Taxa R$</th>
                <th>USDT Adquirido</th>
                <th>Taxa Comercial</th>
                <th>Hash / Obs</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($items as $item) {
            $isPending = $item['operation_type'] === 'deposit_pending';
            $isRejected = $item['operation_type'] === 'deposit_rejected';
            $isCredit = $item['nature'] === 'C' || $item['operation_type'] === 'withdrawal';

            $class = 'amount-d';
            $sign = '-';
            if ($isPending) { $class = 'amount-p'; $sign = ''; }
            elseif ($isRejected) { $class = 'amount-r'; $sign = ''; }
            elseif ($isCredit) { $class = 'amount-c'; $sign = '+'; }

            $label = $typeLabels[$item['operation_type']] ?? $item['operation_type'];
            $dateStr = date('d/m/Y H:i', strtotime($item['transaction_date']));
            
            $feePercentStr = $item['fee_percent'] !== null ? number_format($item['fee_percent'], 2, ',', '.') . '%' : '';
            $feeBrlStr = $item['fee_brl'] !== null ? number_format($item['fee_brl'], 2, ',', '.') : '';
            $usdtStr = $item['usdt_amount'] !== null ? number_format($item['usdt_amount'], 2, ',', '.') : '';
            $spotStr = $item['spot_rate'] !== null ? number_format($item['spot_rate'], 4, ',', '.') : '';
            
            $hashObs = '';
            if ($item['operation_type'] === 'margin_lock') {
                $hashObs = $item['purchase_hash'] ?: '';
            } elseif ($item['operation_type'] === 'withdrawal') {
                $hashObs = $item['notes'] ?: '';
            }

            echo '<tr>
                <td>' . $item['id'] . '</td>
                <td>' . $dateStr . '</td>
                <td>' . esc($label) . '</td>
                <td>' . esc($item['description']) . '</td>
                <td style="text-align: center;">' . ($item['nature'] ?: '-') . '</td>
                <td style="text-align: center;">' . $item['unit'] . '</td>
                <td class="' . $class . '" style="text-align: right;">' . $sign . number_format($item['amount'], 2, ',', '.') . '</td>
                <td style="text-align: right;">' . $feePercentStr . '</td>
                <td style="text-align: right;">' . $feeBrlStr . '</td>
                <td style="text-align: right;">' . $usdtStr . '</td>
                <td style="text-align: right;">' . $spotStr . '</td>
                <td>' . esc($hashObs) . '</td>
            </tr>';
        }

        echo '</tbody>
    </table>
</body>
</html>';
        exit;
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
        return (new \App\Libraries\OcrSpaceClient())->read($filePath);
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

        $userId        = session()->get('user_id');
        $notesList     = $this->request->getPost('notes') ?? [];
        $uploadedFiles = $this->request->getFileMultiple('proofs');

        if (!$uploadedFiles || count($uploadedFiles) === 0) {
            return $this->response->setJSON(['error' => 'Envie ao menos um comprovante.'])->setStatusCode(400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];
        $maxBytes     = 10 * 1024 * 1024;

        foreach ($uploadedFiles as $file) {
            if (!$file || !$file->isValid()) {
                return $this->response->setJSON(['error' => 'Um dos arquivos é inválido.'])->setStatusCode(400);
            }
            if ($file->getSize() > $maxBytes) {
                return $this->response->setJSON(['error' => 'Cada comprovante deve ter no máximo 10MB.'])->setStatusCode(400);
            }
            if (!in_array($file->getMimeType(), $allowedMimes, true)) {
                return $this->response->setJSON(['error' => 'Formato de arquivo não suportado. Envie imagem ou PDF.'])->setStatusCode(400);
            }
        }

        $depositModel = new \App\Models\DepositModel();
        $created      = 0;

        // A leitura do comprovante (OCR + IA) é lenta (pode levar dezenas de
        // segundos por arquivo) e roda em lote no cron /cron/process-deposit-ocr,
        // não aqui — senão o upload de vários comprovantes de uma vez estoura o
        // timeout da requisição. Aqui só salvamos o arquivo e criamos o registro
        // como 'processing'; o valor aparece em instantes, assim que o cron rodar.
        foreach ($uploadedFiles as $i => $file) {
            $notes = trim($notesList[$i] ?? '');

            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/deposits', $newName);
            $proofPath = 'uploads/deposits/' . $newName;

            $depositModel->insert([
                'user_id'    => $userId,
                'amount'     => null,
                'ocr_status' => 'processing',
                'proof_file' => $proofPath,
                'status'     => 'pending',
                'notes'      => $notes,
            ]);
            $created++;
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $created > 1
                ? "{$created} depósitos enviados e aguardando validação."
                : 'Depósito enviado e aguardando validação.',
        ]);
    }
}
