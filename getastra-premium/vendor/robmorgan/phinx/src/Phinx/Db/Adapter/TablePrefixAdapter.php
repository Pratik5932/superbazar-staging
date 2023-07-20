<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */
namespace AstraPrefixed\Phinx\Db\Adapter;

use BadMethodCallException;
use InvalidArgumentException;
use AstraPrefixed\Phinx\Db\Action\AddColumn;
use AstraPrefixed\Phinx\Db\Action\AddForeignKey;
use AstraPrefixed\Phinx\Db\Action\AddIndex;
use AstraPrefixed\Phinx\Db\Action\ChangeColumn;
use AstraPrefixed\Phinx\Db\Action\ChangeComment;
use AstraPrefixed\Phinx\Db\Action\ChangePrimaryKey;
use AstraPrefixed\Phinx\Db\Action\DropForeignKey;
use AstraPrefixed\Phinx\Db\Action\DropIndex;
use AstraPrefixed\Phinx\Db\Action\DropTable;
use AstraPrefixed\Phinx\Db\Action\RemoveColumn;
use AstraPrefixed\Phinx\Db\Action\RenameColumn;
use AstraPrefixed\Phinx\Db\Action\RenameTable;
use AstraPrefixed\Phinx\Db\Table\Column;
use AstraPrefixed\Phinx\Db\Table\ForeignKey;
use AstraPrefixed\Phinx\Db\Table\Index;
use AstraPrefixed\Phinx\Db\Table\Table;
/**
 * Table prefix/suffix adapter.
 *
 * Used for inserting a prefix or suffix into table names.
 *
 * @author Samuel Fisher <sam@sfisher.co>
 */
class TablePrefixAdapter extends AdapterWrapper implements DirectActionInterface
{
    /**
     * @inheritDoc
     */
    public function getAdapterType()
    {
        return 'TablePrefixAdapter';
    }
    /**
     * @inheritDoc
     */
    public function hasTable($tableName)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        return parent::hasTable($adapterTableName);
    }
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function createTable(Table $table, array $columns = [], array $indexes = [])
    {
        $adapterTable = new Table($this->getAdapterTableName($table->getName()), $table->getOptions());
        parent::createTable($adapterTable, $columns, $indexes);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function changePrimaryKey(Table $table, $newColumns)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTable = new Table($this->getAdapterTableName($table->getName()), $table->getOptions());
        $adapter->changePrimaryKey($adapterTable, $newColumns);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function changeComment(Table $table, $newComment)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTable = new Table($this->getAdapterTableName($table->getName()), $table->getOptions());
        $adapter->changeComment($adapterTable, $newComment);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function renameTable($tableName, $newTableName)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapterNewTableName = $this->getAdapterTableName($newTableName);
        $adapter->renameTable($adapterTableName, $adapterNewTableName);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function dropTable($tableName)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropTable($adapterTableName);
    }
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function truncateTable($tableName)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        parent::truncateTable($adapterTableName);
    }
    /**
     * @inheritDoc
     */
    public function getColumns($tableName)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        return parent::getColumns($adapterTableName);
    }
    /**
     * @inheritDoc
     */
    public function hasColumn($tableName, $columnName)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        return parent::hasColumn($adapterTableName, $columnName);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function addColumn(Table $table, Column $column)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        $adapter->addColumn($adapterTable, $column);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function renameColumn($tableName, $columnName, $newColumnName)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->renameColumn($adapterTableName, $columnName, $newColumnName);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function changeColumn($tableName, $columnName, Column $newColumn)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->changeColumn($adapterTableName, $columnName, $newColumn);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function dropColumn($tableName, $columnName)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropColumn($adapterTableName, $columnName);
    }
    /**
     * @inheritDoc
     */
    public function hasIndex($tableName, $columns)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        return parent::hasIndex($adapterTableName, $columns);
    }
    /**
     * @inheritDoc
     */
    public function hasIndexByName($tableName, $indexName)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        return parent::hasIndexByName($adapterTableName, $indexName);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function addIndex(Table $table, Index $index)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTable = new Table($table->getName(), $table->getOptions());
        $adapter->addIndex($adapterTable, $index);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function dropIndex($tableName, $columns)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropIndex($adapterTableName, $columns);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function dropIndexByName($tableName, $indexName)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropIndexByName($adapterTableName, $indexName);
    }
    /**
     * @inheritDoc
     */
    public function hasPrimaryKey($tableName, $columns, $constraint = null)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        return parent::hasPrimaryKey($adapterTableName, $columns, $constraint);
    }
    /**
     * @inheritDoc
     */
    public function hasForeignKey($tableName, $columns, $constraint = null)
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        return parent::hasForeignKey($adapterTableName, $columns, $constraint);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function addForeignKey(Table $table, ForeignKey $foreignKey)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        $adapter->addForeignKey($adapterTable, $foreignKey);
    }
    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function dropForeignKey($tableName, $columns, $constraint = null)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropForeignKey($adapterTableName, $columns, $constraint);
    }
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function insert(Table $table, $row)
    {
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        parent::insert($adapterTable, $row);
    }
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function bulkinsert(Table $table, $rows)
    {
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        parent::bulkinsert($adapterTable, $rows);
    }
    /**
     * Gets the table prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return (string) $this->getOption('table_prefix');
    }
    /**
     * Gets the table suffix.
     *
     * @return string
     */
    public function getSuffix()
    {
        return (string) $this->getOption('table_suffix');
    }
    /**
     * Applies the prefix and suffix to the table name.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function getAdapterTableName($tableName)
    {
        return $this->getPrefix() . $tableName . $this->getSuffix();
    }
    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function executeActions(Table $table, array $actions)
    {
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        foreach ($actions as $k => $action) {
            switch (\true) {
                case $action instanceof AddColumn:
                    $actions[$k] = new AddColumn($adapterTable, $action->getColumn());
                    break;
                case $action instanceof AddIndex:
                    $actions[$k] = new AddIndex($adapterTable, $action->getIndex());
                    break;
                case $action instanceof AddForeignKey:
                    $foreignKey = clone $action->getForeignKey();
                    $refTable = $foreignKey->getReferencedTable();
                    $refTableName = $this->getAdapterTableName($refTable->getName());
                    $foreignKey->setReferencedTable(new Table($refTableName, $refTable->getOptions()));
                    $actions[$k] = new AddForeignKey($adapterTable, $foreignKey);
                    break;
                case $action instanceof ChangeColumn:
                    $actions[$k] = new ChangeColumn($adapterTable, $action->getColumnName(), $action->getColumn());
                    break;
                case $action instanceof DropForeignKey:
                    $actions[$k] = new DropForeignKey($adapterTable, $action->getForeignKey());
                    break;
                case $action instanceof DropIndex:
                    $actions[$k] = new DropIndex($adapterTable, $action->getIndex());
                    break;
                case $action instanceof DropTable:
                    $actions[$k] = new DropTable($adapterTable);
                    break;
                case $action instanceof RemoveColumn:
                    $actions[$k] = new RemoveColumn($adapterTable, $action->getColumn());
                    break;
                case $action instanceof RenameColumn:
                    $actions[$k] = new RenameColumn($adapterTable, $action->getColumn(), $action->getNewName());
                    break;
                case $action instanceof RenameTable:
                    $actions[$k] = new RenameTable($adapterTable, $action->getNewName());
                    break;
                case $action instanceof ChangePrimaryKey:
                    $actions[$k] = new ChangePrimaryKey($adapterTable, $action->getNewColumns());
                    break;
                case $action instanceof ChangeComment:
                    $actions[$k] = new ChangeComment($adapterTable, $action->getNewComment());
                    break;
                default:
                    throw new InvalidArgumentException(\sprintf("Forgot to implement table prefixing for action: '%s'", \get_class($action)));
            }
        }
        parent::executeActions($adapterTable, $actions);
    }
}
