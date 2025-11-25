<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Drive;
use Google\Service\Sheets\ValueRange;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Client as GuzzleClient;

class GoogleService
{
    protected $client;
    protected $sheetsService;
    protected $driveService;

    public function __construct()
    {
        $this->client = new Client();
        
        // 1. Konfigurasi Menggunakan OAuth (Bukan File JSON)
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        
        // 2. Refresh Token (Kunci agar login terus menerus)
        $this->client->refreshToken(env('GOOGLE_REFRESH_TOKEN'));

        // 3. Bypass SSL (Wajib untuk Localhost Windows)
        $guzzle = new GuzzleClient(['verify' => false]);
        $this->client->setHttpClient($guzzle);

        // 4. Load Service
        $this->sheetsService = new Sheets($this->client);
        $this->driveService = new Drive($this->client);
    }

    public function appendToSheet($spreadsheetId, $sheetName, array $data)
    {
        $range = $sheetName . '!A1';
        $body = new ValueRange(['values' => $data]);
        $params = ['valueInputOption' => 'RAW'];

        return $this->sheetsService->spreadsheets_values->append(
            $spreadsheetId, $range, $body, $params
        );
    }

    public function uploadFileToDrive($folderId, $fileName, $fileContent, $mimeType = 'application/pdf')
    {
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId] // Masuk ke folder Shared/Pribadi Anda
        ]);

        return $this->driveService->files->create(
            $fileMetadata,
            [
                'data' => $fileContent,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id',
                'supportsAllDrives' => true,
            ]
        );
    }
}