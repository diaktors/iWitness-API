<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\Sony;

use PHPExiftool\Driver\AbstractTag;

class FocalLengthTeleZoom extends AbstractTag
{

    protected $Id = 16;

    protected $Name = 'FocalLengthTeleZoom';

    protected $FullName = 'Sony::CameraInfo';

    protected $GroupName = 'Sony';

    protected $g0 = 'MakerNotes';

    protected $g1 = 'Sony';

    protected $g2 = 'Camera';

    protected $Type = 'int16u';

    protected $Writable = true;

    protected $Description = 'Focal Length Tele Zoom';

    protected $flag_Permanent = true;

}
