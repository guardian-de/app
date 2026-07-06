<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('privacy', 'Home::privacy', ['as' => 'privacy']);
$routes->get('terms', 'Home::terms', ['as' => 'terms']);

$routes->get('dev', function() {
    return 'Hello World';
});



// Auth Routes
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login', 'Auth::authenticate', ['as' => 'authenticate']);
$routes->get('logout', 'Auth::logout', ['as' => 'logout']);

// Admin Routes (Substitui o cadastro público)
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    $routes->get('users', 'AdminController::index', ['as' => 'admin_users']);
    $routes->get('users/create', 'AdminController::create', ['as' => 'admin_users_create']);
    $routes->post('users/store', 'AdminController::store', ['as' => 'admin_users_store']);
    $routes->get('users/edit/(:num)', 'AdminController::edit/$1', ['as' => 'admin_users_edit']);
    $routes->post('users/update/(:num)', 'AdminController::update/$1', ['as' => 'admin_users_update']);
    $routes->post('users/adjust-limit/(:num)', 'AdminController::adjustLimit/$1', ['as' => 'admin_users_adjust_limit']);
    $routes->post('users/transfer-limit/(:num)', 'AdminController::transferLimit/$1', ['as' => 'admin_users_transfer_limit']);
    $routes->post('users/register-purchase/(:num)', 'AdminController::registerPurchase/$1', ['as' => 'admin_users_register_purchase']);
    $routes->get('users/statement/(:num)', 'AdminController::getUserStatement/$1', ['as' => 'admin_users_statement']);
    $routes->get('users/statement/export/(:num)', 'AdminController::exportUserStatementCsv/$1', ['as' => 'admin_users_statement_export']);
    $routes->get('users/activity/(:num)', 'AdminController::userActivity/$1', ['as' => 'admin_users_activity']);
    $routes->get('transactions', 'AdminController::transactions', ['as' => 'admin_transactions']);
    $routes->get('transactions/check-new', 'AdminController::checkNewTransactions', ['as' => 'admin_transactions_check_new']);
    $routes->get('transactions/show/(:num)', 'AdminController::transactionDetails/$1', ['as' => 'admin_transactions_show']);
    $routes->get('/test-db', 'TestController::index');
    $routes->get('transactions/unlock/(:num)', 'AdminController::unlockTransaction/$1', ['as' => 'admin_transactions_unlock']);
    $routes->post('transactions/update/(:num)', 'AdminController::updateTransactionStatus/$1', ['as' => 'admin_transactions_update']);
    $routes->get('contracts', 'AdminController::contracts', ['as' => 'admin_contracts']);
    $routes->get('contracts/updates', 'AdminController::contractsUpdates', ['as' => 'admin_contracts_updates']);
    $routes->get('contracts/row/(:num)', 'AdminController::contractRow/$1', ['as' => 'admin_contracts_row']);
    $routes->get('contracts/show/(:num)', 'AdminController::contractDetails/$1', ['as' => 'admin_contracts_show']);
$routes->post('contracts/deliver-usdt/(:num)', 'AdminController::deliverUsdt/$1', ['as' => 'admin_contracts_deliver_usdt']);
    $routes->post('contracts/change-delivery-type/(:num)', 'AdminController::changeContractDeliveryType/$1', ['as' => 'admin_contracts_change_delivery_type']);
    $routes->get('delivery', 'AdminController::deliveryQueue', ['as' => 'admin_delivery']);
    $routes->get('delivery/client/(:num)', 'AdminController::deliveryQueueClient/$1', ['as' => 'admin_delivery_client']);
    $routes->post('delivery/send/(:num)', 'AdminController::deliverUsdtBulk/$1', ['as' => 'admin_delivery_send']);
    $routes->post('delivery/block/(:num)', 'AdminController::blockDelivery/$1', ['as' => 'admin_delivery_block']);
    $routes->post('delivery/unblock/(:num)', 'AdminController::unblockDelivery/$1', ['as' => 'admin_delivery_unblock']);
    $routes->post('contracts/lock-heartbeat/(:num)', 'AdminController::lockHeartbeat/$1', ['as' => 'admin_contracts_lock_heartbeat']);
    $routes->get('suppliers', 'SuppliersController::index', ['as' => 'admin_suppliers']);
    $routes->post('suppliers/store', 'SuppliersController::store', ['as' => 'admin_suppliers_store']);
    $routes->post('suppliers/toggle/(:num)', 'SuppliersController::toggle/$1', ['as' => 'admin_suppliers_toggle']);
    $routes->get('lots', 'LotsController::index', ['as' => 'admin_lots']);
    $routes->get('lots/new', 'LotsController::create', ['as' => 'admin_lots_create']);
    $routes->post('lots/store', 'LotsController::store', ['as' => 'admin_lots_store']);
    $routes->get('lots/(:num)', 'LotsController::show/$1', ['as' => 'admin_lots_show']);
    $routes->post('lots/allocate', 'LotsController::allocate', ['as' => 'admin_lots_allocate']);
    $routes->post('lots/allocation/cancel/(:num)', 'LotsController::cancelAllocation/$1', ['as' => 'admin_lots_allocation_cancel']);
    $routes->get('lots/available', 'LotsController::availableLots', ['as' => 'admin_lots_available']);
    $routes->post('lots/quick-buy', 'LotsController::quickBuy', ['as' => 'admin_lots_quick_buy']);
    $routes->get('deposits', 'DepositsController::index', ['as' => 'admin_deposits']);
    $routes->get('deposits/check-new', 'DepositsController::checkNew', ['as' => 'admin_deposits_check_new']);
    $routes->get('deposits/show/(:num)', 'DepositsController::show/$1', ['as' => 'admin_deposits_show']);
    $routes->post('deposits/accept/(:num)', 'DepositsController::accept/$1', ['as' => 'admin_deposits_accept']);
    $routes->post('deposits/reverse/(:num)', 'DepositsController::reverse/$1', ['as' => 'admin_deposits_reverse']);
    $routes->post('deposits/reject/(:num)', 'DepositsController::reject/$1', ['as' => 'admin_deposits_reject']);
    $routes->post('deposits/reverse-rejection/(:num)', 'DepositsController::reverseRejection/$1', ['as' => 'admin_deposits_reverse_rejection']);
    $routes->get('conciliation', 'AdminController::conciliation', ['as' => 'admin_conciliation']);
    $routes->get('settings', 'AdminController::settings', ['as' => 'admin_settings']);
    $routes->post('settings/update', 'AdminController::updateSettings', ['as' => 'admin_settings_update']);
});

// Dashboard (Chat)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'ChatController::mobile', ['as' => 'dashboard']);
    $routes->get('m', 'ChatController::mobile', ['as' => 'dashboard_mobile']);
    $routes->post('chat/send', 'ChatController::send', ['as' => 'chat_send']);
    $routes->post('chat/buy', 'ChatController::createTransaction', ['as' => 'chat_buy']);
    $routes->post('chat/update-language', 'ChatController::updateLanguage', ['as' => 'update_language']);
    $routes->post('chat/update-wallet', 'ChatController::updateWallet', ['as' => 'update_wallet']);
    $routes->get('chat/history', 'ChatController::getHistory', ['as' => 'chat_history']);
    $routes->get('chat/rate', 'ChatController::getRate', ['as' => 'chat_rate']);
    $routes->get('chat/balance', 'ChatController::getBalance', ['as' => 'chat_balance']);
    $routes->get('chat/debt', 'ChatController::getDebt', ['as' => 'chat_debt']);
    $routes->get('chat/messages', 'ChatController::getChatMessages', ['as' => 'chat_messages_history']);
    $routes->post('chat/upload-proof', 'ChatController::uploadProof', ['as' => 'upload_proof']);
    $routes->get('chat/contracts', 'ChatController::getContracts', ['as' => 'chat_contracts']);
    $routes->get('chat/pending-deliveries', 'ChatController::getPendingDeliveries', ['as' => 'chat_pending_deliveries']);
    $routes->get('chat/my-debts', 'ChatController::getMyDebts', ['as' => 'chat_my_debts']);
    $routes->get('chat/statement', 'ChatController::getStatement', ['as' => 'chat_statement']);
    $routes->post('deposit/store', 'ChatController::depositStore', ['as' => 'deposit_store']);
});

// Cron URL
$routes->get('cron/record', 'Cron::record');
$routes->get('cron/populate', 'Cron::populateHistory');
$routes->get('cron/migrate', 'Cron::migrate');
$routes->get('cron/apply-interest', 'Cron::applyInterest');
