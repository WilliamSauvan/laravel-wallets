<?php

namespace Webqamdev\LaravelWallets\Services;

use Illuminate\Support\Facades\Log;
use PKPass\PKPass;

/**
 * Use the package https://github.com/includable/php-pkpass
 * Inspired from https://github.com/includable/php-pkpass/blob/master/examples/example.php
 */
class AppleWalletService
{
    public const PASS_FIELDSET_HEADER = 'headerFields';

    public const PASS_FIELDSET_PRIMARY = 'primaryFields';

    public const PASS_FIELDSET_SECONDARY = 'secondaryFields';

    public const PASS_FIELDSET_AUXILIARY = 'auxiliaryFields';

    public const PASS_FIELDSET_BACK = 'backFields';

    public const APPLE_IMAGES_FOLDER = 'images/wallets/apple-assets/pass/';

    private const IMAGE_LIST = [
        'background.png',
        'background@2x.png',
        'background@3x.png',
        'footer.png',
        'footer@2x.png',
        'footer@3x.png',
        'icon.png',
        'icon@2x.png',
        'icon@3x.png',
        'logo.png',
        'logo@2x.png',
        'logo@3x.png',
        'strip.png',
        'strip@2x.png',
        'strip@3x.png',
        'thumbnail.png',
        'thumbnail@2x.png',
        'thumbnail@3x.png',
    ];

    private PKPass $pkPass;

    protected ?array $newWalletObject = null;

    protected ?string $newWalletPassType = 'generic';

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initNewPkPass();
    }

    public function initNewPkPass(): void
    {
        $this->pkPass = new PKPass(
            $this->config['apple_wallet']['certificates_file_path'],
            $this->config['apple_wallet']['certificates_password']
        );
    }

    public function initNewObjectData(array $config = []): self
    {
        $defaultConfig = [
            'passTypeIdentifier' => $this->config['apple_wallet']['pass_identifier'],
            'teamIdentifier' => $this->config['apple_wallet']['team_identifier'],
            'logoText' => '',
            'description' => config('app.name'),
            'formatVersion' => 1,
            'organizationName' => '',
            'serialNumber' => '12345678',
            'foregroundColor' => 'rgb(0, 0, 0)',
            'backgroundColor' => 'rgb(255, 255, 255)',
            'relevantDate' => date('Y-m-d\TH:i:sP'),
        ];

        $this->newWalletObject = array_merge($defaultConfig, $config);

        try {
            foreach (self::IMAGE_LIST as $image) {
                $this->pkPass->addFile(public_path(self::APPLE_IMAGES_FOLDER.$image));
            }
        } catch (\PKPass\PKPassException $e) {
            Log::error($e->getMessage());
        }

        return $this;
    }

    public function setObjectDataKey(string $key, mixed $value): self
    {
        $this->newWalletObject[$key] = $value;

        return $this;
    }

    public function setObjectBarCode(
        string $qrCodeValue,
        string $format = 'PKBarcodeFormatQR',
        string $messageEncoding = 'iso-8859-1'
    ): self {
        return $this->setObjectDataKey('barcode', [
            'format' => $format,
            'message' => $qrCodeValue,
            'messageEncoding' => $messageEncoding,
        ]);
    }

    public function setObjectPassType(
        string $passType = 'generic',
    ): self {
        $this->newWalletPassType = $passType;

        $defaultValue = [];

        if ($this->newWalletPassType === 'generic') {
            $defaultValue = [
                self::PASS_FIELDSET_HEADER => [],
                self::PASS_FIELDSET_PRIMARY => [],
                self::PASS_FIELDSET_SECONDARY => [],
                self::PASS_FIELDSET_AUXILIARY => [],
                self::PASS_FIELDSET_BACK => [],
            ];
        }

        return $this->setObjectDataKey($passType, $defaultValue);
    }

    public function addToPassFieldset(string $fieldsetName, array $value): self
    {
        if (empty($this->newWalletObject[$this->newWalletPassType])) {
            $this->setObjectDataKey($this->newWalletPassType, []);
        }

        if (empty($this->newWalletObject[$this->newWalletPassType][$fieldsetName])) {
            $this->newWalletObject[$this->newWalletPassType][$fieldsetName] = [];
        }

        $this->newWalletObject[$this->newWalletPassType][$fieldsetName][] = $value;

        return $this;
    }

    public function addToHeaderFields(string $key, string $label, string $value, array $additionalValues = []): self
    {
        return $this->addToPassFieldset(self::PASS_FIELDSET_HEADER, [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            ...$additionalValues,
        ]);
    }

    public function addToPrimaryFields(string $key, string $label, string $value, array $additionalValues = []): self
    {
        return $this->addToPassFieldset(self::PASS_FIELDSET_PRIMARY, [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            ...$additionalValues,
        ]);
    }

    public function addToSecondaryFields(string $key, string $label, string $value, array $additionalValues = []): self
    {
        return $this->addToPassFieldset(self::PASS_FIELDSET_SECONDARY, [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            ...$additionalValues,
        ]);
    }

    public function addToAuxiliaryFields(string $key, string $label, string $value, array $additionalValues = []): self
    {
        return $this->addToPassFieldset(self::PASS_FIELDSET_AUXILIARY, [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            ...$additionalValues,
        ]);
    }

    public function addToBackFields(string $key, string $label, string $value, array $additionalValues = []): self
    {
        return $this->addToPassFieldset(self::PASS_FIELDSET_BACK, [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            ...$additionalValues,
        ]);
    }

    public function downloadPass(): ?string
    {
        try {
            $this->pkPass->setData($this->newWalletObject);

            return $this->pkPass->create(true);
        } catch (\PKPass\PKPassException $e) {
            Log::error($e->getMessage());
        }

        return null;
    }
}
