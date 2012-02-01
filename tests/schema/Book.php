<?php

class BookSchema extends LazyRecord\SchemaDeclare
{

    function schema()
    {
        $this->column('title')
                ->varchar(256)
                ->required()        /* will call requried validator, when create or update */
                ->isa('string');

        $this->column('subtitle')
                ->varchar(512)
                ->default(' ')
                ->isa('string');

        $this->column('isbn')
                ->varchar(128)
                ->isa('string')
                ->default('---')
                ->validate('IsbnValidator');

        $this->column('published_on')
                ->timestamp()
                ->isa('DateTime');

        $this->column('created_on')
                ->timestamp()
                ->isa('DateTime');
    }

}



