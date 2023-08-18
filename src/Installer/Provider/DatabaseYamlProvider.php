<?php

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Installer\Provider;

use Cartrulequantity;
use cdigruttola\CartRuleQuantity\Exceptions\DatabaseYamlFileNotExistsException;

class DatabaseYamlProvider
{
    /**
     * @var Cartrulequantity
     */
    protected $module;

    public function __construct(Cartrulequantity $module)
    {
        $this->module = $module;
    }

    public function getDatabaseFilePath(): string
    {
        $filePossiblePath = _PS_MODULE_DIR_ . $this->module->name . '/config/';
        $databaseFileName = 'database.yml';
        $fullFilePath = $filePossiblePath . $databaseFileName;

        if (file_exists($fullFilePath)) {
            return $fullFilePath;
        } else {
            throw new DatabaseYamlFileNotExistsException($databaseFileName . ' file not exist in ' . $filePossiblePath);
        }
    }
}
