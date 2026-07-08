<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class SuppliersController extends BaseController
{
    public function index()
    {
        if ($response = $this->checkPermission('suppliers')) return $response;
        $model = new SupplierModel();
        return view('admin/suppliers/index', [
            'suppliers'   => $model->orderBy('name', 'ASC')->findAll(),
            'active_menu' => 'suppliers',
        ]);
    }

    public function store()
    {
        if ($response = $this->checkPermission('suppliers')) return $response;
        $name = trim($this->request->getPost('name'));
        $wallet = trim($this->request->getPost('wallet') ?? '');
        if (empty($name)) {
            return redirect()->back()->with('error', 'Nome do fornecedor é obrigatório.');
        }
        if (empty($wallet)) {
            return redirect()->back()->with('error', 'Carteira do fornecedor é obrigatória.');
        }

        $model = new SupplierModel();
        if ($model->where('name', $name)->first()) {
            return redirect()->back()->with('error', 'Já existe um fornecedor com este nome.');
        }

        $model->insert([
            'name'    => $name,
            'wallet'  => $wallet,
            'enabled' => 1
        ]);
        return redirect()->to(url_to('admin_suppliers'))->with('success', 'Fornecedor "' . esc($name) . '" cadastrado.');
    }

    public function update(int $id)
    {
        if ($response = $this->checkPermission('suppliers')) return $response;
        $name = trim($this->request->getPost('name'));
        $wallet = trim($this->request->getPost('wallet') ?? '');
        if (empty($name)) {
            return redirect()->back()->with('error', 'Nome do fornecedor é obrigatório.');
        }
        if (empty($wallet)) {
            return redirect()->back()->with('error', 'Carteira do fornecedor é obrigatória.');
        }

        $model = new SupplierModel();
        $supplier = $model->find($id);
        if (!$supplier) {
            return redirect()->back()->with('error', 'Fornecedor não encontrado.');
        }

        $existing = $model->where('name', $name)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Já existe um fornecedor com este nome.');
        }

        $model->update($id, [
            'name'   => $name,
            'wallet' => $wallet,
        ]);
        return redirect()->to(url_to('admin_suppliers'))->with('success', 'Fornecedor "' . esc($name) . '" atualizado.');
    }

    public function toggle(int $id)
    {
        if ($response = $this->checkPermission('suppliers')) return $response;
        $model    = new SupplierModel();
        $supplier = $model->find($id);
        if (!$supplier) {
            return redirect()->back()->with('error', 'Fornecedor não encontrado.');
        }

        $model->update($id, ['enabled' => $supplier['enabled'] ? 0 : 1]);
        $label = $supplier['enabled'] ? 'desativado' : 'ativado';
        return redirect()->to(url_to('admin_suppliers'))->with('success', 'Fornecedor "' . esc($supplier['name']) . '" ' . $label . '.');
    }

    public function delete(int $id)
    {
        if ($response = $this->checkPermission('suppliers')) return $response;
        $model    = new SupplierModel();
        $supplier = $model->find($id);
        if (!$supplier) {
            return redirect()->back()->with('error', 'Fornecedor não encontrado.');
        }

        $model->delete($id);
        return redirect()->to(url_to('admin_suppliers'))->with('success', 'Fornecedor "' . esc($supplier['name']) . '" excluído.');
    }
}
