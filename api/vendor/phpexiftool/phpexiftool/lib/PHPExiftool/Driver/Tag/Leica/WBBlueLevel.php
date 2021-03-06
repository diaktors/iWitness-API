<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\Leica;

use PHPExiftool\Driver\AbstractTag;

class WBBlueLevel extends AbstractTag
{

    protected $Id = 804;

    protected $Name = 'WBBlueLevel';

    protected $FullName = 'Panasonic::Leica2';

    protected $GroupName = 'Leica';

    protected $g0 = 'MakerNotes';

    protected $g1 = 'Leica';

    protected $g2 = 'Camera';

    protected $Type = 'rational64u';

    protected $Writable = true;

    protected $Description = 'WB Blue Level';

    protected $flag_Permanent = true;

}
