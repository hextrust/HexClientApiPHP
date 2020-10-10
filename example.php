<?php 
require_once "vendor/autoload.php";

use hextrust\HexClientApi;

$endPoint = "https://api-test.hexsafe.io";
$apiKey = "<API Key....>";
$secret = "<API Secret....";

$hexApiClient = new HexClientApi($endPoint, $apiKey, $secret);

$account = $hexApiClient->request('GET', '/hexsafe/api/v4/account');
$accountId = $account->result->records[0]->account_id;
print('Accounts:\n');
print_r($account);


// Get unique deposit address
$address = $hexApiClient->request('get', '/hexsafe/api/v4/deposit/address/asset_ticker/ETH/account_id/' . $accountId . '/wallet_name/ZeroKey?unique_eth_address=true');
print('Deposit Address:\n');
print_r($address);

// Get active webhooks
$webhooks = $hexApiClient->request('GET', '/hexsafe/api/v4/webhook/' . $accountId);
print('Current Webhooks:\n');
print_r($webhooks);


// Delete webhook
$webhooks = $hexApiClient->request('DELETE', '/hexsafe/api/v4/webhook/04f8652e-6ce4-4f7c-ae47-7ad2cc884cea');
print('Webhooks delete:\n');
print_r($webhooks);


// Create deposit request and get deposit address
$body = [
    'account_id' => $accountId,
    'walletName' => 'Zerokey',
    'assetTicker' => 'ETH',
    'quantity' => '0.5',
    'note' => 'API Test',
  ];
$deposit = $hexApiClient->request('post', '/hexsafe/api/v4/deposit', $qs = null,  $body);
print('Deposit Request:\n');
print_r($deposit);

?>
