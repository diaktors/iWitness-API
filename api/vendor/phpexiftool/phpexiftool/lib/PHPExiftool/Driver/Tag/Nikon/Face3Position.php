<?php

/*
 * This file is part of PHPExifTool.
 *
 * (c) 2012 Romain Neutron <imprec@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\Nikon;

use PHPExiftool\Driver\AbstractTag;

class Face3Position extends AbstractTag
{

    protected $Id = 12;

    protected $Name = 'Face3Position';

    protected $FullName = 'Nikon::FaceDetect';

    protected $GroupName = 'Nikon';

    protected $g0 = 'MakerNotes';

    protected $g1 = 'Nikon';

    protected $g2 = 'Image';

    protected $Type = 'int16u';

    protected $Writable = true;

    protected $Description = 'Face 3 Position';

    protected $flag_Permanent = true;

    protected $MaxLength = 4;

}