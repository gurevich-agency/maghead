<?php

use PHPUnit\Framework\TestSuite;

abstract class AbstractDatabaseTestSuite extends TestSuite
{
    static $crossPlatformTests = [
        'AuthorBooks\\Tests\\AuthorTest',
        'AuthorBooks\\Tests\\AuthorAddressTest',
        'AuthorBooks\\Tests\\BookTest',
        'AuthorBooks\\Tests\\AuthorBookTest',
        'AuthorBooks\\Tests\\AuthorCollectionTest',
        'PageApp\\Tests\\PageTest',
    ];


    public static function registerTests(TestSuite $suite)
    {
        foreach (static::$crossPlatformTests as $testCase) {
            if (!class_exists($testCase, true)) {
                throw new Exception("$testCase doesn't exist.");
            }
            $suite->addTestSuite($testCase);
        }
    }

    public function setTestingDriverType($type)
    {
        foreach ($this->tests() as $ts) {
            foreach ($ts->tests() as $tc) {
                if (method_exists($tc, 'setCurrentDriverType')) {
                    $tc->setCurrentDriverType($type);
                }
            }
        }
    }

}

class PgsqlSuiteTest extends AbstractDatabaseTestSuite
{
    /**
     * @requires extension pgsql
     */
    public static function suite()
    {
        $suite = new self;
        $suite->registerTests($suite);
        $suite->setTestingDriverType('pgsql');
        return $suite;
    }
}

class MysqlSuiteTest extends AbstractDatabaseTestSuite
{
    /**
     * @requires extension mysql
     */
    public static function suite()
    {
        $suite = new self;
        $suite->registerTests($suite);
        $suite->setTestingDriverType('mysql');
        return $suite;
    }
}

class SqliteSuiteTest extends AbstractDatabaseTestSuite
{
    /**
     * @requires extension sqlite
     */
    public static function suite()
    {
        $suite = new self;
        $suite->registerTests($suite);
        $suite->setTestingDriverType('sqlite');
        return $suite;
    }
}