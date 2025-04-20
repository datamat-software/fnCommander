[![Build Status](https://travis-ci.com/[username]/[repo].svg?branch=main)](https://travis-ci.com/[username]/[repo])
[![Version](https://img.shields.io/packagist/v/[vendor]/[library-name].svg)](https://packagist.org/packages/[vendor]/[library-name])


# MeineFirma SEPA-XML

Eine PHP-Bibliothek zur Erstellung und Validierung von SEPA-XML-Dateien nach dem ISO 20022-Standard (z. B. ```pain.001.001.09``` für Überweisungen).

## Inhaltsverzeichnis

- [Installation](#installation)
- [Verwendung](#verwendung)
- [Konfiguration](#konfiguration)
- [Beispiele](#beispiele)
- [Abhängigkeiten](#abhängigkeiten)
- [Lizenz](#lizenz)
- [Beitrag](#beitrag)
- [Kontakt](#kontakt)

## Installation

Falls die Library über Composer auf Packagist verfügbar wäre:

```bash
composer require meinefirma/sepa-xml
```

### Installation mit Pfad-Repository

Da die Library lokal entwickelt wird, kannst du sie als Pfad-Repository einbinden. Füge dies zu deiner ```composer.json``` hinzu:

```json
{
  "require": {
    "datamat/function-commander": "0.0.1"
  },
  "repositories": [
    {
      "type": "path",
      "url": "C:/Users/alexa/Documents/datamat_dev/Libs/fnCommander",
      "options": {
        "symlink": false
      }
    }
  ]
}
```

Führe dann im Hauptprojektverzeichnis aus:

```bash
composer update
```

- ```url```: Pfad zur lokalen Bibliothek (relativ oder absolut).
- ```symlink: false```: Stellt sicher, dass die Dateien kopiert werden, was für SFTP-Uploads wichtig ist.

Die Bibliothek wird in ```vendor/meinefirma/sepa-xml``` als Kopie installiert.

## Verwendung

Die Bibliothek bietet Funktionen zur Erstellung und Validierung von SEPA-XML-Dateien. Grundlegende Nutzung:

```php
require 'vendor/autoload.php';

use MeineFirma\SepaXml\SepaGenerator;

$generator = new SepaGenerator();
$xml = $generator->createTransfer('DE89370400440532013000', 'PAY-20250410-001');
file_put_contents('sepa.xml', $xml);
```

## Konfiguration

Die Library unterstützt optionale Konfigurationen, z. B. für Zahlungstypen oder Purpose Codes:

- **Parameter**:
  - ```serviceLevel```: SEPA-Service-Level (Standard: ```SEPA```).
  - ```localInstrument```: Lokales Zahlungsinstrument (z. B. ```INST``` für Echtzeitüberweisung, Standard: leer).
  - ```purposeCode```: Zweckcode (z. B. ```SALA``` für Gehalt, Standard: leer).

- **Beispiel mit Konfiguration**:

```php
$generator = new SepaGenerator([
    'serviceLevel' => 'SEPA',
    'localInstrument' => 'INST',
    'purposeCode' => 'SALA'
]);
```

## Beispiele

### Beispiel 1: Einfache SEPA-Überweisung erstellen

Erstellt ein ```pain.001.001.09```-XML mit einer Überweisung:

```php
require 'vendor/autoload.php';

use MeineFirma\SepaXml\SepaGenerator;

$generator = new SepaGenerator();
$xml = $generator->createTransfer(
    'DE89370400440532013000', // Debitor IBAN
    'PAY-20250410-001',        // Payment Info ID
    [
        [
            'amount' => 1000.50,
            'creditorIban' => 'DE75512108000234567890',
            'creditorName' => 'Max Mustermann',
            'creditorBic' => 'GENODEF1S04',
            'endToEndId' => 'RECHNUNG-001',
            'remittanceInfo' => 'Rechnung 001'
        ]
    ]
);
file_put_contents('sepa.xml', $xml);
```

### Beispiel 2: Überweisung mit Purpose Code und Echtzeit

Erstellt eine Echtzeitüberweisung mit Purpose Code:

```php
require 'vendor/autoload.php';

use MeineFirma\SepaXml\SepaGenerator;

$generator = new SepaGenerator([
    'localInstrument' => 'INST', // Echtzeitüberweisung
    'purposeCode' => 'SALA'      // Gehalt
]);
$xml = $generator->createTransfer(
    'DE89370400440532013000',
    'PAY-20250410-002',
    [
        [
            'amount' => 500.25,
            'creditorIban' => 'DE90500105175432198765',
            'creditorName' => 'Erika Musterfrau',
            'creditorBic' => 'COBADEFFXXX',
            'endToEndId' => 'GEHALT-001',
            'remittanceInfo' => 'Gehalt April 2025'
        ]
    ]
);
file_put_contents('sepa_instant.xml', $xml);
```

### Beispiel 3: XML-Validierung

Validiert ein existierendes SEPA-XML gegen das Schema:

```php
require 'vendor/autoload.php';

use MeineFirma\SepaXml\SepaValidator;

$validator = new SepaValidator();
$errors = $validator->validate('sepa.xml', 'pain.001.001.09.xsd');

if (empty($errors)) {
    echo "XML ist gültig.\n";
} else {
    echo "Validierungsfehler:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
```

## Abhängigkeiten

- PHP >= 7.4
- ```ext-dom``` (für XML-Verarbeitung)
- ```ext-libxml``` (für Schema-Validierung)

## Lizenz

BSD 2-Clause License - Siehe [LICENSE](LICENSE).

## Beitrag

Beiträge sind willkommen! Bitte folge diesen Schritten:

1. Forke das Repository.
2. Erstelle einen Branch (```git checkout -b feature/meine-änderung```).
3. Commit deine Änderungen (```git commit -m "Beschreibung der Änderung"```).
4. Push den Branch (```git push origin feature/meine-änderung```).
5. Erstelle einen Pull Request.

## Kontakt

- **Autor**: Max Mustermann
- **E-Mail**: max.mustermann@example.com
- **Issues**: [GitHub Issues](https://github.com/meinefirma/sepa-xml/issues)