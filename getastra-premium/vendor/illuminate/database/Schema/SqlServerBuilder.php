<?php

namespace AstraPrefixed\Illuminate\Database\Schema;

class SqlServerBuilder extends Builder
{
    /**
     * Drop all tables from the database.
     *
     * @return void
     */
    public function dropAllTables()
    {
        $this->connection->statement($this->grammar->compileDropAllForeignKeys());
        $this->connection->statement($this->grammar->compileDropAllTables());
    }
}
