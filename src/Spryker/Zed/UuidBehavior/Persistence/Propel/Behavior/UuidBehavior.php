<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior;

use Propel\Generator\Model\Behavior;
use Propel\Generator\Model\Column;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Unique;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\ColumnNotFoundException;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\InvalidParameterValueException;
use Zend\Filter\Word\UnderscoreToCamelCase;

class UuidBehavior extends Behavior
{
    protected const KEY_COLUMN_NAME = 'uuid';
    protected const KEY_COLUMN_UNIQUE_INDEX_POSTFIX = '-unique-uuid';
    protected const DATETIME_FORMAT = "'U u'";

    protected const ERROR_INVALID_KEY_COLUMNS_FORMAT = 'Invalid data passed to %s as "key_columns" parameter';
    protected const ERROR_COLUMN_NOT_FOUND = 'Column %s that is specified for generating UUID is not exist.';

    /**
     * @var array
     */
    protected $parameters = [
        'key_prefix' => null,
        'key_columns' => '',
    ];

    /**
     * @return string
     */
    public function objectAttributes(): string
    {
        $script = '';
        $script .= $this->addBaseAttribute();

        return $script;
    }

    /**
     * @return string
     */
    public function preUpdate(): string
    {
        return '$this->updateUuidBeforeUpdate();';
    }

    /**
     * @return string
     */
    public function postInsert(): string
    {
        return '$this->updateUuidAfterInsert($con);';
    }

    /**
     * @return string
     */
    public function objectMethods(): string
    {
        $script = '';
        $script .= $this->addGetUuidGeneratorServiceMethod();
        $script .= $this->addSetGeneratedUuidMethod();
        $script .= $this->addUpdateUuidAfterInsertMethod();
        $script .= $this->addUpdateUuidBeforeUpdateMethod();

        return $script;
    }

    /**
     * @return void
     */
    public function modifyTable(): void
    {
        $table = $this->getTable();

        if (!$table->hasColumn(static::KEY_COLUMN_NAME)) {
            $column = $table->addColumn([
                'name' => static::KEY_COLUMN_NAME,
                'type' => PropelTypes::VARCHAR,
            ]);

            $uniqueIndex = new Unique();
            $uniqueIndex->setName($table->getName() . static::KEY_COLUMN_UNIQUE_INDEX_POSTFIX);
            $uniqueIndex->addColumn(new Column(static::KEY_COLUMN_NAME));
            $table->addUnique($uniqueIndex);
        }
    }

    /**
     * @return string
     */
    public function addBaseAttribute(): string
    {
        return $this->renderTemplate('objectBaseAttribute');
    }

    /**
     * @return string
     */
    protected function addGetUuidGeneratorServiceMethod(): string
    {
        return $this->renderTemplate('objectGetUuidGeneratorService');
    }

    /**
     * @return string
     */
    protected function addSetGeneratedUuidMethod(): string
    {
        $parameters = $this->getParameters();
        if (!isset($parameters['key_prefix'])) {
            $parameters['key_prefix'] = $this->table->getCommonName();
        }

        return $this->renderTemplate('objectSetGeneratedUuid', [
            'keyStatement' => $this->prepareKeyStatement($parameters['key_prefix']),
        ]);
    }

    /**
     * @return string
     */
    protected function addUpdateUuidAfterInsertMethod(): string
    {
        return $this->renderTemplate('objectUpdateUuidAfterInsert');
    }

    /**
     * @return string
     */
    protected function addUpdateUuidBeforeUpdateMethod(): string
    {
        return $this->renderTemplate('objectUpdateUuidBeforeUpdate');
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    protected function prepareKeyStatement(string $prefix): string
    {
        $columns = $this->getKeyColumnNames();

        $keyStatements = [sprintf("'%s'", $prefix)];
        foreach ($columns as $column) {
            $keyStatements[] = $this->buildKeyStatement($column);
        }

        return implode(" . '.' . ", $keyStatements);
    }

    /**
     * @throws \Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\InvalidParameterValueException
     *
     * @return array
     */
    protected function getKeyColumnNames(): array
    {
        $columns = $this->getParameters()['key_columns'] ?? '';

        if ($columns) {
            $columns = explode('.', $columns);
            if (!is_array($columns)) {
                throw new InvalidParameterValueException(
                    sprintf(static::ERROR_INVALID_KEY_COLUMNS_FORMAT, $this->getTable()->getPhpName())
                );
            }

            return $columns;
        }

        return [];
    }

    /**
     * @param string $column
     *
     * @throws \Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\ColumnNotFoundException
     *
     * @return string
     */
    protected function buildKeyStatement(string $column): string
    {
        if (!$this->getTable()->hasColumn($column)) {
            throw new ColumnNotFoundException(sprintf(
                static::ERROR_COLUMN_NOT_FOUND,
                $column
            ));
        }

        $filter = new UnderscoreToCamelCase();
        if ($this->getTable()->getColumn($column)->getType() === 'TIMESTAMP') {
            return sprintf('$this->get%1$s(%2$s)', $filter->filter($column), static::DATETIME_FORMAT);
        }

        return sprintf('$this->get%s()', $filter->filter($column));
    }
}
