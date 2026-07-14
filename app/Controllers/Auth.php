<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        $this->runSchemaGuards();
        
        if (session()->get('isLoggedIn')) {
            $role = session()->get('user_role');
            if ($role === 'admin') {
                return redirect()->to('/admin/contracts');
            } elseif ($role === 'operator') {
                return redirect()->to('/admin/contracts');
            } else {
                return redirect()->to('/dashboard');
            }
        }
        
        return view('auth/login');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('user_role');
            if ($role === 'admin') {
                return redirect()->to('/admin/contracts');
            } elseif ($role === 'operator') {
                return redirect()->to('/admin/contracts');
            } else {
                return redirect()->to('/dashboard');
            }
        }
        return view('auth/register');
    }

    public function store()
    {
        $userModel = new UserModel();
        
        $data = [
            'login'       => $this->request->getPost('login'),
            'password'    => $this->request->getPost('password'),
            'usdt_wallet' => $this->request->getPost('usdt_wallet'),
        ];

        if (!$userModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        $userId = $userModel->getInsertID();
        if ($userId && !empty($data['usdt_wallet'])) {
            $walletModel = new \App\Models\UserWalletModel();
            $walletModel->insert([
                'user_id' => $userId,
                'address' => $data['usdt_wallet'],
                'is_default' => 1
            ]);
        }

        return redirect()->to('/')->with('success', 'Cadastro realizado com sucesso! Faça login.');
    }

    private function runSchemaGuards(): void
    {
        $db = \Config\Database::connect();
        try { $db->query("CREATE TABLE IF NOT EXISTS `suppliers` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(150) NOT NULL,
            `enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}

        try { $db->query("CREATE TABLE IF NOT EXISTS `usdt_lots` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `supplier` VARCHAR(150) NOT NULL,
            `purchase_hash` VARCHAR(255) NULL,
            `delivery_type` VARCHAR(10) NULL,
            `usdt_amount` DECIMAL(18,4) NOT NULL,
            `conversion_rate` DECIMAL(18,6) NOT NULL,
            `total_brl` DECIMAL(18,2) NOT NULL,
            `total_brl_overridden` TINYINT(1) NOT NULL DEFAULT 0,
            `usdt_reserved` DECIMAL(18,4) NOT NULL DEFAULT 0,
            `usdt_delivered` DECIMAL(18,4) NOT NULL DEFAULT 0,
            `profit_brl` DECIMAL(18,2) NOT NULL DEFAULT 0,
            `status` ENUM('active','depleted','cancelled') NOT NULL DEFAULT 'active',
            `created_by` INT(11) UNSIGNED NOT NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}

        try { $db->query("CREATE TABLE IF NOT EXISTS `lot_allocations` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `lot_id` INT(11) UNSIGNED NOT NULL,
            `contract_id` INT(11) UNSIGNED NULL,
            `transaction_id` INT(11) UNSIGNED NULL,
            `usdt_amount` DECIMAL(18,4) NOT NULL,
            `status` ENUM('reserved','delivered','cancelled') NOT NULL DEFAULT 'reserved',
            `profit_brl` DECIMAL(18,2) NULL,
            `allocated_by` INT(11) UNSIGNED NOT NULL,
            `delivered_by` INT(11) UNSIGNED NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}

        try { $db->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) UNSIGNED NULL,
            `action` VARCHAR(100) NOT NULL,
            `entity_type` VARCHAR(50) NOT NULL,
            `entity_id` INT(11) UNSIGNED NOT NULL,
            `payload` TEXT NULL,
            `ip_address` VARCHAR(45) NULL,
            `created_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}

        // Renomeia credit_limit → score se ainda existir a coluna antiga
        try {
            if ($db->fieldExists('credit_limit', 'users') && !$db->fieldExists('score', 'users')) {
                $db->query("ALTER TABLE `users` CHANGE `credit_limit` `score` DECIMAL(15,2) NOT NULL DEFAULT 0.00");
            }
        } catch (\Throwable $e) {}

        try {
            if (!$db->fieldExists('purchase_model', 'users')) {
                $db->query("ALTER TABLE `users` ADD COLUMN `purchase_model` ENUM('usdt','brl','both') NOT NULL DEFAULT 'usdt' AFTER `allowed_delivery_types`");
            }
            if (!$db->fieldExists('last_purchase_mode', 'users')) {
                $db->query("ALTER TABLE `users` ADD COLUMN `last_purchase_mode` ENUM('usdt','brl') NULL AFTER `purchase_model`");
            }
            if (!$db->fieldExists('two_factor_secret', 'users')) {
                $db->query("ALTER TABLE `users` ADD COLUMN `two_factor_secret` VARCHAR(100) NULL AFTER `last_purchase_mode`");
            }
            if (!$db->fieldExists('two_factor_enabled', 'users')) {
                $db->query("ALTER TABLE `users` ADD COLUMN `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `two_factor_secret`");
            }
            if (!$db->fieldExists('status', 'user_wallets')) {
                $db->query("ALTER TABLE `user_wallets` ADD COLUMN `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `is_default`");
            }
        } catch (\Throwable $e) {}
    }

    public function authenticate()
    {
        $session = session();
        $userModel = new UserModel();
        
        $login = $this->request->getPost('login');
        $password = $this->request->getPost('password');

        $user = $userModel->where('login', $login)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if (!empty($user['two_factor_enabled']) && !empty($user['two_factor_secret'])) {
                    $session->set('temp_2fa_user_id', $user['id']);
                    return redirect()->to('/login/2fa');
                }

                $sessionData = [
                    'user_id'    => $user['id'],
                    'user_name'  => $user['login'],
                    'user_login' => $user['login'],
                    'user_fee'   => $user['fee_percent'],
                    'user_lang'  => $user['language'],
                    'user_wallet'=> $user['usdt_wallet'],
                    'user_role'  => $user['role'],
                    'user_permissions' => !empty($user['permissions']) ? json_decode($user['permissions'], true) : [],
                    'score' => $user['score'],
                    'isLoggedIn' => true,
                ];
                $session->set($sessionData);
                
                if ($user['role'] === 'admin') {
                    return redirect()->to('/admin/contracts');
                } elseif ($user['role'] === 'operator') {
                    $perms = !empty($user['permissions']) ? json_decode($user['permissions'], true) : [];
                    if (!is_array($perms)) {
                        $perms = [];
                    }
                    if (in_array('enviar_usdt', $perms)) {
                        return redirect()->to('/admin/contracts');
                    } elseif (in_array('transacoes', $perms)) {
                        return redirect()->to('/admin/transactions');
                    } elseif (in_array('usuarios', $perms)) {
                        return redirect()->to('/admin/users');
                    } elseif (in_array('lots', $perms)) {
                        return redirect()->to('/admin/lots');
                    } elseif (in_array('deposits', $perms)) {
                        return redirect()->to('/admin/deposits');
                    } elseif (in_array('suppliers', $perms)) {
                        return redirect()->to('/admin/suppliers');
                    } elseif (in_array('settings', $perms)) {
                        return redirect()->to('/admin/settings');
                    } else {
                        $session->destroy();
                        return redirect()->to('/')->with('error', 'Acesso negado: você não tem permissão para acessar esta área.');
                    }
                }

                return redirect()->to('/dashboard');
            } else {
                return redirect()->back()->with('error', 'Senha incorreta.');
            }
        } else {
            return redirect()->back()->with('error', 'Login não encontrado.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }

    public function changePassword()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        $data['role'] = session()->get('user_role');
        return view('auth/change_password', $data);
    }

    public function updatePassword()
    {
        if (!session()->get('isLoggedIn')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Sessão expirada. Faça login novamente.'])->setStatusCode(401);
            }
            return redirect()->to('/');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Usuário não encontrado.'])->setStatusCode(404);
            }
            return redirect()->to('/');
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword     = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validations
        if (!password_verify($currentPassword, $user['password'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Senha atual incorreta.'])->setStatusCode(400);
            }
            return redirect()->back()->with('error', 'Senha atual incorreta.');
        }

        if (strlen($newPassword) < 6) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'A nova senha deve ter pelo menos 6 caracteres.'])->setStatusCode(400);
            }
            return redirect()->back()->with('error', 'A nova senha deve ter pelo menos 6 caracteres.');
        }

        if ($newPassword !== $confirmPassword) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'A nova senha e a confirmação não coincidem.'])->setStatusCode(400);
            }
            return redirect()->back()->with('error', 'A nova senha e a confirmação não coincidem.');
        }

        // Save (automatic hashing will be performed by the model callback)
        $userModel->update($userId, [
            'password' => $newPassword
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Senha alterada com sucesso!']);
        }

        return redirect()->back()->with('success', 'Senha alterada com sucesso!');
    }

    public function login2fa()
    {
        if (!session()->get('temp_2fa_user_id')) {
            return redirect()->to('/');
        }
        return view('auth/login_2fa');
    }

    public function verifyLogin2fa()
    {
        $tempUserId = session()->get('temp_2fa_user_id');
        if (!$tempUserId) {
            return redirect()->to('/');
        }

        $code = $this->request->getPost('two_factor_code');
        if (empty($code)) {
            return redirect()->back()->with('error', 'Por favor, digite o código de 2 fatores.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($tempUserId);
        if (!$user) {
            session()->destroy();
            return redirect()->to('/');
        }

        if (!\App\Libraries\GoogleAuthenticator::verifyCode($user['two_factor_secret'], $code)) {
            return redirect()->back()->with('error', 'Código de 2 fatores inválido ou expirado.');
        }

        $session = session();
        $sessionData = [
            'user_id'    => $user['id'],
            'user_name'  => $user['login'],
            'user_login' => $user['login'],
            'user_fee'   => $user['fee_percent'],
            'user_lang'  => $user['language'],
            'user_wallet'=> $user['usdt_wallet'],
            'user_role'  => $user['role'],
            'user_permissions' => !empty($user['permissions']) ? json_decode($user['permissions'], true) : [],
            'score' => $user['score'],
            'isLoggedIn' => true,
        ];
        $session->set($sessionData);
        $session->remove('temp_2fa_user_id');

        if ($user['role'] === 'admin') {
            return redirect()->to('/admin/contracts');
        } elseif ($user['role'] === 'operator') {
            $perms = !empty($user['permissions']) ? json_decode($user['permissions'], true) : [];
            if (!is_array($perms)) {
                $perms = [];
            }
            if (in_array('enviar_usdt', $perms)) {
                return redirect()->to('/admin/contracts');
            } elseif (in_array('transacoes', $perms)) {
                return redirect()->to('/admin/transactions');
            } elseif (in_array('usuarios', $perms)) {
                return redirect()->to('/admin/users');
            } elseif (in_array('lots', $perms)) {
                return redirect()->to('/admin/lots');
            } elseif (in_array('deposits', $perms)) {
                return redirect()->to('/admin/deposits');
            } elseif (in_array('suppliers', $perms)) {
                return redirect()->to('/admin/suppliers');
            } elseif (in_array('settings', $perms)) {
                return redirect()->to('/admin/settings');
            } else {
                $session->destroy();
                return redirect()->to('/')->with('error', 'Acesso negado: você não tem permissão para acessar esta área.');
            }
        }

        return redirect()->to('/dashboard');
    }
}
