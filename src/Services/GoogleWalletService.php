<?php

namespace Webqamdev\LaravelWallets\Services;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Client as GoogleClient;
use Google\Service\Walletobjects;
use Google\Service\Walletobjects\Barcode;
use Google\Service\Walletobjects\GenericObject;
use Google\Service\Walletobjects\LocalizedString;
use Google\Service\Walletobjects\TranslatedString;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Full PHP implementation of Google Wallet API : https://github.com/google-wallet/rest-samples/blob/main/php/README.md
 * Current class inspired from https://github.com/google-wallet/rest-samples/blob/main/php/demo_generic.php
 */
class GoogleWalletService
{
    /**
     * The Google API Client
     * https://github.com/google/google-api-php-client
     */
    private GoogleClient $googleClient;

    /**
     * Path to service account key file from Google Cloud Console. Environment
     * variable: GOOGLE_WALLET_APPLICATION_CREDENTIALS
     */
    private string $keyFilePath;

    /**
     * Content of the $keyFilePath file.
     */
    private array $serviceAccount;

    /**
     * Issuer ID for Google Wallet APIs. Environment variable: GOOGLE_WALLET_ISSUER_ID
     * Used to identify the issuer of the object and prefix class IDs like this : {$issuerId}.{class_suffix}
     */
    private string $issuerId;

    protected ?array $newWalletObject = null;

    /**
     * Service account credentials for Google Wallet APIs.
     */
    private ServiceAccountCredentials $credentials;

    /**
     * Google Wallet service client.
     */
    private Walletobjects $service;

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->keyFilePath = $this->config['google_wallet']['auth_file_path'];
        $this->serviceAccount = json_decode(file_get_contents($this->keyFilePath), true);

        $this->issuerId = $this->config['google_wallet']['issuer_id'];

        $this->auth();
    }

    public function findObject(string $objectSuffix): ?GenericObject
    {
        try {
            return $this->service->genericobject->get("{$this->issuerId}.{$objectSuffix}");
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() !== Response::HTTP_NOT_FOUND) {
                Log::error($e->getMessage());
            }
        }

        return null;
    }

    public function createObject(string $objectTypeClass = GenericObject::class): ?GenericObject
    {
        try {
            $createdObject = $this->service->genericobject->insert(new $objectTypeClass($this->newWalletObject));

            return get_class($createdObject) === $objectTypeClass ? $createdObject : null;
        } catch (\Google\Service\Exception $e) {
            Log::error($e->getMessage());
        }

        return null;
    }

    public function findOrCreateObject(string $objectTypeClass = GenericObject::class): ?GenericObject
    {
        if (! empty($this->newWalletObject['id'])) {
            $foundObject = $this->findObject(explode('.', $this->newWalletObject['id'])[1] ?? '');

            if ($foundObject !== null) {
                return $foundObject;
            }
        }

        return $this->createObject($objectTypeClass);
    }

    public function initNewObjectData(
        string $objectSuffix,
        string $classSuffix,
        string $state = 'ACTIVE'
    ): self {
        $this->newWalletObject = [
            'id' => "{$this->issuerId}.{$objectSuffix}",
            'classId' => "{$this->issuerId}.{$classSuffix}",
            'state' => $state,
        ];

        return $this;
    }

    /**
     * Available keys : https://developers.google.com/wallet/reference/rest/v1/genericobject?hl=fr
     */
    public function setObjectDataKey(string $key, mixed $value): self
    {
        $this->newWalletObject[$key] = $value;

        return $this;
    }

    public function setObjectLocalizedString(string $key, string $value, string $language = 'en-EN'): self
    {
        return $this->setObjectDataKey(
            $key,
            new LocalizedString([
                'defaultValue' => new TranslatedString([
                    'language' => $language,
                    'value' => $value,
                ]),
            ])
        );
    }

    public function setObjectCardTitle(string $value, string $language = 'en-EN'): self
    {
        return $this->setObjectLocalizedString('cardTitle', $value, $language);
    }

    public function setObjectHeader(string $value, string $language = 'en-EN'): self
    {
        return $this->setObjectLocalizedString('header', $value, $language);
    }

    public function setObjectBarCode(string $qrCodeValue, string $type = 'QR_CODE'): self
    {
        return $this->setObjectDataKey('barcode', new Barcode([
            'type' => $type,
            'value' => $qrCodeValue,
        ]));
    }

    public function getWalletObjectButtonLink(
        string $objectId,
        string $classId,
        string $objectType = 'genericObjects'
    ): ?string {

        $customClaims = [
            'payload' => [
                $objectType => [
                    [
                        'id' => $objectId,
                        'classId' => $classId,
                    ],
                ],
            ],
        ];

        $token = $this->getJwtToken($this->getDefaultClaims($customClaims));

        return $this->getButtonLinkByToken($token);
    }

    private function getDefaultClaims(array $additionalClaims = [], string $typ = 'savetowallet'): array
    {
        return array_merge(
            [
                'iss' => $this->serviceAccount['client_email'],
                'aud' => 'google',
                'origins' => [config('app.url')],
                'typ' => $typ,
            ],
            $additionalClaims
        );
    }

    private function getJwtToken(?array $claims = null): string
    {
        if ($claims === null) {
            $claims = $this->getDefaultClaims();
        }

        return JWT::encode(
            $claims,
            $this->serviceAccount['private_key'],
            'RS256'
        );
    }

    private function getButtonLinkByToken(?string $token = null): ?string
    {
        if ($token === null) {
            return null;
        }

        return "https://pay.google.com/gp/v/save/{$token}";
    }

    private function auth(): void
    {
        $this->credentials = new ServiceAccountCredentials(
            Walletobjects::WALLET_OBJECT_ISSUER,
            $this->keyFilePath
        );

        // Initialize Google Wallet API service
        $this->googleClient = new GoogleClient();
        $this->googleClient->setApplicationName(config('app.name'));
        $this->googleClient->setScopes(Walletobjects::WALLET_OBJECT_ISSUER);
        $this->googleClient->setAuthConfig($this->keyFilePath);

        $this->service = new Walletobjects($this->googleClient);
    }
}
