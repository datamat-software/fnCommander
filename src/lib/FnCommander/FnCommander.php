<?php

namespace Datamat\Php\Tools\FnCommander;

use Exception;
use DOMDocument;

/**
       $fncmd = FnCmd::jsonDecode( '{"ggg":x "ggg"}', true);
      

      $r = $_ENV;
     #prEx($fncmd->hasError(), $fncmd->getMessage(), $fncmd->getResult());
 */

//Function Excute
class FnCommander
{
    const NO_ERROR_DEFAULT_TEXT = "No error";
    
    private string $type;
    private int $errorCode;
    private string $errorDetails;
    private bool $operationOccurred;
    private $result;

    private function __construct(string $type, int $errorCode, string $errorDetails, bool $operationOccurred, $result = null)
    {
        $this->type = $type;
        $this->errorCode = $errorCode;
        $this->errorDetails = $errorDetails;
        $this->operationOccurred = $operationOccurred;
        $this->result = $result;
    }

    public function hasError(): bool
    {
        return $this->errorCode !== 0;
    }

    public function getMessage(): string
    {
        return sprintf("%s: %d - %s", $this->type, $this->errorCode, $this->errorDetails);
    }

    public function didOperationOccur(): bool
    {
        return $this->operationOccurred;
    }

    public function getResult()
    {
        return $this->result;
    }

    ################## functions only for checking #####################

    /**
     * Statische Methode für OpenSSL-Fehler (ohne Operation)
     */
    public static function openssl(): self
    {
        $error = openssl_error_string();
        $errorCode = $error !== false ? 1 : 0;
        $errorDetails = $error !== false ? $error : self::NO_ERROR_DEFAULT_TEXT;
        return new self('OpenSSL', $errorCode, $errorDetails, true, null);
    }

    ################## functions with error check #####################

    /**
     * JSON encode mit Fehlerprüfung
     */
    public static function jsonEncode($value, int $options = 0, int $depth = 512): self
    {
        $result = @json_encode($value, $options, $depth);
        $errorCode = json_last_error();
        $errorDetails = $errorCode === JSON_ERROR_NONE ? self::NO_ERROR_DEFAULT_TEXT : json_last_error_msg();
        $operationOccurred = $result !== false; // Encode hat stattgefunden, wenn nicht false
        return new self('JSON_ENCODE', $errorCode, $errorDetails, $operationOccurred, $result);
    }

    /**
     * JSON decode mit Fehlerprüfung
     */
    public static function jsonDecode(string $json, ?bool $associative = null, int $depth = 512, int $options = 0): self
    {
        $result = @json_decode($json, $associative, $depth, $options);
        $errorCode = json_last_error();
        $errorDetails = $errorCode === JSON_ERROR_NONE ? self::NO_ERROR_DEFAULT_TEXT : json_last_error_msg();
        // Decode hat stattgefunden, wenn kein Fehler und Rückgabe nicht null (außer bei "null")
        $operationOccurred = ($errorCode === JSON_ERROR_NONE && ($result !== null || $json === 'null'));
        return new self('JSON_DECODE', $errorCode, $errorDetails, $operationOccurred, $result);
    }

      /**
     * Statische Methode für base64_encode
     */
    public static function base64Encode(string $data): self
    {
        $result = @base64_encode($data);
        $errorCode = $result === false ? 1 : 0;
        $errorDetails = $result === false ? 'Failed to encode data to base64' : self::NO_ERROR_DEFAULT_TEXT;
        $operationOccurred = $result !== false;
        return new self('BASE64_ENCODE', $errorCode, $errorDetails, $operationOccurred, $result);
    }

    /**
     * Statische Methode für base64_decode
     */
    public static function base64Decode(string $data, bool $strict = false): self
    {
        $result = @base64_decode($data, $strict);
        $errorCode = $result === false ? 1 : 0;
        $errorDetails = $result === false ? 'Failed to decode base64 data' : self::NO_ERROR_DEFAULT_TEXT;
        $operationOccurred = $result !== false;
        return new self('BASE64_DECODE', $errorCode, $errorDetails, $operationOccurred, $result);
    }

    /**
     * Statische Methode für file_get_contents
     */
    public static function fileGetContents(string $filename, bool $useIncludePath = false, $context = null): self
    {
        $result = @file_get_contents($filename, $useIncludePath, $context);
        $errorCode = $result === false ? 1 : 0;
        $errorDetails = $result === false ? (error_get_last()['message'] ?? 'Failed to read file') : self::NO_ERROR_DEFAULT_TEXT;
        $operationOccurred = $result !== false;
        return new self('FILE_GET_CONTENTS', $errorCode, $errorDetails, $operationOccurred, $result);
    }

    /**
     * Statische Methode für file_put_contents
     */
    public static function filePutContents(string $filename, $data, int $flags = 0, $context = null): self
    {
        $result = @file_put_contents($filename, $data, $flags, $context);
        $errorCode = $result === false ? 1 : 0;
        $errorDetails = $result === false ? (error_get_last()['message'] ?? 'Failed to write file') : self::NO_ERROR_DEFAULT_TEXT;
        $operationOccurred = $result !== false;
        return new self('FILE_PUT_CONTENTS', $errorCode, $errorDetails, $operationOccurred, $result);
    }

    /**
     * Statische Methode für validateXml
     */
    public static function validateXmlSchema(string $xml, string $path_to_schema): self
    {
        $error_code = 0;
        $error_details = [];
        $operation_occurred = true;
        libxml_use_internal_errors( true ) ;
        $dom = new DOMDocument();

        if( ! $dom->loadXml( $xml ) ) 
        {
            $error_details[] = "The XML could not be loaded";
            $operation_occurred = false;
            $error_code = 1;
        }
        else if( ! $dom->schemaValidate( $path_to_schema ) ) 
        {
            $error_code = 1;
            $libxml_errors = libxml_get_errors();
            
            foreach( $libxml_errors AS $error ) 
            {
                $error_code = $error->code;
                $error_details[] = sprintf(
                    "Error in line %d, Spalte %d: %s (Code: %d)",
                    $error->line,
                    $error->column,
                    trim( $error->message ),
                    $error->code
                );
            }
            libxml_clear_errors();
        }
        else
        {
            $error_details[] = self::NO_ERROR_DEFAULT_TEXT;
        }

        $error_details_text = implode( "\n", $error_details );

        return new self( 'VALIDATE_XML_SCHEMA', $error_code, $error_details_text, $operation_occurred, null );
    }

    /**
     * Generische Methode für benutzerdefinierte Fehler
     */
    public static function custom(string $type, callable $errorCheck): self
    {
        $errorData = $errorCheck();
        [$errorCode, $errorDetails] = is_array($errorData) ? $errorData : [0, 'Unknown error'];
        return new self($type, $errorCode, $errorDetails, true);
    }

}

?>