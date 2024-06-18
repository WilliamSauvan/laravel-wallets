# Laravel Wallets

## Overview

The **Laravel Wallets Service Package** is a comprehensive library for managing Apple Wallet and Google Wallet passes. This package provides an easy way to generate generic cards for both platforms, simplifying the integration process for developers.

## Features

- Create and manage Apple Wallet generic passes
- Create and manage Google Wallet generic passes

## Installation

### Using Composer (for PHP)

```bash
composer require webqamdev/laravel-wallets
```

### Publish files

#### Publish images file

```bash
php artisan vendor:publish --provider="Webqamdev\LaravelWallets\ServiceProvider" --tag="images"
```

This allows you to override them in your project.

#### Publish lang file

```bash
php artisan vendor:publish --provider="Webqamdev\LaravelWallets\ServiceProvider" --tag="lang"
```

## Usage

### Google Wallet

Follow step 1 to 4 in the tutorial to create a Google Wallet Object: https://developers.google.com/wallet/generic/web/prerequisites?hl=fr
The step 5 is handled by this plugin for generic passes

Generate the wallet
```bash
$objectSuffix = 'my_custom_prefix'; // can be anything you want, this will be used to cache the pass
$classSuffix = 'event'; // should be whatever you defined in your google pay console
        
$walletGenericObject = GoogleWallet::initNewObjectData($objectSuffix, $classSuffix)
            ->setObjectCardTitle('Card title', 'fr_FR')
            ->setObjectHeader('Header title', 'fr_FR')
            ->setObjectBarCode('QR code value')
            ->findOrCreateObject();
```

Redirect the user to the corresponding Google link
```bash     
$link = GoogleWallet::getWalletObjectButtonLink($walletGenericObject->id, $walletGenericObject->classId);

return redirect()->to($link);
```

### Apple Wallet

For apple, the pass is directly downloaded
```bash
AppleWallet::initNewObjectData()
            ->setObjectPassType('generic')
            ->setObjectBarCode('QR code value')
            ->addToHeaderFields('headerField', 'Header Field', 'Header Field Value')
            ->addToPrimaryFields('primaryField', 'Primary Field', 'Primary Field Value')
            ->addToSecondaryFields('secondaryField1', 'Secondary Field 1', 'Secondary Field Value 1')
            ->addToSecondaryFields('secondaryField2', 'Secondary Field 2', 'Secondary Field Value 2')
            ->downloadPass();
```
