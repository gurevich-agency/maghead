<?php

namespace Maghead\Runtime;

use PDOException;
use CLIFramework\Logger;

class PDOExceptionPrinter
{
    public static function show(Logger $logger, PDOException $e, $sqlQuery = null, array $arguments = null)
    {
        $logger->error('Exception: '.get_class($e));
        $logger->error('Error Message: '.$e->getMessage());

        if ($sqlQuery) {
            $logger->error('Query: '.$sqlQuery);
        } else {
            $logger->error('Query: Not Supplied.');
        }
        if ($arguments) {
            $logger->error('Arguments: '.var_export($arguments, true));
        }
        if ($e->errorInfo) {
            $logger->error('Error Info: '.var_export($e->errorInfo, true));
        }
        $logger->error("File: {$e->getFile()} @ {$e->getLine()}");
    }
}
