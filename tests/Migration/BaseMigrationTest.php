<?php
use SQLBuilder\Column;
use SQLBuilder\Driver\PDODriverFactory;
use LazyRecord\ConnectionManager;
use LazyRecord\Migration\Migration;

class FooMigration extends Migration
{
    public function upgrade()
    {
        $this->addColumn('foo', function($column) {
            $column->name('name')
                ->type('varchar(128)')
                ->default('(none)')
                ->notNull();
        });
    }

    public function downgrade()
    {
        $this->dropColumn('foo', 'name');
    }
}

/**
 * @group migration
 */
class MigrationTest extends PHPUnit_Framework_TestCase
{
    protected $conn;
    protected $driver;

    public function setUp()
    {
        // XXX: mysterious workaround for tests
        $connm = \LazyRecord\ConnectionManager::getInstance();
        $connm->free();
        $this->conn = new PDO('sqlite::memory:');
        $this->driver = PDODriverFactory::create($this->conn);
    }

    public function tearDown()
    {
        $this->conn = null;
    }


    public function testUpgradeWithAddColumnByCallable()
    {
        ob_start();
        $this->conn->query('CREATE TABLE foo (id INTEGER PRIMARY KEY, name varchar(32));');
        $migration = new FooMigration($this->conn, $this->driver);
        $migration->upgrade();
        $migration->downgrade();
        ob_end_clean();
    }

}

