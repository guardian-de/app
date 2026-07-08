<?php

namespace App\Libraries;

class OcrSpaceClient
{
    /**
     * Lê o texto de uma imagem/PDF via OCR.space. $relativePath é relativo a FCPATH.
     */
    public function read(string $relativePath): ?string
    {
        $fullPath = realpath(FCPATH . $relativePath);
        if (!$fullPath || !file_exists($fullPath)) {
            log_message('error', 'OCR: Arquivo não encontrado em ' . FCPATH . $relativePath);
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
}
