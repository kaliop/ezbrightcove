<?php
/**
 * File containing the klpBcDiContainereZTieinTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcDiContainereZTieinTest extends klpBcTestCase
{
    public function testGetInstance()
    {
        $newInstance = klpBcDiContainereZTiein::getInstance( true );
        $existingInstance = klpBcDiContainereZTiein::getInstance();

        $this->assertSame(
            $newInstance, $existingInstance, "Both instances should be the same"
        );
    }

    public function testGetInstanceForce()
    {
        $newInstance1 = klpBcDiContainereZTiein::getInstance( true );
        $newInstance2 = klpBcDiContainereZTiein::getInstance( true );

        $this->assertNotSame(
            $newInstance1, $newInstance2, "Instances should not be the same"
        );
    }
}

