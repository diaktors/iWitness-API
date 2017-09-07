<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\Canon;

use PHPExiftool\Driver\AbstractTag;

class AFMicroAdjValue extends AbstractTag
{

    protected $Id = 2;

    protected $Name = 'AFMicroAdjValue';

    protected $FullName = 'Canon::AFMicroAdj';

    protected $GroupName = 'Canon';

    protected $g0 = 'MakerNotes';

    protected $g1 = 'Canon';

    protected $g2 = 'Camera';

    protected $Type = 'rational64s';

    protected $Writable = true;

    protected $Description = 'AF Micro Adj Value';

    protected $flag_Permanent = true;

}
