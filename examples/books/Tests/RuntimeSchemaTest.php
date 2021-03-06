<?php

namespace AuthorBooks;

use AuthorBooks\Model\BookSchema;
use AuthorBooks\Model\AuthorSchema;

use PHPUnit\Framework\TestCase;

class RuntimeSchemaTest extends TestCase
{
    public function testRuntimeSchemaConstruction()
    {
        $declare = new BookSchema;
        $this->assertNotEmpty($declare->columns, 'columns');
        $this->assertNotNull($c = $declare->columns['title']);
        $this->assertNotNull($c = $declare->columns['subtitle']);
        $this->assertNotNull($c = $declare->columns['description']);
        $this->assertEquals('AuthorBooks\Model\Book', $declare->getModelClass());
        $this->assertEquals('books', $declare->getTable());
    }
}
