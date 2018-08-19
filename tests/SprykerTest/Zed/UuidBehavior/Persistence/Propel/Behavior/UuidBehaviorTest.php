<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\UuidBehavior\Persistence\Propel\Behavior;

use Codeception\Test\Unit;
use Propel\Generator\Util\QuickBuilder as PropelQuickBuilder;
use TestMain;

/**
 * Auto-generated group annotations
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
    protected const UUID_GENERATED_VALUE_EXPECTED = '08fd3456-4886-5b38-b649-5778ab4ca78d';

    /**
     * @return void
     */
    public function setUp(): void
    {
        if (!class_exists('UuidTest')) {
            $schema = '
                <database name="test_db" defaultIdMethod="native">
                    <table name="test_main">
                        <column name="id_test" required="true" type="INTEGER" autoIncrement="true" primaryKey="true"/>
                        <column name="test_str" type="VARCHAR" required="true"/>
                        <column name="test_int" type="INTEGER" required="true"/>
                        <behavior name="uuid">
                            <parameter name="key_prefix" value="test"/>
                            <parameter name="key_columns" value="test_str.test_int"/>
                        </behavior>
                    </table>
                </database>';

            PropelQuickBuilder::buildSchema($schema);
        }
    }

    /**
     * @return void
     */
    public function testUuidBehaviorGeneratesExpectedUuid(): void
    {
        $testEntity = new TestMain();
        $testEntity->setTestStr('spryker');
        $testEntity->setTestInt(777);
        $testEntity->save();

        $this->assertSame($testEntity->getUuid(), static::UUID_GENERATED_VALUE_EXPECTED);
    }
}
