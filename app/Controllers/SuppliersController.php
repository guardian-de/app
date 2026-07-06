<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class SuppliersController extends BaseController
{
    public function index()
    {
        $model = new SupplierModel();
        return view('admin/suppliers/index', [
            'suppliers'   => $model->orderBy('name', 'ASC')->findAll(),
            'active_menu' => 'suppliers',
        ]);
    }

    public function store()
    {
        $name = trim($this->request->getPost('name'));
        if (empty($name)) {
            return redirect()->back()->with('error', 'Nome do fornecedor é obrigatório.');
        }

        $model = new SupplierModel();
        if ($model->where('name', $name)->first()) {
            return redirect()->back()->with('error', 'Já existe um fornecedor com este nome.');
        }

        $model->insert(['name' => $name, 'enabled' => 1]);
        return redirect()->to(url_to('admin_suppliers'))->with('success', 'Fornecedor "' . esc($name) . '" cadastrado.');
    }

    public function toggle(int $id)
    {
        $model    = new SupplierModel();
        $supplier = $model->find($id);
        if (!$supplier) {
            return redirect()->back()->with('error', 'Fornecedor não encontrado.');
        }

        $model->update($id, ['enabled' => $supplier['enabled'] ? 0 : 1]);
        $label = $supplier['enabled'] ? 'desativado' : 'ativado';
        return redirect()->to(url_to('admin_suppliers'))->with('success', 'Fornecedor "' . esc($supplier['name']) . '" ' . $label . '.');
    }
}
