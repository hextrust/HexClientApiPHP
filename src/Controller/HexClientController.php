<?php
namespace Src\Controller;
use hextrust\HexClientApi;

class HexClientController {

    private $requestMethod;
    private $hexApiClient;
    private $hexClientRequestMethod;
    private $hexClientPath;
    private $hexClientRequestBody;

    public function __construct($requestMethod, $hexClientRequestBody)
    {
        $requestBody = json_decode($hexClientRequestBody, true);
        $this->requestMethod = $requestMethod;
        $this->hexApiClient = new HexClientApi( $_ENV['END_POINT_URL_HEX_SAFE'],  $_ENV['HEX_SAFE_API_KEY'],  $_ENV['HEX_SAFE_SECRET']);
        $this->hexClientRequestMethod = isset($requestBody["method"]) ? $requestBody["method"] : "POST";
        $this->hexClientPath = isset($requestBody["path"]) ? $requestBody["path"] : "";
        $this->hexClientRequestBody = $requestBody;
    }

    public function processRequest()
    {
        $response = $this->createDeposit();
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAccountId()
    {
        $account = $this->hexApiClient->request($this->hexClientRequestMethod, $this->hexClientPath);
        $result = [
            "accountId"=> $account
        ];
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($account);
        return $response;
        $accountId = $account->result->records[0]->account_id;
        $result = [
            "accountId"=> $account
        ];
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($account);
        return $response;
    }

    private function getAddressByAssetType()
    {
        $requestBody = $this->hexClientRequestBody;
        $asset_type_ticker = $requestBody['assetType'];
        $accountId = $requestBody['accountId'];
        $walletType = $requestBody['walletType'];
        $path = sprintf($this->hexClientPath, $asset_type_ticker, $accountId, $walletType);
        $address = $this->hexApiClient->request($this->hexClientRequestMethod, $path);
        $result = [
            "result"=> $address
        ];
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createDeposit()
    {
        $requestBody = $this->hexClientRequestBody;
        $body = [
            'account_id' => $requestBody['account_id'],
            'walletName' => $requestBody['walletName'],
            'assetTicker' => $requestBody['assetTicker'],
            'quantity' => $requestBody['quantity'],
            'note' => $requestBody['note'],
          ];
        $address = $this->hexApiClient->request($this->hexClientRequestMethod, $this->hexClientPath, $qs = null,  $body);
        $result = [
            "result"=> $address
        ];
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}