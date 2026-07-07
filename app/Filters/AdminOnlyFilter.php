<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session()->get('user_role');
        $perms = session()->get('user_permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [];
        }

        if ($role !== 'admin' && !in_array('usuarios', $perms)) {
            return redirect()->to('/admin/contracts')->with('error', 'Acesso restrito a administradores.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
