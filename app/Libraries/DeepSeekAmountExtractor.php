<?php

namespace App\Libraries;

class DeepSeekAmountExtractor
{
    private string $apiKey;
    private string $endpoint = 'https://api.deepseek.com/chat/completions';

    public function __construct()
    {
        $this->apiKey = (string) env('DEEPSEEK_API_KEY');
    }

    /**
     * Interpreta o texto (já extraído via OCR) de um comprovante de pagamento
     * e devolve se parece válido e qual o valor em BRL. Qualquer falha (rede,
     * chave ausente, resposta malformada) cai em is_proof=false/amount=null —
     * nunca aceita um valor não confiável, sempre empurra para revisão manual.
     *
     * @return array{is_proof: bool, amount: ?float}
     */
    public function extract(string $ocrText): array
    {
        $failure = ['is_proof' => false, 'amount' => null];

        $ocrText = trim($ocrText);
        if ($ocrText === '' || $this->apiKey === '') {
            return $failure;
        }

        $prompt = "Você é um assistente que analisa o texto extraído (via OCR) de um comprovante de "
            . "pagamento brasileiro (PIX, TED, DOC ou transferência bancária).\n"
            . "Responda SOMENTE em JSON, no formato exato:\n"
            . "{\"is_proof\": true|false, \"amount\": <número decimal em BRL ou null>}\n"
            . "Regras:\n"
            . "- \"is_proof\" deve ser true somente se o texto parecer claramente um comprovante de "
            . "pagamento/transferência bancária válido para depósito (contém termos como comprovante, PIX, "
            . "TED, DOC, transferência, banco, etc.). Recibos de outra natureza (compra, refeição/vale, "
            . "nota fiscal, cupom fiscal, boleto de cobrança) devem ser is_proof=false.\n"
            . "- \"amount\" é INDEPENDENTE de \"is_proof\": sempre que houver um valor em reais claramente "
            . "identificável no texto (ex: \"R$ 1.000,00\", \"Valor: 250,50\"), preencha \"amount\" com esse "
            . "número decimal (ex: 1000.00), mesmo que \"is_proof\" seja false. Só retorne \"amount\": null "
            . "se nenhum valor monetário aparecer no texto ou se houver mais de um valor e não for possível "
            . "saber qual é o principal.\n\n"
            . "Texto extraído do comprovante:\n---\n{$ocrText}\n---";

        try {
            $ch = curl_init($this->endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'model'    => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você extrai dados estruturados de comprovantes de pagamento brasileiros e responde apenas em JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature'     => 0,
            ]));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $response = curl_exec($ch);
            $err      = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                log_message('error', 'DeepSeek cURL Error: ' . $err);
                return $failure;
            }
            if ($httpCode !== 200) {
                log_message('error', 'DeepSeek API HTTP ' . $httpCode . ': ' . $response);
                return $failure;
            }

            $data    = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'] ?? null;
            if (!$content) {
                log_message('error', 'DeepSeek: resposta sem content.');
                return $failure;
            }

            $parsed = json_decode($content, true);
            if (!is_array($parsed) || !array_key_exists('is_proof', $parsed)) {
                log_message('error', 'DeepSeek: JSON de content malformado: ' . $content);
                return $failure;
            }

            $amount = null;
            if (isset($parsed['amount']) && is_numeric($parsed['amount'])) {
                $amount = round((float) $parsed['amount'], 2);
            }

            return ['is_proof' => (bool) $parsed['is_proof'], 'amount' => $amount];
        } catch (\Throwable $e) {
            log_message('error', 'DeepSeek Exception: ' . $e->getMessage());
            return $failure;
        }
    }
}
