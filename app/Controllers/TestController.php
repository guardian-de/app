<?php

namespace App\Controllers;

class TestController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $tx = $db->table('transactions')->where('id', 20)->get()->getRowArray();
        echo "TRANSACTION 20:\n";
        print_r($tx);

        if ($tx) {
            $contract = $db->table('contracts')->where('transaction_id', 20)->get()->getRowArray();
            echo "\nCONTRACT:\n";
            print_r($contract);
        }

        $fin = $db->table('financial_statements')->where('description LIKE', '%20%')->get()->getResultArray();
        echo "\nFINANCIAL STATEMENTS:\n";
        print_r($fin);
    }
}
