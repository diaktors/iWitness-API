<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\MXF;

use PHPExiftool\Driver\AbstractTag;

class TrackNumber extends AbstractTag
{

    protected $Id = '060e2b34.0101.0102.01040103.00000000';

    protected $Name = 'TrackNumber';

    protected $FullName = 'MXF::Main';

    protected $GroupName = 'MXF';

    protected $g0 = 'MXF';

    protected $g1 = 'MXF';

    protected $g2 = 'Video';

    protected $Type = 'int32u';

    protected $Writable = false;

    protected $Description = 'Track Number';

}