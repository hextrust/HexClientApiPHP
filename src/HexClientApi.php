<?php
namespace hextrust;

class HexClientApi
{

    function __construct($endPoint, $apiKey, $secret) {
        $this->endPoint = $endPoint;
        $this->apiKey = $apiKey;
        $this->secret = $secret;
        $this->httpClient = new \GuzzleHttp\Client();
    }


    public function request($method, $path, $qs = null, $body = null) {
        
        $method = strtoupper($method);
        $url = $this->endPoint . $path;
        if ($method == 'GET' || $method == 'DELETE' || $method == 'PATCH') {
            $headers = HexClientApi::generateRequestHeaders($method, $this->apiKey, $this->secret, $url);
            return json_decode($this->httpClient->request(strtoupper($method), $url, ["headers" => $headers])->getBody());
        } else  if (strtolower($method) == 'post') {
            $headers = HexClientApi::generateRequestHeaders($method, $this->apiKey, $this->secret, $url ,json_encode($body), $contentType = "application/json");
            return json_decode($this->httpClient->request(strtoupper($method), $url, ["json" => $body, "headers" => $headers])->getBody());
        }
    }

    private static function generateRequestHeaders($requestMethod, $userName, $secret, $url, $data = null, $contentType = null)
    {
        $signatureHeaders = array();

        // Get Nonce
        $signatureHeaders["nonce"] = HexClientApi::getNonce();


        // Build the request-line header
        $parsedUrl = parse_url($url);
        $targetUrl = $parsedUrl["path"];
        if (!empty($parsedUrl["query"])) {
            $targetUrl = $targetUrl . "?" . $parsedUrl["query"];
        }
        
        // Get Host
        $signatureHeaders["host"] = $parsedUrl["host"];

        
        // If expect body then we need to produce body digest
        if ($requestMethod === "POST" || $requestMethod === "PATCH") {
            $base64sha256 = HexClientApi::sha256HashBase64($data);
            $signatureHeaders["digest"] = "SHA-256=" . $base64sha256;
        } 

        $requestLine = $requestMethod . " " . $targetUrl . " HTTP/1.1";

        // Set the date header
        $dateHeader = HexClientApi::createDateHeader();
        // $signatureHeaders["date"] = $dateHeader;

        // Add to headers for the signature hash
        $signatureHeaders["request-line"] = $requestLine;

        // Get the list of headers
        $headers = HexClientApi::getHeadersString($signatureHeaders);

        // Build the signature string
        $signatureString = HexClientApi::getSignatureString($signatureHeaders);

        // Hash the signature string using the specified algorithm
        $signatureHash = HexClientApi::sha512HmacBase64($signatureString, $secret);

        // Set the signature hash algorithm
        $algorithm = "hmac-sha512";

        // Format the authorization header
        $authHeaderTemplate = 'hmac username="%s", algorithm="%s", headers="%s", signature="%s"';
        $authHeader = sprintf($authHeaderTemplate, $userName, $algorithm, $headers, $signatureHash);

        // Set the request headers
        if ($requestMethod === "GET" || $requestMethod === "DELETE") {
            $requestHeaders = array(
                "nonce" => $signatureHeaders["nonce"],
                "Authorization" => $authHeader,
                "Date" => $dateHeader
            );
        } else if ($requestMethod === "POST" || $requestMethod === "PATCH") {
            $requestHeaders = array(
                "nonce" => $signatureHeaders["nonce"],
                "digest" => $signatureHeaders["digest"],
                "Authorization" => $authHeader,
                "Date" => $dateHeader
            );
        } else {
            throw new Exception('Unsupported HTTP method: ' . $requestMethod);
        }
        return $requestHeaders;
    }

    private static function createDateHeader()
    {
        return gmdate("D, d M Y H:i:s", time()) . " GMT";
    }

    private static function getHeadersString($signatureHeaders)
    {
        $headers = "";
        foreach($signatureHeaders as $key => $val)
        {
            if ($headers !== "") {
                $headers .= " ";
            }
            $headers .= $key;
        }
        return $headers;
    }

    private static function getSignatureString($signatureHeaders) { 
        $sigString = "";
        foreach($signatureHeaders as $key => $val) { 
            if ($sigString !== "") {
                $sigString .= "\n"; 
            } 
            if (mb_strtolower($key) === "request-line") { 
                $sigString .= $val; 
            } else {
                $sigString .= mb_strtolower($key) . ": " . $val; 
            } 
        } 
        return $sigString; 
    }

    private static function sha512HmacBase64($data, $key)
    {
        return base64_encode(hash_hmac('sha512', $data, $key, $raw_output=true));
    }

    private static function sha256HashBase64($data)
    {
        return base64_encode(hash('sha256', $data, $raw_output = true));
    }

    private static function getNonce() {
        return microtime(true)*10000 . "000";
    }
}