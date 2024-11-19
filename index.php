<?php

define('LOG_FILE', __DIR__ . '/app.log'); // Define o arquivo de log

/**
 * Função principal para autenticar o usuário e buscar os dados da tabela.
 *
 * @param string $username
 * @param string $password
 * @return array
 * @throws Exception
 */
function getTableData(string $username, string $password): array
{
    writeLog("Iniciando processo para obter dados da tabela para o usuário: $username");

    $curlHandle = authenticate($username, $password);

    try {
        $data = fetchTableData($curlHandle);
        writeLog("Dados da tabela obtidos com sucesso para o usuário: $username");
        return $data;
    } catch (Exception $e) {
        writeLog("Erro ao buscar os dados da tabela: " . $e->getMessage(), 'ERROR');
        throw $e;
    } finally {
        curl_close($curlHandle);
    }
}

/**
 * Realiza a autenticação no sistema e retorna o recurso cURL.
 *
 * @param string $username
 * @param string $password
 * @return resource
 * @throws Exception
 */
function authenticate(string $username, string $password)
{
    $loginUrl = "https://sistema.7vitrines.com/login";
    $cookieFilePath = __DIR__ . '/cookie.txt';

    if (file_exists($cookieFilePath)) {
        unlink($cookieFilePath);
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $loginUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $cookieFilePath,
        CURLOPT_COOKIEFILE => $cookieFilePath,
        CURLOPT_HTTPHEADER => getDefaultHeaders(),
    ]);

    $loginPage = curl_exec($ch);

    if (!$loginPage) {
        $error = 'Failed to load login page: ' . curl_error($ch);
        writeLog($error, 'ERROR');
        throw new Exception($error);
    }

    $csrfToken = extractCsrfToken($loginPage);
    if (!$csrfToken) {
        $error = 'CSRF token not found on login page';
        writeLog($error, 'ERROR');
        throw new Exception($error);
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            '_token' => $csrfToken,
            'email' => $username,
            'password' => $password,
        ]),
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    $loginResponse = curl_exec($ch);

    if (!$loginResponse) {
        $error = 'Failed to authenticate: ' . curl_error($ch);
        writeLog($error, 'ERROR');
        throw new Exception($error);
    }

    if (strpos($loginResponse, 'Acessar Tabela') === false) {
        $error = 'Authentication failed: unable to find success marker';
        writeLog($error, 'ERROR');
        throw new Exception($error);
    }

    writeLog("Autenticação realizada com sucesso para o usuário: $username");
    return $ch;
}

/**
 * Busca os dados da tabela após autenticação.
 *
 * @param resource $ch
 * @return array
 * @throws Exception
 */
function fetchTableData($ch): array
{
    $tableUrl = "https://sistema.7vitrines.com/teste/table";
    curl_setopt($ch, CURLOPT_URL, $tableUrl);
    curl_setopt($ch, CURLOPT_POST, false);

    $tablePage = curl_exec($ch);

    if (!$tablePage) {
        $error = 'Failed to fetch table data: ' . curl_error($ch);
        writeLog($error, 'ERROR');
        throw new Exception($error);
    }

    return parseTableData($tablePage);
}

/**
 * Extrai dados da tabela do HTML retornado.
 *
 * @param string $html
 * @return array
 */
function parseTableData(string $html): array
{
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $rows = $dom->getElementsByTagName('tr');

    $data = [];
    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        $rowData = [];
        foreach ($cells as $cell) {
            $rowData[] = trim($cell->textContent);
        }
        if (!empty($rowData)) {
            $data[] = $rowData;
        }
    }

    return $data;
}

/**
 * Extrai o token CSRF de uma página HTML.
 *
 * @param string $html
 * @return string|null
 */
function extractCsrfToken(string $html): ?string
{
    preg_match('/<input type="hidden" name="_token" value="([^"]+)"/', $html, $matches);
    return $matches[1] ?? null;
}

/**
 * Retorna os cabeçalhos padrão para as requisições cURL.
 *
 * @return array
 */
function getDefaultHeaders(): array
{
    return [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
    ];
}

/**
 * Retorna uma resposta JSON.
 *
 * @param array $data
 */
function jsonResponse(array $data)
{
    header('Content-Type: application/json');
    echo json_encode($data);
}

/**
 * Escreve logs no arquivo definido.
 *
 * @param string $message
 * @param string $level
 */
function writeLog(string $message, string $level = 'INFO')
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}

// Execução do script principal
try {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$username || !$password) {
        throw new Exception('Missing username or password');
    }

    $tableData = getTableData($username, $password);
    jsonResponse(['data' => $tableData]);
} catch (Exception $e) {
    writeLog("Erro: " . $e->getMessage(), 'ERROR');
    jsonResponse(['error' => $e->getMessage()]);
}
