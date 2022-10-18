<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior;

use Laminas\Filter\Word\UnderscoreToCamelCase;
use LogicException;
use Propel\Generator\Model\Behavior;
use Propel\Generator\Model\Column;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Table;
use Propel\Generator\Model\Unique;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\ColumnNotFoundException;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\InvalidParameterValueException;

class UuidBehavior extends Behavior
{
    /**
     * @var string
     */
    protected const KEY_COLUMN_NAME = 'uuid';

    /**
     * @var string
     */
    protected const KEY_COLUMN_UNIQUE_INDEX_POSTFIX = '-unique-uuid';

    /**
     * @var string
     */
    protected const DATETIME_FORMAT = "'U u'";

    /**
     * @var string
     */
    protected const ERROR_INVALID_KEY_COLUMNS_FORMAT = 'Invalid data passed to %s as "key_columns" parameter';

    /**
     * @var string
     */
    protected const ERROR_COLUMN_NOT_FOUND = 'Column %s that is specified for generating UUID is not exist.';

    /**
     * @var array<string, mixed>
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
        $table = $this->getTableOrFail();

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
     * @return array<string>
     */
    protected function getKeyColumnNames(): array
    {
        $columns = $this->getParameters()['key_columns'] ?? '';

        if ($columns) {
            $columns = explode('.', $columns);
            if (!is_array($columns)) {
                throw new InvalidParameterValueException(
                    sprintf(static::ERROR_INVALID_KEY_COLUMNS_FORMAT, $this->getTableOrFail()->getPhpName()),
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
        if (!$this->getTableOrFail()->hasColumn($column)) {
            throw new ColumnNotFoundException(sprintf(
                static::ERROR_COLUMN_NOT_FOUND,
                $column,
            ));
        }

        $filter = new UnderscoreToCamelCase();
        /** @var string $value */
        $value = $filter->filter($column);
        if ($this->getTableOrFail()->getColumn($column)->getType() === 'TIMESTAMP') {
            return sprintf('$this->get%1$s(%2$s)', $value, static::DATETIME_FORMAT);
        }

        return sprintf('$this->get%s()', $value);
    }

    /**
     * Returns the table this behavior is applied to
     *
     * @throws \LogicException
     *
     * @return \Propel\Generator\Model\Table
     */
    public function getTableOrFail(): Table
    {
        $table = $this->getTable();

        if ($table === null) {
            throw new LogicException('Table is not defined.');
        }

        return $table;
    }
}
