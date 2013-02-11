<?php
/**
 * File containing the klpbcupdate cronjob
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

$dic = klpBcDiContainereZTiein::getInstance();
$cronjob = new klpBcApiUpdateCronjob( $dic );
$cronjob->process();
