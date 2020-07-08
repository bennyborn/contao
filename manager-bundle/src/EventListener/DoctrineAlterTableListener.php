<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\ManagerBundle\EventListener;

use Doctrine\DBAL\Event\SchemaAlterTableRenameColumnEventArgs;
use Doctrine\DBAL\Schema\TableDiff;

/**
 * @internal
 */
class DoctrineAlterTableListener
{
    /**
     * Prevents renaming arbitrary columns by explicitly dropping the old ones
     * and adding the new ones (see #1716).
     */
    public function onSchemaAlterTableRenameColumn(SchemaAlterTableRenameColumnEventArgs $args)
    {
        $args->preventDefault();

        $platform = $args->getPlatform();
        $table = $args->getTableDiff()->getName($platform);
        $oldColumn = $args->getTableDiff()->fromTable->getColumn($args->getOldColumnName());
        $column = $args->getColumn();

        $tableDiff = new TableDiff($table->getName());
        $tableDiff->removedColumns[$args->getOldColumnName()] = $oldColumn;
        $tableDiff->addedColumns[$column->getName()] = $column;

        $args->addSql($platform->getAlterTableSQL($tableDiff));
    }
}