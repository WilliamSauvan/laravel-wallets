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

#### Publish lang file

```bash
php artisan vendor:publish --provider="Webqamdev\LaravelWallets\ServiceProvider" --tag="lang"
```

## Usage

### Google Wallet

```bash
$objectSuffix = auth()->user()->id.'_'.$eventTimeslot->id;
$classSuffix = 'event';
        
$walletGenericObject = GoogleWallet::initNewObjectData($objectSuffix, $classSuffix)
            ->setObjectCardTitle('Test de cardTitle')
            ->setObjectHeader('Test de header')
            ->setObjectBarCode("Valeur du QR_Code pour l'objet {$objectSuffix}")
            ->findOrCreateObject();
            
$link = GoogleWallet::getWalletObjectButtonLink($walletGenericObject->id, $walletGenericObject->classId);

return redirect()->to($link);
```

### Apple Wallet

```bash
AppleWallet::initNewObjectData()
            ->setObjectPassType('generic')
            ->setObjectBarCode("Valeur du QR_Code pour l'objet test")
            ->addToHeaderFields('headerField', 'Header Field', 'Header Field Value')
            ->addToPrimaryFields('primaryField', 'Primary Field', 'Primary Field Value')
            ->addToSecondaryFields('secondaryField1', 'Secondary Field 1', 'Secondary Field Value 1')
            ->addToSecondaryFields('secondaryField2', 'Secondary Field 2', 'Secondary Field Value 2')
            ->downloadPass();
```
