<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RateLimiterFilter implements FilterInterface
{
    /**
     * Check if request has exceeded limit.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        // Defaults: 15 requests per 60 seconds
        $capacity = 15;
        $seconds  = 60;

        if (is_array($arguments) && count($arguments) >= 2) {
            $capacity = (int) $arguments[0];
            $seconds  = (int) $arguments[1];
        }

        // Generate a key based on IP and URI path
        $key = 'rl_' . md5($request->getIPAddress() . '_' . $request->getUri()->getPath());

        // Check if exceeded limit
        if ($throttler->check($key, $capacity, $seconds) === false) {
            $response = Services::response();
            
            if ($request->isAJAX()) {
                return $response
                    ->setStatusCode(429)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Muitas requisições. Por favor, tente novamente mais tarde.'
                    ]);
            }

            return $response
                ->setStatusCode(429)
                ->setBody('Muitas requisições. Por favor, tente novamente mais tarde.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
