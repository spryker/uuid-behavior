<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\UuidBehavior\Persistence\Propel\Behavior;

use Codeception\Test\Unit;
use Propel\Generator\Util\QuickBuilder as PropelQuickBuilder;
use TestUuidBehavior;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group UuidBehavior
 * @group Persistence
 * @group Propel
 * @group Behavior
 * @group UuidBehaviorTest
 * Add your own group annotations below this line
 */
class UuidBehaviorTest extends Unit
{
    protected const UUID_WITH_UNDEFINED_TIMESTAMP = '1db91ff6-2d7f-5353-9150-7d783ad572b5';
    protected const UUID_WITH_TIMESTAMP = '19016712-311a-551d-97bb-f594e583500f';

    /**
     * @return void
     */
    public function testUuidBehaviorGeneratesExpectedUuidWithUndefinedTimestamp(): void
    {
        // Arrange
        $this->buildPropelEntities();

        // Act
        $testEntity = new TestUuidBehavior();
        $testEntity->setTestStr('spryker');
        $testEntity->setTestInt(777);
        $testEntity->save();

        // Assert
        $this->assertSame($testEntity->getUuid(), static::UUID_WITH_UNDEFINED_TIMESTAMP);
    }

    /**
     * @return void
     */
    public function testUuidBehaviorGeneratesExpectedUuidWithTimestamp(): void
    {
        // Arrange
        $this->buildPropelEntities();

        // Act
        $testEntity = new TestUuidBehavior();
        $testEntity->setTestStr('spryker');
        $testEntity->setTestInt(777);
        $testEntity->setTestTimestamp(777);
        $testEntity->save();

        // Assert
        $this->assertSame($testEntity->getUuid(), static::UUID_WITH_TIMESTAMP);
    }

    /**
     * @uses TestUuidBehavior
     *
     * @return void
     */
    protected function buildPropelEntities(): void
    {
        if (class_exists('TestUuidBehavior')) {
            return;
        }

        $schema = '
            <database name="test_uuid_behavior" defaultIdMethod="native">
                <table name="test_uuid_behavior">
                    <column name="id_test" required="true" type="INTEGER" autoIncrement="true" primaryKey="true"/>
                    <column name="test_str" type="VARCHAR" required="true"/>
                    <column name="test_int" type="INTEGER" required="true"/>
                    <column name="test_timestamp" type="TIMESTAMP" required="false"/>
                    <behavior name="uuid">
                        <parameter name="key_prefix" value="test"/>
                        <parameter name="key_columns" value="test_str.test_int.test_timestamp"/>
                    </behavior>
                </table>
            </database>';

        PropelQuickBuilder::buildSchema($schema);
    }
}
