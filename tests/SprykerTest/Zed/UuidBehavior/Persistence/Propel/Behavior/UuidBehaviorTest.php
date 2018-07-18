<?php
/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Zed\UuidBehavior\Persistense\Propel;

use Propel\Generator\Util\QuickBuilder as PropelQuickBuilder;
use Spryker\Zed\UuidBehavior\Persistence\Propel\Behavior\UuidBehavior;

class UuidBehaviorTest extends \Codeception\Test\Unit
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
                            <parameter name="columns_to_key_name" value="test_str.test_int"/>
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
        $testEntity = new \TestMain();
        $testEntity->setTestStr('spryker');
        $testEntity->setTestInt(777);
        $testEntity->save();

        $this->assertSame($testEntity->getUuid(), static::UUID_GENERATED_VALUE_EXPECTED);
    }
}
