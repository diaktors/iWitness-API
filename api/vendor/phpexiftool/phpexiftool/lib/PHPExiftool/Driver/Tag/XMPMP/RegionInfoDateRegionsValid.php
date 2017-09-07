<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\XMPMP;

use PHPExiftool\Driver\AbstractTag;

class RegionInfoDateRegionsValid extends AbstractTag
{

    protected $Id = 'RegionInfoDateRegionsValid';

    protected $Name = 'RegionInfoDateRegionsValid';

    protected $FullName = 'Microsoft::MP';

    protected $GroupName = 'XMP-MP';

    protected $g0 = 'XMP';

    protected $g1 = 'XMP-MP';

    protected $g2 = 'Image';

    protected $Type = 'date';

    protected $Writable = true;

    protected $Description = 'Region Info Date Regions Valid';

    protected $local_g2 = 'Time';

}
