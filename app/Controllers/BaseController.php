<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url', 'security'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $session = service('session');
        $lang = $session->get('user_lang') ?? 'pt-BR';
        service('request')->setLocale($lang);
        service('language')->setLocale($lang);
    }

    protected function checkPermission(string $permission)
    {
        $role = session()->get('user_role');
        if ($role === 'admin') {
            return null;
        }

        $perms = session()->get('user_permissions') ?? [];
        if (!is_array($perms) || !in_array($permission, $perms)) {
            if ($this->request->isAJAX()) {
                $this->response->setJSON(['success' => false, 'message' => 'Permissão negada.'])->send();
                exit;
            }
            session()->setFlashdata('error', 'Acesso negado: você não tem permissão para acessar esta área.');
            
            $targetUrl = $this->getFirstAllowedUrl();
            if (current_url() === base_url($targetUrl)) {
                $targetUrl = '/logout';
            }
            header('Location: ' . base_url($targetUrl));
            exit;
        }
        return null;
    }

    protected function getFirstAllowedUrl(): string
    {
        $role = session()->get('user_role');
        if ($role === 'admin') {
            return '/admin/contracts';
        }

        $perms = session()->get('user_permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [];
        }

        if (in_array('enviar_usdt', $perms)) {
            return '/admin/contracts';
        }
        if (in_array('transacoes', $perms)) {
            return '/admin/transactions';
        }
        if (in_array('usuarios', $perms)) {
            return '/admin/users';
        }
        if (in_array('lots', $perms)) {
            return '/admin/lots';
        }
        if (in_array('deposits', $perms)) {
            return '/admin/deposits';
        }
        if (in_array('suppliers', $perms)) {
            return '/admin/suppliers';
        }
        if (in_array('settings', $perms)) {
            return '/admin/settings';
        }
        if (in_array('conciliation', $perms)) {
            return '/admin/conciliation';
        }
        if (in_array('chat', $perms)) {
            return '/admin/chat';
        }

        if ($role === 'operator') {
            return '/logout';
        }

        return '/dashboard';
    }
}
