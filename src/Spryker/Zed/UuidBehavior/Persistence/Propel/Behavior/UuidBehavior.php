<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior;

use Propel\Generator\Model\Behavior;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Unique;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\ColumnNotFoundException;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\InvalidParameterValueException;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\MissingAttributeException;
use Zend\Filter\Word\UnderscoreToCamelCase;

class UuidBehavior extends Behavior
{
    protected const KEY_COLUMN_NAME = 'uuid';
    protected const KEY_COLUMN_UNIQUE_INDEX_POSTFIX = '-unique-uuid';

    protected const ERROR_MISSING_KEY_PREFIX_PARAMETER = '%s misses "key_prefix" parameter';
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
    public function preSave(): string
    {
        return '$this->setGeneratedUuid();';
    }

    /**
     * @return string
     */
    public function objectMethods(): string
    {
        $script = '';
        $script .= $this->addGetUuidGeneratorServiceMethod();
        $script .= $this->addSetGeneratedUuidMethod();

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
            $uniqueIndex->addColumn($column);
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
     * @throws \Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\MissingAttributeException
     *
     * @return string
     */
    protected function addSetGeneratedUuidMethod(): string
    {
        $parameters = $this->getParameters();
        if (!isset($parameters['key_prefix'])) {
            throw new MissingAttributeException(
                sprintf(static::ERROR_MISSING_KEY_PREFIX_PARAMETER, $this->getTable()->getPhpName())
            );
        }

        return $this->renderTemplate('objectSetGeneratedUuid', [
            'keyStatement' => $this->prepareKeyStatement($parameters['key_prefix']),
        ]);
    }

    /**
     * @param string $prefix
     *
     * @throws \Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\Exception\ColumnNotFoundException
     *
     * @return string
     */
    protected function prepareKeyStatement(string $prefix): string
    {
        $keyStatement = '\'' . $prefix . '\'';
        $columns = $this->getKeyColumnNames();

        $filter = new UnderscoreToCamelCase();
        foreach ($columns as $column) {
            if (!$this->getTable()->hasColumn($column)) {
                throw new ColumnNotFoundException(sprintf(
                    static::ERROR_COLUMN_NOT_FOUND,
                    $column
                ));
            }
            $getter = sprintf('get%s()', $filter->filter($column));
            $keyStatement .= " . '.' . \$this->{$getter}";
        }

        return $keyStatement;
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
}
